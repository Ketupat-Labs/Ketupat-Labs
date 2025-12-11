<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AIGeneratorController extends Controller
{
    /**
     * Show the AI generator index page
     */
    public function index()
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }
        
        return view('ai-generator.index');
    }

    /**
     * Generate slides using AI
     */
    public function generateSlides(Request $request)
    {
        try {
            $request->validate([
                'topic' => 'nullable|string|max:500',
                'number_of_slides' => 'nullable|integer|min:1|max:50',
                'detail_level' => 'nullable|string|in:basic,intermediate,advanced',
                'document' => 'nullable|file|mimes:pdf,doc,docx,txt|max:10240', // Max 10MB
            ]);

            $topic = $request->input('topic');
            $numberOfSlides = $request->input('number_of_slides', 10);
            $detailLevel = $request->input('detail_level', 'intermediate');
            
            // Check if document is uploaded
            if ($request->hasFile('document')) {
                $documentText = $this->extractTextFromDocument($request->file('document'));
                
                // If no topic provided, use document content
                if (empty($topic)) {
                    $topic = 'Document content: ' . substr($documentText, 0, 200) . '...';
                }
                
                // Generate slides from document
                $slides = $this->generateSlidesFromDocument($documentText, $numberOfSlides, $detailLevel, $topic);
            } else {
                // Validate topic is required if no document
                if (empty($topic)) {
                    return response()->json([
                        'status' => 400,
                        'message' => 'Either topic or document is required',
                    ], 400);
                }
                
                $slides = $this->generateSlidesWithAI($topic, $numberOfSlides, $detailLevel);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Slides generated successfully',
                'data' => [
                    'slides' => $slides,
                    'topic' => $topic,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('AI Slide generation error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 500,
                'message' => 'Error generating slides. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Generate quiz using AI
     */
    public function generateQuiz(Request $request)
    {
        try {
            $request->validate([
                'topic' => 'nullable|string|max:500',
                'number_of_questions' => 'nullable|integer|min:1|max:50',
                'difficulty' => 'nullable|string|in:easy,medium,hard',
                'question_type' => 'nullable|string|in:multiple_choice,true_false,mixed',
                'document' => 'nullable|file|mimes:pdf,doc,docx,txt|max:10240', // Max 10MB
            ]);

            $topic = $request->input('topic');
            $numberOfQuestions = $request->input('number_of_questions', 10);
            $difficulty = $request->input('difficulty', 'medium');
            $questionType = $request->input('question_type', 'multiple_choice');
            
            // Check if document is uploaded
            if ($request->hasFile('document')) {
                $documentText = $this->extractTextFromDocument($request->file('document'));
                
                // If no topic provided, use document content
                if (empty($topic)) {
                    $topic = 'Document content: ' . substr($documentText, 0, 200) . '...';
                }
                
                // Generate quiz from document
                $quiz = $this->generateQuizFromDocument($documentText, $numberOfQuestions, $difficulty, $questionType, $topic);
            } else {
                // Validate topic is required if no document
                if (empty($topic)) {
                    return response()->json([
                        'status' => 400,
                        'message' => 'Either topic or document is required',
                    ], 400);
                }
                
                $quiz = $this->generateQuizWithAI($topic, $numberOfQuestions, $difficulty, $questionType);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Quiz generated successfully',
                'data' => [
                    'quiz' => $quiz,
                    'topic' => $topic,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('AI Quiz generation error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 500,
                'message' => 'Error generating quiz. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Generate slides using OpenAI API
     */
    private function generateSlidesWithAI(string $topic, int $numberOfSlides, string $detailLevel): array
    {
        $apiKey = env('OPENAI_API_KEY');
        
        if (!$apiKey) {
            throw new \Exception('OpenAI API key not configured');
        }

        $systemMessage = "You are an educational content creator. Generate presentation slides in JSON format. " .
                        "Each slide should have: title, content (main points as bullet points), and a brief summary. " .
                        "Return ONLY valid JSON array with no markdown formatting or code blocks.";

        $userMessage = "Create {$numberOfSlides} educational slides about: {$topic}. " .
                      "Detail level: {$detailLevel}. " .
                      "Format: JSON array where each slide is an object with 'title', 'content' (array of bullet points), and 'summary' fields. " .
                      "Make the content educational, clear, and well-structured.";

        $response = $this->callOpenAI($systemMessage, $userMessage, 3000);

        // Parse the response
        $slides = json_decode($response, true);
        
        if (!is_array($slides)) {
            // Try to extract JSON from markdown code blocks if present
            if (preg_match('/```(?:json)?\s*(\[.*?\])\s*```/s', $response, $matches)) {
                $slides = json_decode($matches[1], true);
            } else {
                // Fallback: create slides from text
                $slides = $this->parseSlidesFromText($response, $numberOfSlides);
            }
        }

        return $slides;
    }

    /**
     * Generate quiz using OpenAI API
     */
    private function generateQuizWithAI(string $topic, int $numberOfQuestions, string $difficulty, string $questionType): array
    {
        $apiKey = env('OPENAI_API_KEY');
        
        if (!$apiKey) {
            throw new \Exception('OpenAI API key not configured');
        }

        $systemMessage = "You are an educational quiz creator. Generate quiz questions in JSON format. " .
                        "Each question should have: question text, options (array of 4 options), correct_answer (index 0-3), and explanation. " .
                        "Return ONLY valid JSON array with no markdown formatting or code blocks.";

        $typeInstruction = $questionType === 'multiple_choice' 
            ? "All questions should be multiple choice with 4 options each."
            : ($questionType === 'true_false' 
                ? "All questions should be true/false with 2 options: 'True' and 'False'."
                : "Mix of multiple choice and true/false questions.");

        $userMessage = "Create {$numberOfQuestions} quiz questions about: {$topic}. " .
                      "Difficulty: {$difficulty}. " .
                      "Question type: {$typeInstruction} " .
                      "Format: JSON array where each question is an object with 'question', 'options' (array), 'correct_answer' (0-based index), and 'explanation' fields.";

        $response = $this->callOpenAI($systemMessage, $userMessage, 3000);

        // Parse the response
        $quiz = json_decode($response, true);
        
        if (!is_array($quiz)) {
            // Try to extract JSON from markdown code blocks if present
            if (preg_match('/```(?:json)?\s*(\[.*?\])\s*```/s', $response, $matches)) {
                $quiz = json_decode($matches[1], true);
            } else {
                throw new \Exception('Failed to parse quiz response from AI');
            }
        }

        return $quiz;
    }

    /**
     * Call OpenAI API
     */
    private function callOpenAI(string $systemMessage, string $userMessage, int $maxTokens = 2000): string
    {
        $apiKey = env('OPENAI_API_KEY');
        
        $messages = [
            [
                'role' => 'system',
                'content' => $systemMessage
            ],
            [
                'role' => 'user',
                'content' => $userMessage
            ]
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        
        $postData = json_encode([
            'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => $maxTokens,
        ]);
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new \Exception('Connection error: ' . $curlError);
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            throw new \Exception('API error: ' . ($errorData['error']['message'] ?? 'Unknown error'));
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['choices'][0]['message']['content'])) {
            throw new \Exception('Unexpected API response structure');
        }
        
        return trim($data['choices'][0]['message']['content']);
    }

    /**
     * Parse slides from text response (fallback)
     */
    private function parseSlidesFromText(string $text, int $expectedCount): array
    {
        $slides = [];
        $lines = explode("\n", $text);
        $currentSlide = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Check if it's a slide title (usually starts with number or is all caps)
            if (preg_match('/^(?:Slide\s*\d+|#+\s*\d+\.?\s*|^\d+\.?\s*)(.+)$/i', $line, $matches)) {
                if ($currentSlide) {
                    $slides[] = $currentSlide;
                }
                $currentSlide = [
                    'title' => trim($matches[1]),
                    'content' => [],
                    'summary' => ''
                ];
            } elseif ($currentSlide && (strpos($line, '-') === 0 || strpos($line, '*') === 0 || strpos($line, '•') === 0)) {
                // Bullet point
                $currentSlide['content'][] = trim($line, '-*• ');
            } elseif ($currentSlide && !empty($line)) {
                // Regular content
                if (empty($currentSlide['content'])) {
                    $currentSlide['content'][] = $line;
                } else {
                    $currentSlide['summary'] .= $line . ' ';
                }
            }
        }
        
        if ($currentSlide) {
            $slides[] = $currentSlide;
        }
        
        // Ensure we have the expected number of slides
        while (count($slides) < $expectedCount) {
            $slides[] = [
                'title' => 'Slide ' . (count($slides) + 1),
                'content' => ['Content to be generated'],
                'summary' => ''
            ];
        }
        
        
        return array_slice($slides, 0, $expectedCount);
    }
    
    /**
     * Extract text from uploaded document
     */
    private function extractTextFromDocument($file): string
    {
        $extension = $file->getClientOriginalExtension();
        $filePath = $file->getRealPath();
        
        try {
            switch (strtolower($extension)) {
                case 'txt':
                    return file_get_contents($filePath);
                    
                case 'pdf':
                    // For PDF, we'll need a PDF parser library
                    // For now, return a placeholder
                    return "PDF content extraction requires additional library. Please use TXT or DOCX files for now, or the system will use the topic you provided.";
                    
                case 'doc':
                case 'docx':
                    // For DOCX, we'll need a DOCX parser library
                    // For now, return a placeholder
                    return "DOCX content extraction requires additional library. Please use TXT files for now, or the system will use the topic you provided.";
                    
                default:
                    throw new \Exception('Unsupported file format');
            }
        } catch (\Exception $e) {
            Log::error('Document extraction error: ' . $e->getMessage());
            throw new \Exception('Failed to extract text from document: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate slides from document content
     */
    private function generateSlidesFromDocument(string $documentText, int $numberOfSlides, string $detailLevel, string $topic): array
    {
        $apiKey = env('OPENAI_API_KEY');
        
        if (!$apiKey) {
            throw new \Exception('OpenAI API key not configured');
        }

        $systemMessage = "You are an educational content creator. Generate presentation slides in JSON format based on the provided document content. " .
                        "Each slide should have: title, content (main points as bullet points), and a brief summary. " .
                        "Return ONLY valid JSON array with no markdown formatting or code blocks.";

        // Limit document text to prevent token overflow
        $limitedText = substr($documentText, 0, 3000);
        
        $userMessage = "Based on this document content:\n\n{$limitedText}\n\n" .
                      "Create {$numberOfSlides} educational slides. " .
                      "Detail level: {$detailLevel}. " .
                      "Topic context: {$topic}. " .
                      "Format: JSON array where each slide is an object with 'title', 'content' (array of bullet points), and 'summary' fields. " .
                      "Make the content educational, clear, and well-structured based on the document.";

        $response = $this->callOpenAI($systemMessage, $userMessage, 4000);

        // Parse the response
        $slides = json_decode($response, true);
        
        if (!is_array($slides)) {
            // Try to extract JSON from markdown code blocks if present
            if (preg_match('/```(?:json)?\s*(\[.*?\])\s*```/s', $response, $matches)) {
                $slides = json_decode($matches[1], true);
            } else {
                // Fallback: create slides from text
                $slides = $this->parseSlidesFromText($response, $numberOfSlides);
            }
        }

        return $slides;
    }
    
    /**
     * Generate quiz from document content
     */
    private function generateQuizFromDocument(string $documentText, int $numberOfQuestions, string $difficulty, string $questionType, string $topic): array
    {
        $apiKey = env('OPENAI_API_KEY');
        
        if (!$apiKey) {
            throw new \Exception('OpenAI API key not configured');
        }

        $systemMessage = "You are an educational quiz creator. Generate quiz questions in JSON format based on the provided document content. " .
                        "Each question should have: question text, options (array of 4 options), correct_answer (index 0-3), and explanation. " .
                        "Return ONLY valid JSON array with no markdown formatting or code blocks.";

        $typeInstruction = $questionType === 'multiple_choice' 
            ? "All questions should be multiple choice with 4 options each."
            : ($questionType === 'true_false' 
                ? "All questions should be true/false with 2 options: 'True' and 'False'."
                : "Mix of multiple choice and true/false questions.");

        // Limit document text to prevent token overflow
        $limitedText = substr($documentText, 0, 3000);
        
        $userMessage = "Based on this document content:\n\n{$limitedText}\n\n" .
                      "Create {$numberOfQuestions} quiz questions. " .
                      "Difficulty: {$difficulty}. " .
                      "Question type: {$typeInstruction} " .
                      "Topic context: {$topic}. " .
                      "Format: JSON array where each question is an object with 'question', 'options' (array), 'correct_answer' (0-based index), and 'explanation' fields. " .
                      "Base the questions on the document content provided.";

        $response = $this->callOpenAI($systemMessage, $userMessage, 4000);

        // Parse the response
        $quiz = json_decode($response, true);
        
        if (!is_array($quiz)) {
            // Try to extract JSON from markdown code blocks if present
            if (preg_match('/```(?:json)?\s*(\[.*?\])\s*```/s', $response, $matches)) {
                $quiz = json_decode($matches[1], true);
            } else {
                throw new \Exception('Failed to parse quiz response from AI');
            }
        }

        return $quiz;
    }
}


