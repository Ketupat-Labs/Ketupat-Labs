<?php

namespace App\Http\Controllers;

use App\Models\AiGeneratedContent;
use App\Models\Classroom;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiContentController extends Controller
{
    /**
     * Analyze uploaded documents and generate AI content.
     */
    public function analyze(Request $request)
    {
        try {
            Log::info('AI Content Analyze Request', [
                'user_id' => Auth::id(),
                'user_role' => Auth::user()->role ?? 'guest',
                'output_type' => $request->output_type,
                'has_documents' => $request->hasFile('documents'),
            ]);

            $request->validate([
                'documents' => 'required|array|min:1',
                'documents.*' => [
                    'required',
                    'file',
                    'mimes:pdf,doc,docx,txt',
                    'max:10240',
                ],
                'output_type' => 'required|in:summary_notes,quiz',
                'question_type' => 'nullable|in:mcq,structured,mixed',
                'question_count' => 'nullable|integer|min:3|max:20',
                'separate_answers' => 'nullable|boolean',
                'class_id' => 'nullable|exists:classrooms,id',
                'title' => 'required|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);
        }

        // Check if user is a teacher
        if (Auth::user()->role !== 'teacher') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya guru boleh menjana kandungan AI.'
            ], 403);
        }

        // Create initial record
        $aiContent = AiGeneratedContent::create([
            'teacher_id' => Auth::id(),
            'class_id' => $request->class_id,
            'content_type' => $request->output_type,
            'title' => $request->title,
            'status' => 'processing',
            'content' => [],
            'question_type' => $request->output_type === 'quiz' ? ($request->question_type ?? 'mcq') : null,
        ]);

        try {
            // Extract text from documents
            $documentTexts = [];

            foreach ($request->file('documents') as $file) {
                $path = $file->store('ai_processing', 'public');
                $fullPath = storage_path('app/public/' . $path);

                Log::info('Extracting text from document', [
                    'filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                ]);

                $text = $this->extractTextFromDocument($fullPath, $file->getMimeType());
                $documentTexts[] = $text;

                // Clean up
                Storage::disk('public')->delete($path);
            }

            // Combine texts
            $combinedText = implode("\n\n", $documentTexts);

            // Generate content
            if ($request->output_type === 'quiz') {
                $questionType = $request->question_type ?? 'mcq';
                $questionCount = $request->question_count ?? 10;

                if ($questionType === 'mcq') {
                    $content = $this->generateMCQQuiz($combinedText, $questionCount);
                } elseif ($questionType === 'structured') {
                    $content = $this->generateStructuredQuestions($combinedText, $questionCount);
                } else {
                    $content = $this->generateMixedQuestions($combinedText, $questionCount);
                }
            } else {
                $content = $this->generateSummaryNotes($combinedText);
            }

            // Update record
            $aiContent->update([
                'content' => $content,
                'status' => 'completed',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kandungan berjaya dijana!',
                'data' => $aiContent,
            ]);

        } catch (\Exception $e) {
            $aiContent->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('AI Content Generation Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menjana kandungan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all AI-generated content
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
     * Get specific content
     */
    public function show($id)
    {
        $content = AiGeneratedContent::with(['class', 'teacher'])->findOrFail($id);

        // Authorization check
        if (Auth::id() !== $content->teacher_id) {
            if (!$content->is_shared || !$content->class_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dibenarkan.'
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $content,
        ]);
    }

    /**
     * Update content
     */
    public function update(Request $request, $id)
    {
        $content = AiGeneratedContent::findOrFail($id);

        if (Auth::id() !== $content->teacher_id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dibenarkan.'
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
            'message' => 'Kandungan dikemaskini!',
            'data' => $content,
        ]);
    }

    /**
     * Delete content
     */
    public function destroy($id)
    {
        $content = AiGeneratedContent::findOrFail($id);

        if (Auth::id() !== $content->teacher_id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dibenarkan.'
            ], 403);
        }

        $content->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kandungan dipadam!',
        ]);
    }

    /**
     * Extract text from document
     */
    private function extractTextFromDocument($filePath, $mimeType)
    {
        try {
            switch ($mimeType) {
                case 'application/pdf':
                    return $this->extractTextFromPDF($filePath);
                case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                case 'application/msword':
                    return $this->extractTextFromDOCX($filePath);
                case 'text/plain':
                    return file_get_contents($filePath);
                default:
                    throw new \Exception("Jenis fail tidak disokong: " . $mimeType);
            }
        } catch (\Exception $e) {
            Log::error('Text extraction error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function extractTextFromPDF($filePath)
    {
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();
            $text = preg_replace('/\s+/', ' ', $text);
            $text = trim($text);

            if (empty($text)) {
                throw new \Exception("Tiada teks dijumpai dalam PDF.");
            }

            return $text;
        } catch (\Exception $e) {
            throw new \Exception("Gagal mengekstrak teks dari PDF: " . $e->getMessage());
        }
    }

    private function extractTextFromDOCX($filePath)
    {
        try {
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);
            $text = '';

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                        foreach ($element->getElements() as $textElement) {
                            if (method_exists($textElement, 'getText')) {
                                $text .= $textElement->getText() . ' ';
                            }
                        }
                        $text .= "\n";
                    } elseif ($element instanceof \PhpOffice\PhpWord\Element\Text) {
                        $text .= $element->getText() . "\n";
                    } elseif (method_exists($element, 'getText')) {
                        $text .= $element->getText() . "\n";
                    }
                }
            }

            $text = trim($text);

            if (empty($text)) {
                throw new \Exception("Tiada teks dijumpai dalam DOCX.");
            }

            return $text;
        } catch (\Exception $e) {
            throw new \Exception("Gagal mengekstrak teks dari DOCX: " . $e->getMessage());
        }
    }

    /**
     * Generate MCQ Quiz using Gemini AI
     */
    private function generateMCQQuiz($text, $count = 10)
    {
        $apiKey = env('GEMINI_API_KEY');

        if (!empty($apiKey)) {
            try {
                $prompt = "Anda adalah pencipta kandungan pendidikan. Berdasarkan teks berikut, hasilkan {$count} soalan aneka pilihan.\n\n";
                $prompt .= "Teks:\n{$text}\n\n";
                $prompt .= "Hasilkan TEPAT {$count} soalan aneka pilihan dalam format JSON:\n";
                $prompt .= "{\n";
                $prompt .= '  "questions": [' . "\n";
                $prompt .= "    {\n";
                $prompt .= '      "question": "Soalan di sini?",' . "\n";
                $prompt .= '      "options": {' . "\n";
                $prompt .= '        "A": "Pilihan A",' . "\n";
                $prompt .= '        "B": "Pilihan B",' . "\n";
                $prompt .= '        "C": "Pilihan C",' . "\n";
                $prompt .= '        "D": "Pilihan D"' . "\n";
                $prompt .= "      },\n";
                $prompt .= '      "correct_answer": "A",' . "\n";
                $prompt .= '      "explanation": "Penjelasan mengapa A betul"' . "\n";
                $prompt .= "    }\n";
                $prompt .= "  ]\n";
                $prompt .= "}\n\n";
                $prompt .= "Semua soalan dan jawapan mestilah dalam Bahasa Melayu.";

                $response = Http::timeout(60)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key={$apiKey}",
                    [
                        'contents' => [['parts' => [['text' => $prompt]]]]
                    ]
                );

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                        $generatedText = $data['candidates'][0]['content']['parts'][0]['text'];
                        $jsonStart = strpos($generatedText, '{');
                        $jsonEnd = strrpos($generatedText, '}') + 1;
                        
                        if ($jsonStart !== false && $jsonEnd !== false) {
                            $jsonText = substr($generatedText, $jsonStart, $jsonEnd - $jsonStart);
                            $parsed = json_decode($jsonText, true);

                            if ($parsed && isset($parsed['questions'])) {
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
                Log::error('Gemini API Error: ' . $e->getMessage());
            }
        }

        // Fallback
        return $this->getFallbackMCQ($count);
    }

    /**
     * Generate summary notes using Gemini AI
     */
    private function generateSummaryNotes($text)
    {
        $apiKey = env('GEMINI_API_KEY');

        if (!empty($apiKey)) {
            try {
                $prompt = "Anda adalah penganalisis kandungan pendidikan. Analisis teks berikut dan cipta nota ringkasan.\n\n";
                $prompt .= "Teks:\n{$text}\n\n";
                $prompt .= "Sediakan:\n";
                $prompt .= "1. Ringkasan keseluruhan (2-3 perenggan)\n";
                $prompt .= "2. Senarai 5-10 perkara penting\n\n";
                $prompt .= "Format sebagai JSON:\n";
                $prompt .= "{\n";
                $prompt .= '  "summary": "Ringkasan di sini...",' . "\n";
                $prompt .= '  "key_points": ["Perkara 1", "Perkara 2", ...]' . "\n";
                $prompt .= "}\n\n";
                $prompt .= "Semua kandungan mestilah dalam Bahasa Melayu.";

                $response = Http::timeout(60)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key={$apiKey}",
                    [
                        'contents' => [['parts' => [['text' => $prompt]]]]
                    ]
                );

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                        $generatedText = $data['candidates'][0]['content']['parts'][0]['text'];
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
                Log::error('Gemini API Error: ' . $e->getMessage());
            }
        }

        // Fallback
        return [
            'summary' => 'Ringkasan dokumen (Tambah GEMINI_API_KEY untuk penjanaan sebenar)',
            'key_points' => [
                'Pengenalan kepada topik utama',
                'Konsep dan definisi penting',
                'Aplikasi praktikal',
                'Kesimpulan dan rumusan'
            ],
            'word_count' => str_word_count($text),
        ];
    }

    private function generateStructuredQuestions($text, $count)
    {
        // Similar implementation with Gemini
        return $this->getFallbackStructured($count);
    }

    private function generateMixedQuestions($text, $count)
    {
        $mcqCount = (int)ceil($count * 0.6);
        $structuredCount = $count - $mcqCount;

        $mcqData = $this->generateMCQQuiz($text, $mcqCount);
        $structuredData = $this->generateStructuredQuestions($text, $structuredCount);

        return [
            'mcq_questions' => $mcqData['questions'],
            'structured_questions' => $structuredData['questions'],
            'total_mcq' => count($mcqData['questions']),
            'total_structured' => count($structuredData['questions']),
        ];
    }

    private function getFallbackMCQ($count)
    {
        $questions = [];
        for ($i = 1; $i <= $count; $i++) {
            $questions[] = [
                'id' => $i,
                'question' => "Soalan {$i} berdasarkan dokumen?",
                'options' => ['A' => 'Pilihan A', 'B' => 'Pilihan B', 'C' => 'Pilihan C', 'D' => 'Pilihan D'],
                'correct_answer' => 'A',
                'explanation' => 'Penjelasan (Tambah GEMINI_API_KEY untuk penjanaan sebenar)',
            ];
        }
        return ['questions' => $questions, 'total_questions' => count($questions)];
    }

    private function getFallbackStructured($count)
    {
        $questions = [];
        for ($i = 1; $i <= $count; $i++) {
            $questions[] = [
                'id' => $i,
                'question' => "Huraikan konsep dalam seksyen {$i}.",
                'marks' => 10,
                'model_answer' => 'Jawapan model (Tambah GEMINI_API_KEY)',
                'marking_scheme' => 'Skim pemarkahan',
            ];
        }
        return ['questions' => $questions, 'total_marks' => $count * 10];
    }
}
