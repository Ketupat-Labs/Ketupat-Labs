<?php

namespace App\Http\Controllers;

use App\Models\AiGeneratedContent;
use App\Models\Document;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class AiContentController extends Controller
{
    /**
     * Analyze uploaded documents and generate AI content.
     * AC: "Ability to select source files", "choose desired output"
     */
    public function analyze(Request $request)
    {
        try {
            // Add logging for debugging
            \Illuminate\Support\Facades\Log::info('AI Content Analyze Request', [
                'user_id' => Auth::id(),
                'user_role' => Auth::user()->role ?? 'guest',
                'output_type' => $request->output_type,
                'has_documents' => $request->hasFile('documents'),
                'document_count' => is_array($request->file('documents')) ? count($request->file('documents')) : 0,
            ]);

            // --- VALIDATION (from Acceptance Criteria) ---
            $request->validate([
                'documents' => 'required|array|min:1',
                'documents.*' => [
                    'required',
                    'file',
                    'mimes:pdf,docx,pptx', // AC: PDF, DOCX, PPT
                    'max:10240', // AC: "file size up to 10MB"
                ],
                'output_type' => 'required|in:summary_notes,quiz', // AC: "Generate summary note", "Generate quiz"
                'question_type' => 'nullable|in:mcq,structured,mixed', // Question type for quizzes
                'question_count' => 'nullable|integer|min:3|max:20', // Number of questions
                'separate_answers' => 'nullable|boolean', // Whether to separate answers
                'class_id' => 'nullable|exists:class,id',
                'title' => 'required|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->except('documents'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Unexpected error in analyze start', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
        // Check if user is a teacher
        if (Auth::user()->role !== 'teacher') {
            return response()->json([
                'success' => false,
                'message' => 'Only teachers can generate AI content.'
            ], 403);
        }

        // Create initial record with 'processing' status
        $aiContent = AiGeneratedContent::create([
            'teacher_id' => Auth::id(),
            'class_id' => $request->class_id,
            'content_type' => $request->output_type,
            'title' => $request->title,
            'status' => 'processing',
            'content' => [],
        ]);

        // Store question type if quiz
        if ($request->output_type === 'quiz') {
            $aiContent->question_type = $request->question_type ?? 'mcq';
            $aiContent->save();
        }

        try {
            // Store uploaded files temporarily and extract text
            $documentTexts = [];
            $documentIds = [];

            foreach ($request->file('documents') as $file) {
                // Store document
                $path = $file->store('ai_processing', 'private');
                $fullPath = storage_path('app/private/' . $path);

                \Illuminate\Support\Facades\Log::info('Extracting text from document', [
                    'filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);

                // Extract text from document
                $text = $this->extractTextFromDocument($fullPath, $file->getMimeType());

                \Illuminate\Support\Facades\Log::info('Text extracted successfully', [
                    'text_length' => strlen($text),
                    'preview' => substr($text, 0, 200),
                ]);

                $documentTexts[] = $text;

                // Clean up temp file
                Storage::disk('private')->delete($path);
            }

            // Combine all document texts
            $combinedText = implode("\n\n", $documentTexts);

            \Illuminate\Support\Facades\Log::info('Combined text for AI analysis', [
                'total_length' => strlen($combinedText),
                'preview' => substr($combinedText, 0, 300),
            ]);

            // Generate AI content based on type
            if ($request->output_type === 'quiz') {
                $questionType = $request->question_type ?? 'mcq';
                $questionCount = $request->question_count ?? 10;

                if ($questionType === 'mcq') {
                    $content = $this->generateMCQQuiz($combinedText, $questionCount);
                } elseif ($questionType === 'structured') {
                    $content = $this->generateStructuredQuestions($combinedText, $questionCount);
                } else { // mixed
                    $content = $this->generateMixedQuestions($combinedText, $questionCount);
                }
            } else {
                $content = $this->generateSummaryNotes($combinedText);
            }

            // Update record with generated content
            $aiContent->update([
                'content' => $content,
                'status' => 'completed',
                'source_document_ids' => $documentIds,
            ]);

            // AC: "A success message is displayed immediately"
            return response()->json([
                'success' => true,
                'message' => 'Content generated successfully!',
                'data' => $aiContent,
            ]);

        } catch (\Exception $e) {
            // AC: "clear error message after generation"
            $aiContent->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            \Illuminate\Support\Facades\Log::error('AI Content Generation Error: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('Stack Trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate content: ' . $e->getMessage(),
                'error_details' => [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
            ], 500);
        }
    }

    /**
     * Get all AI-generated content for the authenticated teacher.
     */
    public function index(Request $request)
    {
        $query = AiGeneratedContent::where('teacher_id', Auth::id())
            ->with(['class', 'teacher'])
            ->orderBy('created_at', 'desc');

        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->has('content_type')) {
            $query->where('content_type', $request->content_type);
        }

        $contents = $query->get();

        return response()->json([
            'success' => true,
            'data' => $contents,
        ]);
    }

    /**
     * Get a specific AI-generated content by ID.
     * AC: "Ability to view the generated notes or quiz"
     */
    public function show($id)
    {
        $content = AiGeneratedContent::with(['class', 'teacher'])->findOrFail($id);

        // Authorization: Teacher who created it, or students in the class if shared
        if (Auth::id() !== $content->teacher_id) {
            if (!$content->is_shared || !$content->class_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to view this content.'
                ], 403);
            }

            // Check if student is enrolled in the class
            $class = ClassModel::find($content->class_id);
            $enrolledStudentIds = $class->students()->pluck('id')->toArray();

            if (!in_array(Auth::id(), $enrolledStudentIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to view this content.'
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $content,
        ]);
    }

    /**
     * Update AI-generated content.
     * AC: "Ability to edit the AI-generated content before saving"
     */
    public function update(Request $request, $id)
    {
        $content = AiGeneratedContent::findOrFail($id);

        // Only the creator can edit
        if (Auth::id() !== $content->teacher_id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to edit this content.'
            ], 403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|array',
            'is_shared' => 'sometimes|boolean',
        ]);

        $content->update($request->only(['title', 'content', 'is_shared']));

        return response()->json([
            'success' => true,
            'message' => 'Content updated successfully!',
            'data' => $content,
        ]);
    }

    /**
     * Share content with students.
     * AC: "Ability to save and share it with students"
     */
    public function share(Request $request, $id)
    {
        $content = AiGeneratedContent::findOrFail($id);

        if (Auth::id() !== $content->teacher_id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to share this content.'
            ], 403);
        }

        $request->validate([
            'class_id' => 'required|exists:class,id',
        ]);

        $content->update([
            'class_id' => $request->class_id,
            'is_shared' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Content shared with students successfully!',
            'data' => $content,
        ]);
    }

    /**
     * Delete AI-generated content.
     * AC: "Ability to remove generated content"
     */
    public function destroy($id)
    {
        $content = AiGeneratedContent::findOrFail($id);

        if (Auth::id() !== $content->teacher_id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this content.'
            ], 403);
        }

        $content->delete();

        return response()->json([
            'success' => true,
            'message' => 'Content deleted successfully!',
        ]);
    }

    /**
     * Extract text from uploaded document.
     * Placeholder - integrate with actual text extraction library (e.g., Apache Tika, pdf-parse, etc.)
     */
    private function extractTextFromDocument($filePath, $mimeType)
    {
        try {
            // Extract text based on file type
            switch ($mimeType) {
                case 'application/pdf':
                    return $this->extractTextFromPDF($filePath);

                case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                    return $this->extractTextFromDOCX($filePath);

                case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
                    return $this->extractTextFromPPTX($filePath);

                default:
                    throw new \Exception("Unsupported file type: " . $mimeType);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Text extraction error: ' . $e->getMessage());
            throw new \Exception("Failed to extract text from document: " . $e->getMessage());
        }
    }

    /**
     * Extract text from PDF file
     */
    private function extractTextFromPDF($filePath)
    {
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($filePath);
        $text = $pdf->getText();

        // Clean up the text
        $text = preg_replace('/\s+/', ' ', $text); // Replace multiple spaces with single space
        $text = trim($text);

        if (empty($text)) {
            throw new \Exception("No text could be extracted from the PDF. It might be a scanned image.");
        }

        return $text;
    }

    /**
     * Extract text from DOCX file
     */
    private function extractTextFromDOCX($filePath)
    {
        $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);
        $text = '';

        foreach ($phpWord->getSections() as $section) {
            $elements = $section->getElements();
            foreach ($elements as $element) {
                // Get text from TextRun elements
                if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                    foreach ($element->getElements() as $textElement) {
                        if (method_exists($textElement, 'getText')) {
                            $text .= $textElement->getText() . ' ';
                        }
                    }
                    $text .= "\n";
                }
                // Get text from Text elements
                elseif ($element instanceof \PhpOffice\PhpWord\Element\Text) {
                    $text .= $element->getText() . "\n";
                }
                // Handle other element types
                elseif (method_exists($element, 'getText')) {
                    $text .= $element->getText() . "\n";
                }
            }
        }

        $text = trim($text);

        if (empty($text)) {
            throw new \Exception("No text could be extracted from the DOCX file.");
        }

        return $text;
    }
    /**
     * Extract text from PPTX file
     */
    private function extractTextFromPPTX($filePath)
    {
        // For now, return a message indicating PPTX support is limited
        // Full PPTX extraction requires phpoffice/phppresentation which has more dependencies
        throw new \Exception("PPTX text extraction is not yet implemented. Please use PDF or DOCX format.");
    }

    /**
     * Generate MCQ quiz from extracted text using AI.
     * AC: "The system will display 5-10 multiple choice questions"
     * Now powered by Google Gemini AI
     */
    private function generateMCQQuiz($text, $count = 10)
    {
        $apiKey = env('GEMINI_API_KEY');

        // Use Gemini AI if API key is available
        if (!empty($apiKey)) {
            try {
                $prompt = "You are an educational content creator. Based on the following text, generate {$count} multiple choice questions.\n\n";
                $prompt .= "Text:\n{$text}\n\n";
                $prompt .= "Generate EXACTLY {$count} multiple choice questions in JSON format:\n";
                $prompt .= "{\n";
                $prompt .= '  "questions": [' . "\n";
                $prompt .= "    {\n";
                $prompt .= '      "question": "Question text here?",' . "\n";
                $prompt .= '      "options": {' . "\n";
                $prompt .= '        "A": "Option A text",' . "\n";
                $prompt .= '        "B": "Option B text",' . "\n";
                $prompt .= '        "C": "Option C text",' . "\n";
                $prompt .= '        "D": "Option D text"' . "\n";
                $prompt .= "      },\n";
                $prompt .= '      "correct_answer": "A",' . "\n";
                $prompt .= '      "explanation": "Explanation why A is correct"' . "\n";
                $prompt .= "    }\n";
                $prompt .= "  ]\n";
                $prompt .= "}\n\n";
                $prompt .= "Make questions challenging but fair. Ensure all options are plausible.";

                $response = Http::timeout(60)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}",
                    [
                        'contents' => [
                            ['parts' => [['text' => $prompt]]]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.7,
                            'maxOutputTokens' => 2048,
                        ]
                    ]
                );

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                        $generatedText = $data['candidates'][0]['content']['parts'][0]['text'];

                        // Extract JSON from the response
                        $jsonStart = strpos($generatedText, '{');
                        $jsonEnd = strrpos($generatedText, '}') + 1;
                        if ($jsonStart !== false && $jsonEnd !== false) {
                            $jsonText = substr($generatedText, $jsonStart, $jsonEnd - $jsonStart);
                            $parsed = json_decode($jsonText, true);

                            if ($parsed && isset($parsed['questions'])) {
                                // Add IDs to questions
                                foreach ($parsed['questions'] as $i => &$q) {
                                    $q['id'] = $i + 1;
                                }

                                return [
                                    'questions' => $parsed['questions'],
                                    'total_questions' => count($parsed['questions']),
                                ];
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Gemini API Error in MCQ generation: ' . $e->getMessage());
            }
        }

        // Fallback if API fails or not configured
        $questions = [];
        for ($i = 1; $i <= $count; $i++) {
            $questions[] = [
                'id' => $i,
                'question' => "Sample Question {$i} based on the document content?",
                'options' => [
                    'A' => 'Option A',
                    'B' => 'Option B',
                    'C' => 'Option C',
                    'D' => 'Option D',
                ],
                'correct_answer' => 'A',
                'explanation' => 'This is why option A is correct. (Note: Add your Gemini API key to generate real questions)',
            ];
        }

        return [
            'questions' => $questions,
            'total_questions' => count($questions),
        ];
    }

    /**
     * Generate summary notes from extracted text using AI.
     * AC: "The system displays a list of key points from the documents"
     * Now powered by Google Gemini AI
     */
    private function generateSummaryNotes($text)
    {
        $apiKey = env('GEMINI_API_KEY');

        // Use Gemini AI if API key is available
        if (!empty($apiKey)) {
            try {
                $prompt = "You are an educational content summarizer. Analyze the following text and create comprehensive summary notes.\n\n";
                $prompt .= "Text:\n{$text}\n\n";
                $prompt .= "Provide:\n";
                $prompt .= "1. A concise overall summary (2-3 paragraphs)\n";
                $prompt .= "2. A list of 5-10 key points covering the main concepts\n\n";
                $prompt .= "Format as JSON:\n";
                $prompt .= "{\n";
                $prompt .= '  "summary": "Overall summary text here...",' . "\n";
                $prompt .= '  "key_points": [' . "\n";
                $prompt .= '    "First key point",' . "\n";
                $prompt .= '    "Second key point",' . "\n";
                $prompt .= '    "etc..."' . "\n";
                $prompt .= "  ]\n";
                $prompt .= "}\n\n";
                $prompt .= "Make the summary educational and easy to understand for students.";

                $response = Http::timeout(60)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}",
                    [
                        'contents' => [
                            ['parts' => [['text' => $prompt]]]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.7,
                            'maxOutputTokens' => 2048,
                        ]
                    ]
                );

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                        $generatedText = $data['candidates'][0]['content']['parts'][0]['text'];

                        // Extract JSON from the response
                        $jsonStart = strpos($generatedText, '{');
                        $jsonEnd = strrpos($generatedText, '}') + 1;
                        if ($jsonStart !== false && $jsonEnd !== false) {
                            $jsonText = substr($generatedText, $jsonStart, $jsonEnd - $jsonStart);
                            $parsed = json_decode($jsonText, true);

                            if ($parsed && isset($parsed['summary']) && isset($parsed['key_points'])) {
                                return [
                                    'summary' => $parsed['summary'],
                                    'key_points' => $parsed['key_points'],
                                    'word_count' => str_word_count($text),
                                ];
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Gemini API Error in summary generation: ' . $e->getMessage());
            }
        }

        // Fallback if API fails or not configured
        $keyPoints = [
            'Introduction to the main topic and its importance',
            'Historical background and context',
            'Key concepts and definitions',
            'Major theories and frameworks',
            'Practical applications and examples',
            'Current trends and future directions',
            'Conclusion and key takeaways',
        ];

        return [
            'summary' => 'This is an AI-generated summary of the document(s). (Note: Add your Gemini API key to generate real summaries)',
            'key_points' => $keyPoints,
            'word_count' => str_word_count($text),
        ];
    }

    /**
     * Generate structured (essay-type) questions from extracted text.
     * Now powered by Google Gemini AI
     */
    private function generateStructuredQuestions($text, $count = 5)
    {
        $apiKey = env('GEMINI_API_KEY');

        // Use Gemini AI if API key is available
        if (!empty($apiKey)) {
            try {
                $prompt = "You are an educational assessment creator. Based on the following text, generate {$count} structured (essay-type) questions.\n\n";
                $prompt .= "Text:\n{$text}\n\n";
                $prompt .= "Generate EXACTLY {$count} structured questions in JSON format:\n";
                $prompt .= "{\n";
                $prompt .= '  "questions": [' . "\n";
                $prompt .= "    {\n";
                $prompt .= '      "question": "Question text requiring detailed answer?",' . "\n";
                $prompt .= '      "marks": 10,' . "\n";
                $prompt .= '      "model_answer": "A comprehensive answer including key points...",' . "\n";
                $prompt .= '      "marking_scheme": "Definition (3 marks), Examples (3 marks), Analysis (4 marks)"' . "\n";
                $prompt .= "    }\n";
                $prompt .= "  ]\n";
                $prompt .= "}\n\n";
                $prompt .= "Questions should require critical thinking and detailed answers. Marks should range from 5-15.";

                $response = Http::timeout(60)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}",
                    [
                        'contents' => [
                            ['parts' => [['text' => $prompt]]]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.7,
                            'maxOutputTokens' => 2048,
                        ]
                    ]
                );

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                        $generatedText = $data['candidates'][0]['content']['parts'][0]['text'];

                        // Extract JSON from the response
                        $jsonStart = strpos($generatedText, '{');
                        $jsonEnd = strrpos($generatedText, '}') + 1;
                        if ($jsonStart !== false && $jsonEnd !== false) {
                            $jsonText = substr($generatedText, $jsonStart, $jsonEnd - $jsonStart);
                            $parsed = json_decode($jsonText, true);

                            if ($parsed && isset($parsed['questions'])) {
                                // Add IDs to questions
                                foreach ($parsed['questions'] as $i => &$q) {
                                    $q['id'] = $i + 1;
                                }

                                return [
                                    'questions' => $parsed['questions'],
                                    'total_marks' => array_sum(array_column($parsed['questions'], 'marks')),
                                ];
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Gemini API Error in structured questions: ' . $e->getMessage());
            }
        }

        // Fallback if API fails or not configured
        $questions = [];
        for ($i = 1; $i <= $count; $i++) {
            $questions[] = [
                'id' => $i,
                'question' => "Explain the concept discussed in section {$i}. Provide examples to support your answer.",
                'marks' => rand(5, 15),
                'model_answer' => "A comprehensive answer would include:\n1. Clear definition of the concept\n2. Relevant examples from the text\n3. Analysis of key points\n4. Well-structured conclusion\n(Note: Add your Gemini API key to generate real questions)",
                'marking_scheme' => "Award marks for: Definition (3 marks), Examples (4 marks), Analysis (5 marks), Conclusion (3 marks)",
            ];
        }

        return [
            'questions' => $questions,
            'total_marks' => array_sum(array_column($questions, 'marks')),
        ];
    }

    /**
     * Generate a mix of MCQ and structured questions.
     */
    private function generateMixedQuestions($text, $count = 10)
    {
        // Split count between MCQ and structured (60/40 ratio)
        $mcqCount = (int)ceil($count * 0.6);
        $structuredCount = $count - $mcqCount;

        // Generate both types
        $mcqData = $this->generateMCQQuiz($text, $mcqCount);
        $structuredData = $this->generateStructuredQuestions($text, $structuredCount);

        return [
            'mcq_questions' => $mcqData['questions'],
            'structured_questions' => $structuredData['questions'],
            'total_mcq' => count($mcqData['questions']),
            'total_structured' => count($structuredData['questions']),
        ];
    }

    /**
     * Export generated content to specified format (PDF, DOCX, PPTX, TXT).
     */
    public function export(Request $request, $id)
    {
        $request->validate([
            'format' => 'required|in:pdf,docx,pptx,txt',
            'separate_answers' => 'nullable|boolean',
        ]);

        $content = AiGeneratedContent::findOrFail($id);

        // Check authorization (teacher or enrolled student if shared)
        $user = Auth::user();
        if ($user->role === 'teacher' && $user->id !== $content->teacher_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $format = $request->input('format');
        $separateAnswers = $request->input('separate_answers', false);
        $filename = $content->title . '.' . $format;

        try {
            // If separate answers is requested and content is quiz
            if ($separateAnswers && $content->content_type === 'quiz') {
                // Generate ZIP with two files
                $zipFilename = $content->title . '.zip';
                $questionsFile = $this->generateExportFile($content, $format, false); // Questions only
                $answersFile = $this->generateExportFile($content, $format, true); // Answers only

                // Create ZIP archive
                $zip = new \ZipArchive();
                $zipPath = storage_path('app/temp/' . $zipFilename);

                // Ensure temp directory exists
                if (!file_exists(storage_path('app/temp'))) {
                    mkdir(storage_path('app/temp'), 0755, true);
                }

                if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                    $zip->addFromString($content->title . '_questions.' . $format, $questionsFile);
                    $zip->addFromString($content->title . '_answers.' . $format, $answersFile);
                    $zip->close();

                    // Return ZIP file
                    $zipContent = file_get_contents($zipPath);
                    unlink($zipPath); // Clean up

                    return response($zipContent)
                        ->header('Content-Type', 'application/zip')
                        ->header('Content-Disposition', 'attachment; filename="' . $zipFilename . '"');
                } else {
                    throw new \Exception('Failed to create ZIP archive');
                }
            } else {
                // Generate single file with both questions and answers
                $fileContent = $this->generateExportFile($content, $format, null);

                // Return as downloadable file
                return response($fileContent)
                    ->header('Content-Type', $this->getMimeType($format))
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate exportable file content.
     * @param $content - the AI generated content object
     * @param $format - file format (pdf, docx, pptx, txt)
     * @param $answersOnly - null: both, false: questions only, true: answers only
     */
    private function generateExportFile($content, $format, $answersOnly = null)
    {
        // TODO: Implement proper file generation using libraries
        // For PDF: use TCPDF or DomPDF
        // For DOCX: use PHPWord
        // For PPTX: use PHPPresentation

        $data = $content->content;
        $text = "Title: {$content->title}\n\n";

        if ($content->content_type === 'summary_notes') {
            $text .= "Summary:\n" . ($data['summary'] ?? '') . "\n\n";
            $text .= "Key Points:\n";
            foreach ($data['key_points'] ?? [] as $i => $point) {
                $text .= ($i + 1) . ". " . $point . "\n";
            }
        } else {
            // Quiz content - handle MCQ questions
            if (isset($data['questions'])) {
                foreach ($data['questions'] as $i => $q) {
                    if ($answersOnly === false) {
                        // Questions only
                        $text .= "\nQuestion " . ($i + 1) . ": " . $q['question'] . "\n";
                        if (isset($q['options'])) {
                            foreach ($q['options'] as $key => $option) {
                                $text .= $key . ". " . $option . "\n";
                            }
                        }
                        if (isset($q['marks'])) {
                            $text .= "[" . $q['marks'] . " marks]\n";
                        }
                    } elseif ($answersOnly === true) {
                        // Answers only
                        $text .= "\nQuestion " . ($i + 1) . " Answer:\n";
                        if (isset($q['correct_answer'])) {
                            $text .= "Correct Answer: " . $q['correct_answer'] . "\n";
                        }
                        if (isset($q['explanation'])) {
                            $text .= "Explanation: " . $q['explanation'] . "\n";
                        }
                        if (isset($q['model_answer'])) {
                            $text .= "Model Answer:\n" . $q['model_answer'] . "\n";
                        }
                        if (isset($q['marking_scheme'])) {
                            $text .= "Marking Scheme: " . $q['marking_scheme'] . "\n";
                        }
                    } else {
                        // Both questions and answers
                        $text .= "\nQuestion " . ($i + 1) . ": " . $q['question'] . "\n";
                        if (isset($q['options'])) {
                            foreach ($q['options'] as $key => $option) {
                                $text .= $key . ". " . $option . "\n";
                            }
                        }
                        if (isset($q['marks'])) {
                            $text .= "[" . $q['marks'] . " marks]\n";
                        }
                        if (isset($q['correct_answer'])) {
                            $text .= "Correct Answer: " . $q['correct_answer'] . "\n";
                        }
                        if (isset($q['explanation'])) {
                            $text .= "Explanation: " . $q['explanation'] . "\n";
                        }
                        if (isset($q['model_answer'])) {
                            $text .= "Model Answer:\n" . $q['model_answer'] . "\n";
                        }
                        if (isset($q['marking_scheme'])) {
                            $text .= "Marking Scheme: " . $q['marking_scheme'] . "\n";
                        }
                    }
                }
            }

            // Handle mixed format (MCQ + Structured)
            if (isset($data['mcq_questions'])) {
                $text .= "\n=== MULTIPLE CHOICE QUESTIONS ===\n";
                foreach ($data['mcq_questions'] as $i => $q) {
                    if ($answersOnly === false) {
                        $text .= "\nQuestion " . ($i + 1) . ": " . $q['question'] . "\n";
                        foreach ($q['options'] as $key => $option) {
                            $text .= $key . ". " . $option . "\n";
                        }
                    } elseif ($answersOnly === true) {
                        $text .= "\nQuestion " . ($i + 1) . " Answer: " . $q['correct_answer'] . "\n";
                        $text .= "Explanation: " . $q['explanation'] . "\n";
                    } else {
                        $text .= "\nQuestion " . ($i + 1) . ": " . $q['question'] . "\n";
                        foreach ($q['options'] as $key => $option) {
                            $text .= $key . ". " . $option . "\n";
                        }
                        $text .= "Correct Answer: " . $q['correct_answer'] . "\n";
                        $text .= "Explanation: " . $q['explanation'] . "\n";
                    }
                }
            }

            if (isset($data['structured_questions'])) {
                $text .= "\n=== STRUCTURED QUESTIONS ===\n";
                foreach ($data['structured_questions'] as $i => $q) {
                    if ($answersOnly === false) {
                        $text .= "\nQuestion " . ($i + 1) . ": " . $q['question'] . "\n";
                        $text .= "[" . $q['marks'] . " marks]\n";
                    } elseif ($answersOnly === true) {
                        $text .= "\nQuestion " . ($i + 1) . " Model Answer:\n";
                        $text .= $q['model_answer'] . "\n";
                        $text .= "Marking Scheme: " . $q['marking_scheme'] . "\n";
                    } else {
                        $text .= "\nQuestion " . ($i + 1) . ": " . $q['question'] . "\n";
                        $text .= "[" . $q['marks'] . " marks]\n";
                        $text .= "Model Answer:\n" . $q['model_answer'] . "\n";
                        $text .= "Marking Scheme: " . $q['marking_scheme'] . "\n";
                    }
                }
            }
        }

        // For now, return plain text for all formats
        // In production, use proper libraries to generate formatted files
        return $text;
    }

    /**
     * Get MIME type for file format.
     */
    private function getMimeType($format)
    {
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'txt' => 'text/plain',
        ];

        return $mimeTypes[$format] ?? 'application/octet-stream';
    }
}
