<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\IOFactory as PptIOFactory;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Bullet;
use PhpOffice\PhpWord\Element\ListItem;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Text as WordText;
use PhpOffice\PhpWord\Element\TextBreak;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpWord\PhpWord;
use Smalot\PdfParser\Parser as PdfParser;

class AIGeneratorController extends Controller
{
    /**
     * Cached result of Gemini ListModels (per request lifecycle).
     */
    private ?array $geminiModelsCache = null;

    /**
     * Ensure the slides array size is exactly the requested count.
     *
     * Policy: no demo/placeholder. If AI returns fewer slides, we retry once with a strict instruction.
     * If still fewer, we return 400 with a clear error so the UI doesn't show partial results.
     */
    private function ensureExactSlidesCountOrThrow(array $slides, int $expectedCount, string $topic, string $detailLevel, ?string $sourceText = null): array
    {
        $normalized = $this->normalizeSlides($slides);
        if (count($normalized) === $expectedCount) {
            return $normalized;
        }

        // Too many: trim deterministically.
        if (count($normalized) > $expectedCount) {
            return array_slice($normalized, 0, $expectedCount);
        }

        // Too few: fail fast. Caller may have already retried.
        throw new \Exception("AI hanya menghasilkan " . count($normalized) . " slaid (diminta {$expectedCount}). Sila cuba lagi.");
    }

    /**
     * Normalize slides schema and remove invalid items.
     */
    private function normalizeSlides(array $slides): array
    {
        $normalized = array_values(array_filter(array_map(function ($s) {
            if (!is_array($s)) return null;
            $title = isset($s['title']) ? trim((string)$s['title']) : '';
            $content = $s['content'] ?? [];
            if (!is_array($content)) {
                $content = [trim((string)$content)];
            }
            $content = array_values(array_filter(array_map(fn($v) => trim((string)$v), $content), fn($v) => $v !== ''));
            $summary = isset($s['summary']) ? trim((string)$s['summary']) : '';
            if ($title === '' || count($content) === 0) {
                return null;
            }
            return [
                'title' => $title,
                'content' => $content,
                'summary' => $summary,
                'image_path' => $s['image_path'] ?? null, // Store image path for slides
            ];
        }, $slides), fn($v) => $v !== null));

        return $normalized;
    }

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
     * Show list of generated slides
     */
    public function showGeneratedSlides()
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        // Get all generated slide sets from session
        $slideSets = session('generated_slide_sets', []);
        if (!is_array($slideSets)) {
            $slideSets = [];
        }
        
        // Check if there's a currently generating set
        $sessionKey = 'slide_generation_' . $userId;
        $status = session($sessionKey . '_status', 'none');
        
        // If generating, add to list temporarily
        if ($status === 'generating') {
            // Check if generating set already exists
            $hasGenerating = false;
            foreach ($slideSets as $set) {
                if (isset($set['id']) && $set['id'] === 'generating') {
                    $hasGenerating = true;
                    break;
                }
            }
            
            if (!$hasGenerating) {
                $currentTopic = session('generated_slides_topic', 'Slaid Sedang Dijana...');
                $generatingSet = [
                    'id' => 'generating',
                    'topic' => $currentTopic,
                    'status' => 'generating',
                    'created_at' => now()->toDateTimeString(),
                    'slide_count' => 0,
                    'slides' => []
                ];
                array_unshift($slideSets, $generatingSet);
            }
        }

        return view('ai-generator.slaid-dijana', [
            'slideSets' => $slideSets,
            'status' => $status,
            'sessionKey' => $sessionKey
        ]);
    }

    /**
     * Show individual slide set detail
     */
    public function showSlideSet($id)
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $slideSets = session('generated_slide_sets', []);
        $slideSet = collect($slideSets)->firstWhere('id', $id);

        if (!$slideSet) {
            return redirect()->route('ai-generator.slaid-dijana')
                ->with('error', 'Slaid tidak ditemui.');
        }

        $slides = $slideSet['slides'] ?? [];
        $topic = $slideSet['topic'] ?? 'Generated Slides';
        
        // Check if this is the generating one
        $sessionKey = 'slide_generation_' . $userId;
        $status = ($id === 'generating') ? session($sessionKey . '_status', 'generating') : 'completed';

        return view('ai-generator.slaid-dijana-detail', compact('slides', 'topic', 'id', 'status', 'sessionKey'));
    }

    /**
     * Check generation status
     */
    public function checkGenerationStatus(Request $request)
    {
        $userId = session('user_id');
        if (!$userId) {
            return response()->json(['status' => 'error', 'message' => 'Not authenticated'], 401);
        }

        $sessionKey = 'slide_generation_' . $userId;
        $status = session($sessionKey . '_status', 'none');
        
        if ($status === 'completed') {
            // Get the most recent slide set
            $slideSets = session('generated_slide_sets', []);
            $latestSet = !empty($slideSets) ? $slideSets[0] : null;
            
            if ($latestSet && ($latestSet['status'] ?? 'completed') === 'completed') {
                return response()->json([
                    'status' => 'completed',
                    'slides' => $latestSet['slides'] ?? [],
                    'topic' => $latestSet['topic'] ?? 'Generated Slides',
                    'set_id' => $latestSet['id'] ?? null
                ]);
            }
            
            // Fallback to old session format
            $slides = session('generated_slides', []);
            $topic = session('generated_slides_topic', 'Generated Slides');
            return response()->json([
                'status' => 'completed',
                'slides' => $slides,
                'topic' => $topic
            ]);
        } elseif ($status === 'generating') {
            return response()->json(['status' => 'generating']);
        } elseif ($status === 'error') {
            $error = session($sessionKey . '_error', 'Unknown error');
            return response()->json(['status' => 'error', 'message' => $error]);
        }

        return response()->json(['status' => 'none']);
    }

    /**
     * Generate slides using AI
     */
    public function generateSlides(Request $request)
    {
        // Increase execution time limit for slide generation (especially with images)
        set_time_limit(300); // 5 minutes should be enough for even 50 slides with images
        
        // Mark generation as started IMMEDIATELY
        $userId = session('user_id');
        $sessionKey = null;
        if ($userId) {
            $sessionKey = 'slide_generation_' . $userId;
            session([$sessionKey . '_status' => 'generating']);
            session()->save(); // Force save session immediately
        }
        
        try {
            $request->validate([
                'topic' => 'nullable|string|max:500',
                'number_of_slides' => 'nullable|integer|min:1|max:50',
                'detail_level' => 'nullable|string|in:basic,intermediate,advanced',
                'document' => 'nullable|file|mimes:pdf,doc,docx,txt|max:20480', // Max 20MB
                'page_from' => 'nullable|integer|min:1',
                'page_to' => 'nullable|integer|min:1',
            ]);

            // Validate page_to >= page_from if both are provided
            $pageFrom = $request->input('page_from');
            $pageTo = $request->input('page_to');
            if ($pageFrom !== null && $pageTo !== null && $pageTo < $pageFrom) {
                if ($sessionKey) {
                    session([$sessionKey . '_status' => 'error', $sessionKey . '_error' => 'End page must be greater than or equal to start page.']);
                }
                return response()->json([
                    'status' => 400,
                    'message' => 'End page must be greater than or equal to start page.',
                ], 400);
            }
            
            // Get parameters
            $topic = $request->input('topic');
            $numberOfSlides = $request->input('number_of_slides', 10);
            $detailLevel = $request->input('detail_level', 'intermediate');

            // Create a placeholder slide set with "generating" status
            if ($userId) {
                $slideSets = session('generated_slide_sets', []);
                // Remove any existing "generating" entry
                $slideSets = array_filter($slideSets, fn($set) => ($set['id'] ?? '') !== 'generating');
                $slideSets = array_values($slideSets);
                
                $placeholderTopic = $topic ?: 'Slaid Sedang Dijana...';
                $placeholderSet = [
                    'id' => 'generating',
                    'topic' => $placeholderTopic,
                    'slides' => [],
                    'status' => 'generating',
                    'created_at' => now()->toDateTimeString(),
                    'slide_count' => 0
                ];
                array_unshift($slideSets, $placeholderSet);
                session(['generated_slide_sets' => $slideSets]);
                session()->save(); // Force save
            }
            
            // Save file temporarily if uploaded (for background processing)
            // We need to preserve original filename and extension for proper extraction
            $tempDocumentPath = null;
            $originalFilename = null;
            $originalExtension = null;
            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $originalFilename = $file->getClientOriginalName();
                $originalExtension = $file->getClientOriginalExtension();
                $tempDocumentPath = $file->store('temp_slide_generation', 'local');
                // Store original filename in session for background processing
                session([
                    'temp_document_original_name_' . basename($tempDocumentPath) => $originalFilename,
                    'temp_document_original_ext_' . basename($tempDocumentPath) => $originalExtension
                ]);
                session()->save();
            }
            
            // Return immediate response (202 Accepted) and continue processing in background
            // For XAMPP/development server, we need to ensure response is sent immediately
            ignore_user_abort(true);
            set_time_limit(300); // Allow long execution
            
            // Disable output buffering if enabled
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            // Send response headers and content immediately
            header('Content-Type: application/json', true);
            header('HTTP/1.1 202 Accepted', true);
            http_response_code(202);
            
            echo json_encode([
                'status' => 202,
                'message' => 'Generation started',
                'redirect' => route('ai-generator.slaid-dijana'),
            ]);
            
            // Force flush all output buffers
            if (ob_get_level() > 0) {
                ob_end_flush();
            }
            flush();
            
            // For FastCGI, finish the request
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }
            
            // Continue processing in background after response is sent
            try {
                // Check if document is uploaded
                if ($tempDocumentPath) {
                    try {
                        // Load file from temp storage
                        $fullPath = storage_path('app/' . $tempDocumentPath);
                        
                        // Get original filename and extension from session
                        $tempFileKey = basename($tempDocumentPath);
                        $originalFilename = session('temp_document_original_name_' . $tempFileKey, $tempFileKey);
                        $originalExtension = session('temp_document_original_ext_' . $tempFileKey, pathinfo($tempFileKey, PATHINFO_EXTENSION));
                        
                        Log::info('Starting document extraction (background)', [
                            'temp_path' => $tempDocumentPath,
                            'original_filename' => $originalFilename,
                            'original_extension' => $originalExtension,
                            'size' => file_exists($fullPath) ? filesize($fullPath) : 0,
                            'page_from' => $pageFrom,
                            'page_to' => $pageTo
                        ]);
                        
                        if (!file_exists($fullPath)) {
                            throw new \Exception('Temporary file not found. Please try uploading again.');
                        }
                        
                        // Create a proper UploadedFile object with original filename
                        // We need to use test mode but provide the original filename
                        $uploadedFile = new \Illuminate\Http\UploadedFile(
                            $fullPath,
                            $originalFilename, // Use original filename, not temp path
                            mime_content_type($fullPath),
                            null,
                            true // test mode
                        );
                        
                        $documentText = $this->extractTextFromDocument($uploadedFile, $pageFrom, $pageTo);

                    Log::info('Document extraction completed', [
                        'extracted_length' => strlen($documentText)
                    ]);

                    // Validate that we got actual text content
                    if (strlen($documentText) < 50) {
                        return response()->json([
                            'status' => 400,
                            'message' => 'Document appears to be empty or too short. Please ensure your document contains readable text' . 
                                        ($pageFrom || $pageTo ? ' in the specified page range.' : '.'),
                        ], 400);
                    }

                    // If no topic provided, use document content
                    if (empty($topic)) {
                        $topic = 'Document analysis';
                    }

                    Log::info('Starting slide generation', [
                        'topic' => $topic,
                        'number_of_slides' => $numberOfSlides,
                        'detail_level' => $detailLevel
                    ]);

                    // Generate slides from document using Gemini AI
                    $slides = $this->generateSlidesFromDocument($documentText, $numberOfSlides, $detailLevel, $topic);
                    
                    Log::info('Slide generation completed', [
                        'slides_count' => count($slides)
                    ]);
                    } catch (\Exception $e) {
                        Log::error('Document slide generation failed (background)', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        
                        // Mark as error in session instead of returning
                        if ($userId && $sessionKey) {
                            session([
                                $sessionKey . '_status' => 'error',
                                $sessionKey . '_error' => $e->getMessage()
                            ]);
                            $slideSets = session('generated_slide_sets', []);
                            $slideSets = array_filter($slideSets, fn($set) => ($set['id'] ?? '') !== 'generating');
                            session(['generated_slide_sets' => array_values($slideSets)]);
                            session()->save();
                        }
                        
                        // Clean up temp file and session data
                        if ($tempDocumentPath) {
                            $tempFileKey = basename($tempDocumentPath);
                            session()->forget(['temp_document_original_name_' . $tempFileKey, 'temp_document_original_ext_' . $tempFileKey]);
                            $fullPath = storage_path('app/' . $tempDocumentPath);
                            if (file_exists($fullPath)) {
                                @unlink($fullPath);
                            }
                        }
                        return; // Exit background processing
                    } catch (\Throwable $e) {
                        Log::error('Fatal error in document slide generation (background)', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        
                        // Mark as error in session
                        if ($userId && $sessionKey) {
                            session([
                                $sessionKey . '_status' => 'error',
                                $sessionKey . '_error' => $e->getMessage()
                            ]);
                            $slideSets = session('generated_slide_sets', []);
                            $slideSets = array_filter($slideSets, fn($set) => ($set['id'] ?? '') !== 'generating');
                            session(['generated_slide_sets' => array_values($slideSets)]);
                            session()->save();
                        }
                        
                        // Clean up temp file and session data
                        if ($tempDocumentPath) {
                            $tempFileKey = basename($tempDocumentPath);
                            session()->forget(['temp_document_original_name_' . $tempFileKey, 'temp_document_original_ext_' . $tempFileKey]);
                            $fullPath = storage_path('app/' . $tempDocumentPath);
                            if (file_exists($fullPath)) {
                                @unlink($fullPath);
                            }
                        }
                        return; // Exit background processing
                    }
                } else {
                    // Validate topic is required if no document
                    if (empty($topic)) {
                        // Mark as error in session
                        if ($userId && $sessionKey) {
                            session([
                                $sessionKey . '_status' => 'error',
                                $sessionKey . '_error' => 'Either topic or document is required'
                            ]);
                            $slideSets = session('generated_slide_sets', []);
                            $slideSets = array_filter($slideSets, fn($set) => ($set['id'] ?? '') !== 'generating');
                            session(['generated_slide_sets' => array_values($slideSets)]);
                            session()->save();
                        }
                        return; // Exit background processing
                    }

                    $slides = $this->generateSlidesWithAI($topic, $numberOfSlides, $detailLevel);
                }

                // Generate images for each slide
                try {
                    $slides = $this->generateImagesForSlides($slides, $topic);
                } catch (\Exception $e) {
                    Log::warning('Image generation failed, continuing without images: ' . $e->getMessage());
                    // Continue without images if generation fails
                }

                // Store slides in session and mark as completed
                if (!$userId) {
                    $userId = session('user_id');
                }
                if (!$sessionKey && $userId) {
                    $sessionKey = 'slide_generation_' . $userId;
                }
            
            // Get existing slide sets
            $slideSets = session('generated_slide_sets', []);
            
            // Remove any "generating" entry
            $slideSets = array_filter($slideSets, fn($set) => $set['id'] !== 'generating');
            $slideSets = array_values($slideSets); // Re-index
            
            // Create new slide set entry
            $newSet = [
                'id' => uniqid('slideset_', true),
                'topic' => $topic,
                'slides' => $slides,
                'status' => 'completed',
                'created_at' => now()->toDateTimeString(),
                'slide_count' => count($slides)
            ];
            
            // Add to beginning of array (newest first)
            array_unshift($slideSets, $newSet);
            
            // Keep only last 50 slide sets to prevent session bloat
            $slideSets = array_slice($slideSets, 0, 50);
            
            session([
                'generated_slide_sets' => $slideSets,
                'generated_slides' => $slides, // Keep for backward compatibility
                'generated_slides_topic' => $topic, // Keep for backward compatibility
                $sessionKey . '_status' => 'completed'
            ]);
                session()->save(); // Force save session
                
                // Clean up temp file
                if ($tempDocumentPath && file_exists(storage_path('app/' . $tempDocumentPath))) {
                    @unlink(storage_path('app/' . $tempDocumentPath));
                }

            } catch (\Exception $e) {
                Log::error('AI Slide generation error (background): ' . $e->getMessage(), [
                    'exception' => $e,
                    'trace' => $e->getTraceAsString()
                ]);

                // Mark generation as error in session
                if (isset($userId) && $userId && isset($sessionKey) && $sessionKey) {
                    session([
                        $sessionKey . '_status' => 'error',
                        $sessionKey . '_error' => $e->getMessage()
                    ]);
                    // Remove generating placeholder
                    $slideSets = session('generated_slide_sets', []);
                    $slideSets = array_filter($slideSets, fn($set) => ($set['id'] ?? '') !== 'generating');
                    session(['generated_slide_sets' => array_values($slideSets)]);
                    session()->save();
                }
                
                // Clean up temp file on error
                if ($tempDocumentPath && file_exists(storage_path('app/' . $tempDocumentPath))) {
                    @unlink(storage_path('app/' . $tempDocumentPath));
                }
            }
        } catch (\Exception $e) {
            // Error before background processing starts
            Log::error('AI Slide generation setup error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            // Mark generation as error in session
            if (isset($userId) && $userId && isset($sessionKey) && $sessionKey) {
                session([
                    $sessionKey . '_status' => 'error',
                    $sessionKey . '_error' => $e->getMessage()
                ]);
                session()->save();
            }
            
            // Clean up temp file
            if (isset($tempDocumentPath) && $tempDocumentPath && file_exists(storage_path('app/' . $tempDocumentPath))) {
                @unlink(storage_path('app/' . $tempDocumentPath));
            }

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
                'document' => 'nullable|file|mimes:pdf,doc,docx,txt|max:20480', // Max 20MB
            ]);

            $topic = $request->input('topic');
            $numberOfQuestions = $request->input('number_of_questions', 10);
            $difficulty = $request->input('difficulty', 'medium');
            $questionType = $request->input('question_type', 'multiple_choice');

            // Check if document is uploaded
            if ($request->hasFile('document')) {
                try {
                    $documentText = $this->extractTextFromDocument($request->file('document'));

                    // Validate that we got actual text content
                    if (strlen($documentText) < 50) {
                        return response()->json([
                            'status' => 400,
                            'message' => 'Document appears to be empty or too short. Please ensure your document contains readable text.',
                        ], 400);
                    }

                    // If no topic provided, use document content
                    if (empty($topic)) {
                        $topic = 'Document analysis';
                    }

                    // Generate quiz from document using Gemini AI
                    $quiz = $this->generateQuizFromDocument($documentText, $numberOfQuestions, $difficulty, $questionType, $topic);
                } catch (\Exception $e) {
                    // Return error to user instead of falling back to demo mode
                    return response()->json([
                        'status' => 400,
                        'message' => $e->getMessage(),
                    ], 400);
                }
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
     * Export generated quiz as PDF (print HTML), DOCX, or TXT.
     *
     * Expects JSON body:
     * - quiz: array of { question: string, options: string[], correct_answer: int, explanation?: string }
     * - topic: optional string
     * - format: pdf|docx|txt
     */
    public function exportQuiz(Request $request)
    {
        $request->validate([
            'quiz' => 'required|array|min:1',
            'quiz.*.question' => 'required|string',
            'quiz.*.options' => 'required|array|min:1',
            'quiz.*.options.*' => 'required|string',
            'quiz.*.correct_answer' => 'required|integer|min:0',
            'quiz.*.explanation' => 'nullable|string',
            'topic' => 'nullable|string|max:500',
            'format' => 'required|string|in:pdf,docx,txt,pptx',
        ]);

        $quiz = $request->input('quiz');
        $topic = $request->input('topic', 'quiz');
        $format = strtolower($request->input('format'));

        // Keep filename safe for Windows + browsers
        $safeTopic = trim($topic) !== '' ? $topic : 'quiz';
        $baseName = Str::slug(mb_substr($safeTopic, 0, 80));
        if ($baseName === '') {
            $baseName = 'quiz';
        }

        try {
            return match ($format) {
                'txt' => $this->exportQuizAsTxt($quiz, $baseName),
                'docx' => $this->exportQuizAsDocx($quiz, $baseName),
                'pdf' => $this->exportQuizAsPrintHtml($quiz, $baseName),
                'pptx' => $this->exportQuizAsPptx($quiz, $baseName),
                default => response()->json(['status' => 400, 'message' => 'Unsupported export format'], 400),
            };
        } catch (\Throwable $e) {
            Log::error('Quiz export error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'status' => 500,
                'message' => 'Failed to export quiz. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    private function exportQuizAsTxt(array $quiz, string $baseName)
    {
        $out = [];
        foreach ($quiz as $i => $q) {
            $out[] = 'Question ' . ($i + 1) . ':';
            $out[] = (string)($q['question'] ?? '');
            $out[] = '';

            $options = is_array($q['options'] ?? null) ? $q['options'] : [];
            foreach ($options as $idx => $opt) {
                $label = chr(65 + $idx);
                $out[] = $label . '. ' . (string)$opt;
            }

            $correct = (int)($q['correct_answer'] ?? -1);
            if ($correct >= 0 && $correct < count($options)) {
                $out[] = '';
                $out[] = 'Correct Answer: ' . chr(65 + $correct);
            }

            $explanation = trim((string)($q['explanation'] ?? ''));
            if ($explanation !== '') {
                $out[] = 'Explanation: ' . $explanation;
            }

            $out[] = str_repeat('-', 40);
        }

        $text = implode("\n", $out);
        $fileName = $baseName . '.txt';
        return response($text, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    private function exportQuizAsPptx(array $quiz, string $baseName)
    {
        $ppt = $this->buildQuizPptx($quiz);

        $tmpPath = storage_path('app/tmp');
        if (!is_dir($tmpPath)) {
            @mkdir($tmpPath, 0775, true);
        }

        $filePath = $tmpPath . '/' . $baseName . '-' . uniqid('', true) . '.pptx';
        $writer = PptIOFactory::createWriter($ppt, 'PowerPoint2007');
        $writer->save($filePath);

        if (ob_get_length()) ob_end_clean();
        return response()->download($filePath, $baseName . '.pptx')->deleteFileAfterSend(true);
    }

    private function exportQuizAsDocx(array $quiz, string $baseName)
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(12);
        $section = $phpWord->addSection();

        foreach ($quiz as $i => $q) {
            $section->addText('Question ' . ($i + 1), ['bold' => true, 'size' => 16]);
            $section->addText((string)($q['question'] ?? ''), ['bold' => true]);
            $section->addTextBreak(1);

            $options = is_array($q['options'] ?? null) ? $q['options'] : [];
            foreach ($options as $idx => $opt) {
                $label = chr(65 + $idx) . '. ';
                $section->addText($label . (string)$opt);
            }

            $correct = (int)($q['correct_answer'] ?? -1);
            if ($correct >= 0 && $correct < count($options)) {
                $section->addTextBreak(1);
                $section->addText('Correct Answer: ' . chr(65 + $correct), ['bold' => true]);
            }

            $explanation = trim((string)($q['explanation'] ?? ''));
            if ($explanation !== '') {
                $section->addText('Explanation: ' . $explanation, ['italic' => true]);
            }

            if ($i < count($quiz) - 1) {
                $section->addPageBreak();
            }
        }

        $writer = WordIOFactory::createWriter($phpWord, 'Word2007');
        $tmpPath = tempnam(sys_get_temp_dir(), 'quiz_');
        if ($tmpPath === false) {
            throw new \Exception('Unable to create temporary file');
        }
        $filePath = $tmpPath . '.docx';
        @unlink($tmpPath);
        $writer->save($filePath);

        $fileName = $baseName . '.docx';
        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }

    private function exportQuizAsPrintHtml(array $quiz, string $baseName)
    {
        $html = $this->buildQuizHtml($quiz);
        $fileName = $baseName . '.html';

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    private function buildQuizHtml(array $quiz): string
    {
        $escape = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $parts = [];
        $parts[] = '<!doctype html><html><head><meta charset="utf-8">';
        $parts[] = '<style>';
        $parts[] = 'body{font-family:DejaVu Sans, Arial, sans-serif;font-size:12px;color:#111;padding:18px;}';
        $parts[] = 'h1{font-size:18px;margin:0 0 10px 0;}';
        $parts[] = '.q{page-break-after:always;margin-bottom:18px;}';
        $parts[] = '.q:last-child{page-break-after:auto;}';
        $parts[] = '.question{font-weight:700;margin:0 0 8px 0;}';
        $parts[] = 'ol{margin:0 0 10px 18px;padding:0;}';
        $parts[] = 'li{margin:0 0 6px 0;}';
        $parts[] = '.answer{margin-top:10px;font-weight:700;}';
        $parts[] = '.explain{margin-top:6px;font-style:italic;color:#333;}';
        $parts[] = '</style></head><body>';
        $parts[] = '<h1>Generated Quiz</h1>';

        foreach ($quiz as $i => $q) {
            $parts[] = '<div class="q">';
            $parts[] = '<div class="question">' . $escape('Question ' . ($i + 1) . ': ') . $escape($q['question'] ?? '') . '</div>';

            $options = is_array($q['options'] ?? null) ? (array)$q['options'] : [];
            $parts[] = '<ol type="A">';
            foreach ($options as $opt) {
                $parts[] = '<li>' . $escape($opt) . '</li>';
            }
            $parts[] = '</ol>';

            $correct = (int)($q['correct_answer'] ?? -1);
            if ($correct >= 0 && $correct < count($options)) {
                $parts[] = '<div class="answer">' . $escape('Correct Answer: ' . chr(65 + $correct)) . '</div>';
            }

            $explanation = trim((string)($q['explanation'] ?? ''));
            if ($explanation !== '') {
                $parts[] = '<div class="explain">' . $escape('Explanation: ' . $explanation) . '</div>';
            }

            $parts[] = '</div>';
        }

        $parts[] = '</body></html>';
        return implode('', $parts);
    }

    /**
     * Export generated slides as PDF, DOCX, or TXT.
     *
     * Expects JSON body:
     * - slides: array of { title: string, content: string[]|string, summary?: string }
     * - topic: optional string
     * - format: pdf|docx|txt
     */
    public function exportSlides(Request $request)
    {
        $request->validate([
            'slides' => 'required|array|min:1',
            'slides.*.title' => 'required|string',
            'slides.*.content' => 'required',
            'slides.*.summary' => 'nullable|string',
            'topic' => 'nullable|string|max:500',
            'format' => 'required|string|in:pdf,docx,txt,pptx',
        ]);

        $slides = $request->input('slides');
        $topic = $request->input('topic', 'slides');
        $format = strtolower($request->input('format'));

        // Keep filename safe for Windows + browsers
        $safeTopic = trim($topic) !== '' ? $topic : 'slides';
        $baseName = Str::slug(mb_substr($safeTopic, 0, 80));
        if ($baseName === '') {
            $baseName = 'slides';
        }

        try {
            return match ($format) {
                'txt' => $this->exportSlidesAsTxt($slides, $baseName),
                'docx' => $this->exportSlidesAsDocx($slides, $baseName),
                'pptx' => $this->exportSlidesAsPptx($slides, $baseName),
                'pdf' => $this->exportSlidesAsPdf($slides, $baseName),
                default => response()->json(['status' => 400, 'message' => 'Unsupported export format'], 400),
            };
        } catch (\Throwable $e) {
            Log::error('Slide export error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'status' => 500,
                'message' => 'Failed to export slides. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    private function exportSlidesAsPptx(array $slides, string $baseName)
    {
        $ppt = $this->buildSlidesPptx($slides);

        $tmpPath = storage_path('app/tmp');
        if (!is_dir($tmpPath)) {
            @mkdir($tmpPath, 0775, true);
        }

        $filePath = $tmpPath . '/' . $baseName . '-' . uniqid('', true) . '.pptx';
        $writer = PptIOFactory::createWriter($ppt, 'PowerPoint2007');
        $writer->save($filePath);

        if (ob_get_length()) ob_end_clean();
        return response()->download($filePath, $baseName . '.pptx')->deleteFileAfterSend(true);
    }

    /**
     * Build a PPTX presentation from slides JSON.
     * Notes policy: we store notes as JSON too; for now, slide notes come from `summary`.
     */
    private function buildSlidesPptx(array $slides): PhpPresentation
    {
        $slides = $this->normalizeSlides($slides);
        if (count($slides) === 0) {
            throw new \Exception('Tiada slaid untuk dieksport.');
        }

        $ppt = new PhpPresentation();

        // Use the first slide instead of removing it
        $firstSlide = $ppt->getActiveSlide();

        foreach ($slides as $i => $s) {
            $slide = ($i === 0) ? $firstSlide : $ppt->createSlide();

            // Title
            $titleShape = $slide->createRichTextShape();
            $titleShape->setHeight(60)->setWidth(920)->setOffsetX(30)->setOffsetY(20);
            $titleShape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $titleRun = $titleShape->createTextRun((string)($s['title'] ?? ('Slide ' . ($i + 1))));
            $titleRun->getFont()->setBold(true)->setSize(28)->setColor(new Color('FF111111'));

            // Check if image exists to adjust layout
            $imagePath = $s['image_path'] ?? null;
            $hasImage = $imagePath && file_exists($imagePath);

            // Bullets - adjust width if image is present
            $bodyShape = $slide->createRichTextShape();
            $bodyWidth = $hasImage ? 450 : 900; // Smaller width when image is present
            $bodyShape->setHeight(420)->setWidth($bodyWidth)->setOffsetX(50)->setOffsetY(110);
            $bodyShape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $contentLines = $this->normalizeSlideContent($s['content'] ?? []);
            foreach ($contentLines as $idx => $line) {
                $p = $idx === 0 ? $bodyShape->getActiveParagraph() : $bodyShape->createParagraph();
                $p->getBulletStyle()->setBulletType(Bullet::TYPE_BULLET);
                $p->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                // Create TextRun on the paragraph, not the shape, to ensure proper association
                $run = $p->createTextRun((string)$line);
                $run->getFont()->setSize(18)->setColor(new Color('FF111111'));
            }

            // Add image if available
            if ($hasImage) {
                try {
                    // Create image shape on the right side of the slide
                    $imageShape = $slide->createDrawingShape();
                    $imageShape->setPath($imagePath);
                    $imageShape->setWidth(450)->setHeight(340); // Maintain aspect ratio
                    $imageShape->setOffsetX(520)->setOffsetY(110); // Position on right side
                    $imageShape->setName('Slide Image');
                } catch (\Exception $e) {
                    Log::warning('Failed to add image to slide: ' . $e->getMessage());
                    // Continue without image if there's an error
                }
            }

            // Notes (from summary)
            $summary = trim((string)($s['summary'] ?? ''));
            if ($summary !== '') {
                $note = $slide->getNote();
                $notesShape = $note->createRichTextShape();
                $notesShape->setHeight(600)->setWidth(900)->setOffsetX(30)->setOffsetY(30);
                $notesShape->createTextRun($summary)->getFont()->setSize(14)->setColor(new Color('FF111111'));
            }
        }

        return $ppt;
    }

    /**
     * Build a PPTX presentation from quiz JSON.
     * Notes policy: store explanation as slide notes.
     */
    private function buildQuizPptx(array $quiz): PhpPresentation
    {
        if (!is_array($quiz) || count($quiz) === 0) {
            throw new \Exception('Tiada soalan kuiz untuk dieksport.');
        }

        $ppt = new PhpPresentation();
        $firstSlide = $ppt->getActiveSlide();

        foreach (array_values($quiz) as $i => $q) {
            $slide = ($i === 0) ? $firstSlide : $ppt->createSlide();

            $question = trim((string)($q['question'] ?? ''));
            if ($question === '') {
                continue;
            }

            $titleShape = $slide->createRichTextShape();
            $titleShape->setHeight(80)->setWidth(920)->setOffsetX(30)->setOffsetY(20);
            $titleRun = $titleShape->createTextRun('Soalan ' . ($i + 1));
            $titleRun->getFont()->setBold(true)->setSize(24)->setColor(new Color('FF111111'));

            $qShape = $slide->createRichTextShape();
            $qShape->setHeight(140)->setWidth(900)->setOffsetX(50)->setOffsetY(90);
            $qShape->createTextRun($question)->getFont()->setSize(20)->setColor(new Color('FF111111'));

            $options = is_array($q['options'] ?? null) ? $q['options'] : [];
            $bodyShape = $slide->createRichTextShape();
            $bodyShape->setHeight(360)->setWidth(900)->setOffsetX(50)->setOffsetY(240);
            foreach (array_values($options) as $idx => $opt) {
                $label = chr(65 + $idx) . '. ';
                $p = $idx === 0 ? $bodyShape->getActiveParagraph() : $bodyShape->createParagraph();
                $p->getBulletStyle()->setBulletType(Bullet::TYPE_BULLET);
                // Create TextRun on the paragraph, not the shape, to ensure proper association
                $run = $p->createTextRun($label . (string)$opt);
                $run->getFont()->setSize(18)->setColor(new Color('FF111111'));
            }

            $correct = (int)($q['correct_answer'] ?? -1);
            $explanation = trim((string)($q['explanation'] ?? ''));
            $notesText = '';
            if ($correct >= 0 && $correct < count($options)) {
                $notesText .= 'Jawapan betul: ' . chr(65 + $correct) . "\n";
            }
            if ($explanation !== '') {
                $notesText .= "Penjelasan: " . $explanation;
            }
            $notesText = trim($notesText);
            if ($notesText !== '') {
                $note = $slide->getNote();
                $notesShape = $note->createRichTextShape();
                $notesShape->setHeight(600)->setWidth(900)->setOffsetX(30)->setOffsetY(30);
                $notesShape->createTextRun($notesText)->getFont()->setSize(14)->setColor(new Color('FF111111'));
            }
        }

        return $ppt;
    }

    private function normalizeSlideContent($content): array
    {
        if (is_array($content)) {
            return array_values(array_filter(array_map('strval', $content), fn($v) => trim($v) !== ''));
        }

        $text = trim((string) $content);
        if ($text === '') {
            return [];
        }

        // Split common bullet/line separators
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $lines = array_map(fn($l) => trim($l, " \t-•*"), $lines);
        $lines = array_values(array_filter($lines, fn($l) => $l !== ''));
        return $lines ?: [$text];
    }

    private function exportSlidesAsTxt(array $slides, string $baseName)
    {
        $out = [];
        foreach ($slides as $i => $slide) {
            $out[] = 'Slide ' . ($i + 1) . ': ' . (string)($slide['title'] ?? '');
            $contentLines = $this->normalizeSlideContent($slide['content'] ?? []);
            foreach ($contentLines as $line) {
                $out[] = '- ' . $line;
            }
            $summary = trim((string)($slide['summary'] ?? ''));
            if ($summary !== '') {
                $out[] = 'Summary: ' . $summary;
            }
            $out[] = ''; // blank line between slides
        }

        $text = implode("\n", $out);
        $fileName = $baseName . '.txt';
        return response($text, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    private function exportSlidesAsDocx(array $slides, string $baseName)
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(12);

        $section = $phpWord->addSection();

        foreach ($slides as $i => $slide) {
            $title = (string)($slide['title'] ?? '');
            $section->addText('Slide ' . ($i + 1) . ': ' . $title, ['bold' => true, 'size' => 16]);

            $contentLines = $this->normalizeSlideContent($slide['content'] ?? []);
            foreach ($contentLines as $line) {
                $section->addListItem($line, 0, null, 'listStyle');
            }

            $summary = trim((string)($slide['summary'] ?? ''));
            if ($summary !== '') {
                $section->addText('Summary: ' . $summary, ['italic' => true, 'size' => 11]);
            }

            // page break between slides except the last
            if ($i < count($slides) - 1) {
                $section->addPageBreak();
            }
        }

        $writer = WordIOFactory::createWriter($phpWord, 'Word2007');
        $tmpPath = tempnam(sys_get_temp_dir(), 'slides_');
        if ($tmpPath === false) {
            throw new \Exception('Unable to create temporary file');
        }
        $filePath = $tmpPath . '.docx';
        // tempnam creates a file; save to a new name and delete the original
        @unlink($tmpPath);
        $writer->save($filePath);

        $fileName = $baseName . '.docx';
        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }

    private function exportSlidesAsPdf(array $slides, string $baseName)
    {
        // Lightweight fallback: provide HTML that prints well.
        // (Proper server-side PDF rendering can be added once a PDF library is installed via Composer.)
        $html = $this->buildSlidesHtml($slides);
        $fileName = $baseName . '.html';

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    private function buildSlidesHtml(array $slides): string
    {
        $escape = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $parts = [];
        $parts[] = '<!doctype html><html><head><meta charset="utf-8">';
        $parts[] = '<style>';
        $parts[] = 'body{font-family:DejaVu Sans, Arial, sans-serif;font-size:12px;color:#111;}';
        $parts[] = '.slide{page-break-after:always;padding:18px;}';
        $parts[] = '.slide:last-child{page-break-after:auto;}';
        $parts[] = 'h1{font-size:18px;margin:0 0 10px 0;}';
        $parts[] = 'ul{margin:0 0 10px 18px;padding:0;}';
        $parts[] = 'li{margin:0 0 6px 0;}';
        $parts[] = '.summary{font-style:italic;color:#333;margin-top:8px;}';
        $parts[] = '</style></head><body>';

        foreach ($slides as $i => $slide) {
            $title = $escape('Slide ' . ($i + 1) . ': ' . ($slide['title'] ?? ''));
            $parts[] = '<div class="slide">';
            $parts[] = '<h1>' . $title . '</h1>';

            $contentLines = $this->normalizeSlideContent($slide['content'] ?? []);
            $parts[] = '<ul>';
            foreach ($contentLines as $line) {
                $parts[] = '<li>' . $escape($line) . '</li>';
            }
            $parts[] = '</ul>';

            $summary = trim((string)($slide['summary'] ?? ''));
            if ($summary !== '') {
                $parts[] = '<div class="summary">' . $escape('Summary: ' . $summary) . '</div>';
            }
            $parts[] = '</div>';
        }

        $parts[] = '</body></html>';
        return implode('', $parts);
    }

    /**
     * Generate slides using AI (Gemini or OpenAI) or Demo Mode
     */
    private function generateSlidesWithAI(string $topic, int $numberOfSlides, string $detailLevel): array
    {
        // Prefer OpenAI first (official), fallback to Gemini if configured.
        $openaiKey = env('OPENAI_API_KEY');
        $geminiKey = env('GEMINI_API_KEY');

        if ($openaiKey && strlen($openaiKey) > 20) {
            try {
                return $this->generateSlidesWithOpenAI($topic, $numberOfSlides, $detailLevel);
            } catch (\Exception $e) {
                Log::warning('OpenAI API failed, trying Gemini: ' . $e->getMessage());
            }
        }

        if ($geminiKey && strlen($geminiKey) > 20) {
            try {
                return $this->generateSlidesWithGemini($topic, $numberOfSlides, $detailLevel);
            } catch (\Exception $e) {
                Log::error('Gemini API also failed: ' . $e->getMessage());
            }
        }

        // No demo mode
        throw new \Exception('Failed to generate slides. Please check your API configuration or try again later.');
    }    /**
     * Generate slides using Gemini API
     */
    private function generateSlidesWithGemini(string $topic, int $numberOfSlides, string $detailLevel): array
    {
        $prompt = $this->buildSlidesJsonPrompt($topic, $numberOfSlides, $detailLevel);

        // First try
        $response = $this->callGemini($prompt, 3000, ['responseMimeType' => 'application/json']);
        $slides = $this->parseJsonArrayOrNull($response);

        // Retry once with stronger constraint if the model replied with non-JSON.
        if (!is_array($slides)) {
            $retryPrompt = $prompt . "\n\nIMPORTANT: Output MUST be valid JSON only. Do NOT include any explanations, markdown, code fences, or extra text. Start with '[' and end with ']'.";
            $response = $this->callGemini($retryPrompt, 3000, ['responseMimeType' => 'application/json']);
            $slides = $this->parseJsonArrayOrNull($response);
        }

        if (!is_array($slides)) {
            Log::warning('Slides JSON parse failed (raw AI response preview)', [
                'response_preview' => mb_substr(trim($response ?? ''), 0, 600),
            ]);
            throw new \Exception('AI returned non-JSON slides. Please try again.');
        }

        $slides = $this->normalizeSlides($slides);

        if (count($slides) === 0) {
            throw new \Exception('AI returned an unstructured response. Please try again.');
        }

        // Enforce exact count (no partial results)
        return $this->ensureExactSlidesCountOrThrow($slides, $numberOfSlides, $topic, $detailLevel);
    }

    private function buildSlidesJsonPrompt(string $topic, int $numberOfSlides, string $detailLevel): string
    {
        // Give the model an explicit contract + example shape.
        // Keep it short to reduce risk of model adding extra prose.
        $schemaExample = '[{"title":"Slide 1 title","content":["Point 1","Point 2"],"summary":"Short summary"}]';

        return "You are an educational content creator.\n" .
            "Task: Create {$numberOfSlides} presentation slides about: {$topic}.\n" .
            "Detail level: {$detailLevel}.\n\n" .
            "OUTPUT FORMAT (STRICT):\n" .
            "Return ONLY a valid JSON array (no markdown, no code fences, no extra text).\n" .
            "Each array item must be an object with keys: title (string), content (array of strings), summary (string).\n" .
            "Example: {$schemaExample}\n\n" .
            "Rules:\n" .
            "- content must contain 3-7 bullet points\n" .
            "- keep language clear and educational\n" .
        "- do not include citations or URLs unless necessary\n" .
        "- IMPORTANT: output MUST include EXACTLY {$numberOfSlides} items in the JSON array";
    }

    /**
     * Attempts to decode a JSON array from a response.
     * Returns decoded array on success, or null on failure.
     */
    private function parseJsonArrayOrNull(string $response): ?array
    {
        $trim = trim($response);

        // First, direct decode
        $decoded = json_decode($trim, true);
        if (is_array($decoded)) {
            // Some models may return an object wrapper, e.g. {"slides": [...]}
            if (array_is_list($decoded)) {
                return $decoded;
            }
            if (isset($decoded['slides']) && is_array($decoded['slides'])) {
                return $decoded['slides'];
            }
            return $decoded;
        }

        // Remove markdown fences if present
        if (preg_match('/```(?:json)?\s*(.*?)\s*```/s', $trim, $m)) {
            $inner = trim($m[1]);
            $decoded = json_decode($inner, true);
            if (is_array($decoded)) {
                if (array_is_list($decoded)) {
                    return $decoded;
                }
                if (isset($decoded['slides']) && is_array($decoded['slides'])) {
                    return $decoded['slides'];
                }
                return $decoded;
            }
        }

        // Try to extract the first JSON array block by matching the outermost [...]
        $start = strpos($trim, '[');
        $end = strrpos($trim, ']');
        if ($start !== false && $end !== false && $end > $start) {
            $slice = substr($trim, $start, $end - $start + 1);
            $decoded = json_decode($slice, true);
            if (is_array($decoded)) {
                if (array_is_list($decoded)) {
                    return $decoded;
                }
                if (isset($decoded['slides']) && is_array($decoded['slides'])) {
                    return $decoded['slides'];
                }
                return $decoded;
            }
        }

        // Try to extract object JSON too (e.g. {"slides": [...]})
        $objStart = strpos($trim, '{');
        $objEnd = strrpos($trim, '}');
        if ($objStart !== false && $objEnd !== false && $objEnd > $objStart) {
            $slice = substr($trim, $objStart, $objEnd - $objStart + 1);
            $decoded = json_decode($slice, true);
            if (is_array($decoded)) {
                if (isset($decoded['slides']) && is_array($decoded['slides'])) {
                    return $decoded['slides'];
                }
                if (array_is_list($decoded)) {
                    return $decoded;
                }
            }
        }

        return null;
    }

    /**
     * Generate slides using OpenAI API
     */
    private function generateSlidesWithOpenAI(string $topic, int $numberOfSlides, string $detailLevel): array
    {
        $systemMessage = "You are an educational content creator. " .
            "Return ONLY valid JSON (no markdown, no code fences, no extra text).";

        $userMessage = "Create {$numberOfSlides} educational slides about: {$topic}.\n" .
            "Detail level: {$detailLevel}.\n\n" .
            "Strict output format: a JSON array of slide objects.\n" .
            "Each slide object keys:\n" .
            "- title: string\n" .
            "- content: array of strings (3-7 bullet points)\n" .
            "- summary: string\n\n" .
            "Start your response with '[' and end with ']'.";

        $response = $this->callOpenAI($systemMessage, $userMessage, 3000);
        // Parse the response
        $slides = json_decode($response, true);

        if (!is_array($slides)) {
            // Try to extract JSON from markdown code blocks if present
            if (preg_match('/```(?:json)?\s*(\[.*?\])\s*```/s', $response, $matches)) {
                $slides = json_decode($matches[1], true);
            } else {
                // Try extract outermost JSON array
                $start = strpos($response, '[');
                $end = strrpos($response, ']');
                if ($start !== false && $end !== false && $end > $start) {
                    $slice = substr($response, $start, $end - $start + 1);
                    $slides = json_decode($slice, true);
                }
            }
        }

        if (!is_array($slides)) {
            // Avoid placeholders: if the model didn't return JSON, fail clearly.
            throw new \Exception('AI returned non-JSON slides. Please try again.');
        }

        $slides = $this->normalizeSlides($slides);

        if (count($slides) === 0) {
            throw new \Exception('AI returned an unstructured response. Please try again.');
        }

        // Enforce exact count (no partial results)
        return $this->ensureExactSlidesCountOrThrow($slides, $numberOfSlides, $topic, $detailLevel);
    }

    /**
     * Generate quiz using AI (Gemini or OpenAI) or Demo Mode
     */
    private function generateQuizWithAI(string $topic, int $numberOfQuestions, string $difficulty, string $questionType): array
    {
        // Prefer OpenAI first (official), fallback to Gemini if configured.
        $openaiKey = env('OPENAI_API_KEY');
        $geminiKey = env('GEMINI_API_KEY');

        if ($openaiKey && strlen($openaiKey) > 20) {
            try {
                return $this->generateQuizWithOpenAI($topic, $numberOfQuestions, $difficulty, $questionType);
            } catch (\Exception $e) {
                Log::warning('OpenAI API failed, trying Gemini: ' . $e->getMessage());
            }
        }

        if ($geminiKey && strlen($geminiKey) > 20) {
            try {
                return $this->generateQuizWithGemini($topic, $numberOfQuestions, $difficulty, $questionType);
            } catch (\Exception $e) {
                Log::error('Gemini API also failed: ' . $e->getMessage());
            }
        }

        // No demo mode
        throw new \Exception('Failed to generate quiz. Please check your API configuration or try again later.');
    }    /**
     * Generate quiz using Gemini API
     */
    private function generateQuizWithGemini(string $topic, int $numberOfQuestions, string $difficulty, string $questionType): array
    {
        $typeInstruction = $questionType === 'multiple_choice'
            ? "All questions should be multiple choice with 4 options each."
            : ($questionType === 'true_false'
                ? "All questions should be true/false with 2 options: 'True' and 'False'."
                : "Mix of multiple choice and true/false questions.");

        $prompt = "You are an educational quiz creator. Create {$numberOfQuestions} quiz questions about: {$topic}\n\n" .
                 "Difficulty: {$difficulty}\n" .
                 "Question type: {$typeInstruction}\n\n" .
                 "Return ONLY a valid JSON array with no markdown formatting or code blocks. " .
                 "Each question must be an object with:\n" .
                 "- 'question': string (the question text)\n" .
                 "- 'options': array of strings (answer options)\n" .
                 "- 'correct_answer': number (0-based index of correct option)\n" .
                 "- 'explanation': string (why the answer is correct)";

        $response = $this->callGemini($prompt, 3000);

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
     * Generate quiz using OpenAI API
     */
    private function generateQuizWithOpenAI(string $topic, int $numberOfQuestions, string $difficulty, string $questionType): array
    {
        $systemMessage = "You are an educational quiz creator. Generate quiz questions in JSON format. " .
                        "Each question should have: question text, options (array of 4 options), correct_answer (index 0-3), and explanation. " .
                        "Return ONLY valid JSON array with no markdown formatting or code blocks.";

        $typeInstruction = $questionType === 'multiple_choice'
            ? "All questions should be multiple choice with 4 options each."
            : ($questionType === 'true_false'
                ? "All questions should be true/false with 2 options: 'True' and 'False'."
                : "Mix of multiple choice and true/false questions.");        $userMessage = "Create {$numberOfQuestions} quiz questions about: {$topic}. " .
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

        if (!$apiKey || strlen($apiKey) < 20) {
            throw new \Exception('OpenAI API key not configured properly. Please add a valid OPENAI_API_KEY to your .env file. You can get one from https://platform.openai.com/account/api-keys');
        }

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
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($postData === false) {
            $jsonError = json_last_error_msg();
            throw new \Exception('Failed to encode JSON for OpenAI request: ' . $jsonError);
        }

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
            $errorMessage = $errorData['error']['message'] ?? 'Unknown error';

            // Provide helpful error messages
            if (strpos($errorMessage, 'Incorrect API key') !== false) {
                throw new \Exception('Invalid OpenAI API key. Please update your OPENAI_API_KEY in the .env file with a valid key from https://platform.openai.com/account/api-keys');
            }

            throw new \Exception('API error: ' . $errorMessage);
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

        // If we couldn't detect any slide structure, fail fast.
        if (count($slides) === 0) {
            throw new \Exception('AI returned an unstructured response. Please try again.');
        }

        // If we detected some slides but fewer than expected, just return what we have.
        // (Better than showing fake placeholder "Content to be generated".)
        return array_slice($slides, 0, min(count($slides), $expectedCount));
    }

    /**
     * Extract text from uploaded document
     * @param \Illuminate\Http\UploadedFile $file
     * @param int|null $pageFrom Starting page number (1-based, null = from beginning)
     * @param int|null $pageTo Ending page number (1-based, null = to end)
     * @return string
     */
    private function extractTextFromDocument($file, ?int $pageFrom = null, ?int $pageTo = null): string
    {
        $extension = $file->getClientOriginalExtension();
        $filePath = $file->getRealPath();

        try {
            switch (strtolower($extension)) {
                case 'txt':
                    return $this->extractTextFromTxt($filePath, $pageFrom, $pageTo);

                case 'pdf':
                    return $this->extractTextFromPdf($filePath, $pageFrom, $pageTo);

                case 'doc':
                case 'docx':
                    return $this->extractTextFromWord($filePath, $pageFrom, $pageTo);

                default:
                    throw new \Exception('Unsupported file format. Please use PDF, DOCX, DOC, or TXT files.');
            }
        } catch (\Exception $e) {
            Log::error('Document extraction error: ' . $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Get pdftotext executable path (tries multiple common locations)
     */
    private function getPdftotextPath(): ?string
    {
        // Try common Windows installation paths
        $possiblePaths = [
            'C:\\poppler\\poppler-25.12.0\\Library\\bin\\pdftotext.exe',
            'C:\\poppler\\poppler-24.08.0\\Library\\bin\\pdftotext.exe',
            'C:\\poppler\\poppler-23.11.0\\Library\\bin\\pdftotext.exe',
            'pdftotext.exe', // Try PATH
            'pdftotext', // Try PATH (without .exe)
        ];
        
        foreach ($possiblePaths as $path) {
            // If it's a full path, check if file exists
            if (strpos($path, '\\') !== false || strpos($path, '/') !== false) {
                if (file_exists($path) && is_executable($path)) {
                    return $path;
                }
            } else {
                // For PATH-based commands, try executing --version to see if it works
                $testCommand = escapeshellarg($path) . ' -v 2>&1';
                $testOutput = @shell_exec($testCommand);
                if ($testOutput && strpos($testOutput, 'not recognized') === false && strpos($testOutput, 'not found') === false) {
                    return $path;
                }
            }
        }
        
        return null;
    }

    /**
     * Extract text from PDF with optional page range
     * Note: Page range extraction requires pdftotext binary. If not available, extracts all text.
     */
    private function extractTextFromPdf(string $filePath, ?int $pageFrom = null, ?int $pageTo = null): string
    {
        try {
            // If page range is specified, try pdftotext binary first (only reliable way to extract specific pages)
            if (($pageFrom !== null || $pageTo !== null) && function_exists('shell_exec')) {
                $pdftotextPath = $this->getPdftotextPath();
                
                if ($pdftotextPath) {
                    $start = ($pageFrom !== null) ? $pageFrom : 1;
                    $end = ($pageTo !== null) ? $pageTo : 10000; // Large number for "to end"
                    
                    $command = escapeshellarg($pdftotextPath) . " -f {$start} -l {$end} " . escapeshellarg($filePath) . " - 2>&1";
                    Log::info('Attempting pdftotext extraction', [
                        'command' => $command,
                        'page_from' => $start,
                        'page_to' => $end,
                    ]);
                    
                    $output = @shell_exec($command);
                    
                    if ($output && trim($output) && strpos($output, 'command not found') === false && strpos($output, 'not recognized') === false) {
                        // Check if there are actual errors in the output
                        if (strpos($output, 'Error') === false && strpos($output, 'error') === false) {
                            $extractedText = trim($output);
                            if (strlen($extractedText) > 20) {
                                Log::info('Successfully extracted text using pdftotext', [
                                    'text_length' => strlen($extractedText),
                                ]);
                                return $extractedText;
                            }
                        }
                    }
                    
                    Log::warning("pdftotext command executed but output was invalid", [
                        'output_length' => strlen($output ?? ''),
                        'output_preview' => substr($output ?? '', 0, 200),
                    ]);
                } else {
                    Log::warning("pdftotext binary not found in common locations or PATH");
                }
                
                // If pdftotext failed but page range was specified, warn user
                Log::warning("Page range extraction via pdftotext failed or pdftotext not available. Extracting all text instead.");
                
                // For very large PDFs, warn that extraction might be slow or fail
                $fileSize = filesize($filePath);
                if ($fileSize > 10 * 1024 * 1024) { // > 10MB
                    Log::warning("Large PDF detected ({$fileSize} bytes). Full text extraction may take time or fail. Consider installing pdftotext for page range support.");
                }
            }

            // Extract all text using PDF parser (fallback or when no page range specified)
            try {
                $parser = new PdfParser();
                $pdf = $parser->parseFile($filePath);
                $text = $pdf->getText();

                if ($text && strlen(trim($text)) > 20) {
                    // Limit extracted text to prevent memory issues with very large PDFs (max 500KB of text)
                    if (strlen($text) > 500000) {
                        Log::warning("PDF text extraction exceeded 500KB, truncating to prevent memory issues.");
                        $text = mb_substr($text, 0, 500000, 'UTF-8');
                    }
                    
                    Log::info('PDF text extraction completed', ['text_length' => strlen($text)]);
                    
                    if ($pageFrom !== null || $pageTo !== null) {
                        Log::info("Extracted all PDF text. Page range filtering requires pdftotext binary to be installed.");
                    }
                    return $text;
                }
                
                Log::warning('PDF text extraction returned empty or too short text');
            } catch (\Throwable $e) {
                Log::error('PDF parser failed: ' . $e->getMessage(), [
                    'exception' => $e,
                    'file_path' => $filePath,
                    'file_size' => file_exists($filePath) ? filesize($filePath) : 'unknown'
                ]);
                
                throw new \Exception('Failed to extract text from PDF. The PDF may be too large or corrupted: ' . $e->getMessage());
            }

            throw new \Exception('Could not extract text from PDF. Please ensure the PDF is not scanned-only or install pdftotext for page range support.');
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Could not extract') === 0) {
                throw $e;
            }
            Log::error('PDF extraction error: ' . $e->getMessage(), ['exception' => $e]);
            throw new \Exception('Could not extract text from PDF. Please ensure the PDF is not scanned-only or install pdftotext.');
        }
    }

    /**
     * Extract text from TXT file with optional line range (approximating pages)
     */
    private function extractTextFromTxt(string $filePath, ?int $pageFrom = null, ?int $pageTo = null): string
    {
        $content = file_get_contents($filePath);
        
        if ($pageFrom === null && $pageTo === null) {
            return $content;
        }

        // For TXT files, approximate pages as ~50 lines per page
        $lines = explode("\n", $content);
        $totalLines = count($lines);
        $linesPerPage = 50;
        $totalPages = ceil($totalLines / $linesPerPage);

        $startLine = ($pageFrom !== null) ? max(0, ($pageFrom - 1) * $linesPerPage) : 0;
        $endLine = ($pageTo !== null) ? min($totalLines - 1, $pageTo * $linesPerPage - 1) : ($totalLines - 1);

        if ($startLine > $endLine) {
            throw new \Exception("Invalid page range for text file.");
        }

        $selectedLines = array_slice($lines, $startLine, $endLine - $startLine + 1);
        return implode("\n", $selectedLines);
    }

    /**
     * Extract text content from DOC/DOCX using PhpWord with optional section range
     * @param string $filePath
     * @param int|null $pageFrom Starting section number (1-based, null = from beginning)
     * @param int|null $pageTo Ending section number (1-based, null = to end)
     * @return string
     */
    private function extractTextFromWord(string $filePath, ?int $pageFrom = null, ?int $pageTo = null): string
    {
        $phpWord = WordIOFactory::load($filePath);
        $sections = $phpWord->getSections();
        $totalSections = count($sections);

        // Validate section range
        if ($pageFrom !== null && ($pageFrom < 1 || $pageFrom > $totalSections)) {
            throw new \Exception("Invalid start section. Document has {$totalSections} sections.");
        }
        if ($pageTo !== null && ($pageTo < 1 || $pageTo > $totalSections)) {
            throw new \Exception("Invalid end section. Document has {$totalSections} sections.");
        }
        if ($pageFrom !== null && $pageTo !== null && $pageFrom > $pageTo) {
            throw new \Exception("Start section cannot be greater than end section.");
        }

        // Determine section range (0-based indexing)
        $startIndex = ($pageFrom !== null) ? max(0, $pageFrom - 1) : 0;
        $endIndex = ($pageTo !== null) ? min($totalSections - 1, $pageTo - 1) : ($totalSections - 1);

        $parts = [];
        for ($i = $startIndex; $i <= $endIndex; $i++) {
            $section = $sections[$i];
            foreach ($section->getElements() as $element) {
                $text = $this->getWordElementText($element);
                if ($text !== null && $text !== '') {
                    $parts[] = $text;
                }
            }
        }

        $result = trim(implode("\n", array_filter($parts)));
        
        if (strlen($result) < 20) {
            throw new \Exception('Could not extract text from DOC/DOCX. Please ensure the document is not password-protected or try saving as TXT.');
        }

        return $result;
    }

    /**
     * Recursively convert PhpWord elements into plain text
     */
    private function getWordElementText($element): string
    {
        if ($element instanceof WordText) {
            return $element->getText();
        }

        if ($element instanceof TextRun) {
            $pieces = [];
            foreach ($element->getElements() as $child) {
                $childText = $this->getWordElementText($child);
                if ($childText !== '') {
                    $pieces[] = $childText;
                }
            }
            return implode('', $pieces);
        }

        if ($element instanceof TextBreak) {
            return "\n";
        }

        if ($element instanceof ListItem) {
            return '- ' . $this->getWordElementText($element->getTextObject());
        }

        if ($element instanceof Table) {
            $rows = [];
            foreach ($element->getRows() as $row) {
                foreach ($row->getCells() as $cell) {
                    foreach ($cell->getElements() as $cellElement) {
                        $cellText = $this->getWordElementText($cellElement);
                        if ($cellText !== '') {
                            $rows[] = $cellText;
                        }
                    }
                }
            }
            return implode("\n", $rows);
        }

        // Fallback for any element exposing getText
        if (method_exists($element, 'getText')) {
            return (string) $element->getText();
        }

        return '';
    }

    /**
     * Generate slides from document content
     */
    private function generateSlidesFromDocument(string $documentText, int $numberOfSlides, string $detailLevel, string $topic): array
    {
        // Prefer OpenAI first, fallback to Gemini if needed
        $openaiKey = env('OPENAI_API_KEY');
        $geminiKey = env('GEMINI_API_KEY');

        // Clean and sanitize document text
        $documentText = mb_convert_encoding($documentText, 'UTF-8', 'UTF-8');
        // Remove control characters except newlines, tabs, and carriage returns
        $documentText = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $documentText);
        // Limit document text to prevent token overflow (using mb_substr for proper UTF-8 handling)
        $limitedText = mb_substr($documentText, 0, 3000, 'UTF-8');
        // Remove any remaining problematic characters that might break JSON (remove characters above U+007F that aren't valid UTF-8)
        $limitedText = mb_convert_encoding($limitedText, 'UTF-8', 'UTF-8');
        if (empty(trim($limitedText))) {
            throw new \Exception('Document text is empty or contains no valid content after sanitization.');
        }

        $response = null;

        if ($openaiKey && strlen($openaiKey) > 50) {
            try {
                $systemMessage = "You are an educational content creator. Generate presentation slides in JSON format based on the provided document content. " .
                                "Each slide should have: title, content (main points as bullet points), and a brief summary. " .
                                "Return ONLY valid JSON array with no markdown formatting or code blocks.";

                $userMessage = "Based on this document content:\n\n{$limitedText}\n\n" .
                              "Create EXACTLY {$numberOfSlides} educational slides. " .
                              "Detail level: {$detailLevel}. " .
                              "Topic context: {$topic}. " .
                              "Format: JSON array where each slide is an object with 'title', 'content' (array of bullet points), and 'summary' fields. " .
                              "IMPORTANT: You MUST return EXACTLY {$numberOfSlides} slides in the JSON array. " .
                              "Make the content educational, clear, and well-structured based on the document.";

                $response = $this->callOpenAI($systemMessage, $userMessage, 4000);
            } catch (\Exception $e) {
                Log::warning('OpenAI API failed for document slides, trying Gemini: ' . $e->getMessage());
            }
        }

        if (!$response && $geminiKey && strlen($geminiKey) > 30) {
            try {
                $prompt = "You are an educational content creator. Based on this document content:\n\n{$limitedText}\n\n" .
                         "Create EXACTLY {$numberOfSlides} educational presentation slides.\n" .
                         "Detail level: {$detailLevel}\n" .
                         "Topic context: {$topic}\n\n" .
                         "Return ONLY a valid JSON array with no markdown formatting or code blocks. " .
                         "Each slide must be an object with:\n" .
                         "- 'title': string (slide title)\n" .
                         "- 'content': array of strings (bullet points)\n" .
                         "- 'summary': string (brief summary)\n\n" .
                         "IMPORTANT: You MUST return EXACTLY {$numberOfSlides} slides in the JSON array.\n\n" .
                         "Make the content educational, clear, and well-structured based on the document.";

                $response = $this->callGemini($prompt, 4000);
                
                // Check if we got the right number of slides, retry once if not
                $tempSlides = json_decode($response, true);
                if (is_array($tempSlides) && count($this->normalizeSlides($tempSlides)) < $numberOfSlides) {
                    Log::info('First attempt generated fewer slides, retrying with stronger prompt');
                    $retryPrompt = $prompt . "\n\nCRITICAL: You must generate EXACTLY {$numberOfSlides} slides. Count them carefully. If the document doesn't have enough content for {$numberOfSlides} distinct slides, create more detailed breakdowns or split topics into multiple slides.";
                    $response = $this->callGemini($retryPrompt, 4000);
                }
            } catch (\Exception $e) {
                Log::error('Gemini API also failed for document slides: ' . $e->getMessage());
            }
        }

        if (!$response) {
            // No demo mode - throw exception if both APIs fail
            throw new \Exception('Failed to generate slides from document. Please check your API configuration or try again later.');
        }

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

        // Validate and ensure exact slide count
        return $this->ensureExactSlidesCountOrThrow($slides, $numberOfSlides, $topic, $detailLevel, $limitedText);
    }

    /**
     * Generate quiz from document content
     */
    private function generateQuizFromDocument(string $documentText, int $numberOfQuestions, string $difficulty, string $questionType, string $topic): array
    {
        // Prefer OpenAI first, fallback to Gemini if needed
        $openaiKey = env('OPENAI_API_KEY');
        $geminiKey = env('GEMINI_API_KEY');
        $typeInstruction = $questionType === 'multiple_choice'
            ? "All questions should be multiple choice with 4 options each."
            : ($questionType === 'true_false'
                ? "All questions should be true/false with 2 options: 'True' and 'False'."
                : "Mix of multiple choice and true/false questions.");

        // Limit document text to prevent token overflow
        $limitedText = substr($documentText, 0, 3000);

        $response = null;

        if ($openaiKey && strlen($openaiKey) > 50) {
            try {
                $systemMessage = "You are an educational quiz creator. Generate quiz questions in JSON format based on the provided document content. " .
                                "Each question should have: question text, options (array of 4 options), correct_answer (index 0-3), and explanation. " .
                                "Return ONLY valid JSON array with no markdown formatting or code blocks.";

                $userMessage = "Based on this document content:\n\n{$limitedText}\n\n" .
                              "Create {$numberOfQuestions} quiz questions. " .
                              "Difficulty: {$difficulty}. " .
                              "Question type: {$typeInstruction} " .
                              "Topic context: {$topic}. " .
                              "Format: JSON array where each question is an object with 'question', 'options' (array), 'correct_answer' (0-based index), and 'explanation' fields. " .
                              "Base the questions on the document content provided.";

                $response = $this->callOpenAI($systemMessage, $userMessage, 4000);
            } catch (\Exception $e) {
                Log::warning('OpenAI API failed for document quiz, trying Gemini: ' . $e->getMessage());
            }
        }

        if (!$response && $geminiKey && strlen($geminiKey) > 30) {
            try {
                $prompt = "You are an educational quiz creator. Based on this document content:\n\n{$limitedText}\n\n" .
                         "Create {$numberOfQuestions} quiz questions.\n" .
                         "Difficulty: {$difficulty}\n" .
                         "Question type: {$typeInstruction}\n" .
                         "Topic context: {$topic}\n\n" .
                         "Return ONLY a valid JSON array with no markdown formatting or code blocks. " .
                         "Each question must be an object with:\n" .
                         "- 'question': string (the question text)\n" .
                         "- 'options': array of strings (answer options)\n" .
                         "- 'correct_answer': number (0-based index of correct option)\n" .
                         "- 'explanation': string (why the answer is correct)\n\n" .
                         "Base the questions on the document content provided.";

                $response = $this->callGemini($prompt, 4000);

                Log::info('Gemini API successfully generated quiz from document');
            } catch (\Exception $e) {
                Log::error('Gemini API also failed for document quiz: ' . $e->getMessage());
            }
        }

        if (!$response) {
            // No demo mode - throw exception if both APIs fail
            throw new \Exception('Failed to generate quiz from document. Please check your API configuration or try again later.');
        }

        // Parse the response
        $quiz = json_decode($response, true);

        if (!is_array($quiz)) {
            // Try to extract JSON from markdown code blocks if present
            if (preg_match('/```(?:json)?\s*(\[.*?\])\s*```/s', $response, $matches)) {
                $quiz = json_decode($matches[1], true);
            } else {
                // If parsing fails, return error instead of demo content
                Log::error('Failed to parse quiz JSON response');
                throw new \Exception('Failed to parse AI quiz response. Please try again or adjust your prompt.');
            }
        }

        return $quiz;
    }

    /**
     * Call Google Gemini API
     */
    private function callGemini(string $prompt, int $maxTokens = 2000, array $generationConfigOverrides = []): string
    {
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey || strlen($apiKey) < 20) {
            throw new \Exception('Gemini API key not configured properly. Please add a valid GEMINI_API_KEY to your .env file. You can get one from https://makersuite.google.com/app/apikey');
        }

        // Prefer configured model, but be ready to auto-select if it 404s.
        $model = env('GEMINI_MODEL', 'gemini-1.5-flash-latest');

        $response = $this->callGeminiGenerateContent($apiKey, $model, $prompt, $maxTokens, $generationConfigOverrides);
        if ($response !== null) {
            return $response;
        }

        // If we got here, the configured model likely 404'd. Try to find a supported model.
        $fallbackModel = $this->pickSupportedGeminiModel($apiKey);
        if (!$fallbackModel) {
            throw new \Exception('Gemini model not available for this API key/project. Please check your Gemini API access and model name.');
        }

        Log::warning('Configured Gemini model not available, falling back to: ' . $fallbackModel);
        $response = $this->callGeminiGenerateContent($apiKey, $fallbackModel, $prompt, $maxTokens, $generationConfigOverrides);
        if ($response === null) {
            throw new \Exception('Failed to call Gemini generateContent using fallback model: ' . $fallbackModel);
        }
        return $response;
    }

    /**
     * Performs a generateContent call. Returns text on success, null on "model not found".
     */
    private function callGeminiGenerateContent(string $apiKey, string $model, string $prompt, int $maxTokens, array $generationConfigOverrides = []): ?string
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $apiKey;

        $generationConfig = [
            'temperature' => 0.7,
            'topK' => 40,
            'topP' => 0.95,
            'maxOutputTokens' => $maxTokens,
        ];
        foreach ($generationConfigOverrides as $k => $v) {
            $generationConfig[$k] = $v;
        }

        $postData = json_encode([
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => $generationConfig
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($postData === false) {
            $jsonError = json_last_error_msg();
            throw new \Exception('Failed to encode JSON for Gemini request: ' . $jsonError);
        }

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => [
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
            $errorMessage = $errorData['error']['message'] ?? 'Unknown error';

            // Log the full error for debugging
            Log::error('Gemini API Error', [
                'http_code' => $httpCode,
                'error_data' => $errorData,
                'response' => $response
            ]);

            // Provide helpful error messages
            if (strpos($errorMessage, 'API key not valid') !== false || strpos($errorMessage, 'API_KEY_INVALID') !== false) {
                throw new \Exception('Invalid Gemini API key. Please update your GEMINI_API_KEY in the .env file with a valid key from https://aistudio.google.com/app/apikey');
            }

            // If model not found, suggest latest alias
            if ($httpCode === 404 && str_contains($errorMessage, 'not found')) {
                Log::warning('Gemini model not found: ' . $model . ' - will attempt automatic model selection.');
                return null;
            }

            throw new \Exception('Gemini API error: ' . $errorMessage . ' (HTTP ' . $httpCode . ')');
        }

        $data = json_decode($response, true);

        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            throw new \Exception('Unexpected Gemini API response structure');
        }


        return trim($data['candidates'][0]['content']['parts'][0]['text']);
    }

    /**
     * Call Gemini ListModels and return the first model that supports generateContent.
     *
     * We prefer "gemini-1.5-flash" variants when available.
     */
    private function pickSupportedGeminiModel(string $apiKey): ?string
    {
        $models = $this->listGeminiModels($apiKey);
        if (!$models) {
            return null;
        }

        $supportsGenerateContent = array_values(array_filter($models, function ($m) {
            $methods = $m['supportedGenerationMethods'] ?? [];
            return in_array('generateContent', $methods, true);
        }));

        if (!$supportsGenerateContent) {
            return null;
        }

        $preferred = array_values(array_filter($supportsGenerateContent, function ($m) {
            return isset($m['name']) && str_contains($m['name'], 'gemini-1.5-flash');
        }));

        $selected = $preferred[0]['name'] ?? $supportsGenerateContent[0]['name'] ?? null;
        // API returns names like "models/gemini-1.5-flash-latest"
        if (is_string($selected) && str_starts_with($selected, 'models/')) {
            return substr($selected, strlen('models/'));
        }
        return $selected;
    }

    /**
     * Calls Gemini ListModels and caches the result.
     */
    private function listGeminiModels(string $apiKey): array
    {
        if ($this->geminiModelsCache !== null) {
            return $this->geminiModelsCache;
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . $apiKey;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            Log::error('Gemini ListModels connection error: ' . $curlError);
            return $this->geminiModelsCache = [];
        }

        if ($httpCode !== 200) {
            Log::error('Gemini ListModels error', [
                'http_code' => $httpCode,
                'response' => $resp,
            ]);
            return $this->geminiModelsCache = [];
        }

        $data = json_decode($resp, true);
        $models = $data['models'] ?? [];
        return $this->geminiModelsCache = (is_array($models) ? $models : []);
    }

    /**
     * Generate demo slides without API (fallback mode)
     */
    private function generateDemoSlides(string $topic, int $numberOfSlides, string $detailLevel): array
    {
        $slides = [];

        for ($i = 1; $i <= $numberOfSlides; $i++) {
            $slides[] = [
                'title' => "Slide {$i}: " . ucfirst($topic) . " - Part {$i}",
                'content' => [
                    "Key concept #{$i} about {$topic}",
                    "Important point related to this topic",
                    "Practical application or example",
                    "Best practices to remember"
                ],
                'summary' => "This slide covers the essential aspects of {$topic} at {$detailLevel} level."
            ];
        }

        return $slides;
    }

    /**
     * Generate demo quiz without API (fallback mode)
     */
    private function generateDemoQuiz(string $topic, int $numberOfQuestions, string $difficulty, string $questionType): array
    {
        $quiz = [];

        for ($i = 1; $i <= $numberOfQuestions; $i++) {
            $isTrueFalse = $questionType === 'true_false' || ($questionType === 'mixed' && $i % 2 == 0);

            if ($isTrueFalse) {
                $quiz[] = [
                    'question' => "Statement #{$i}: {$topic} is an important concept in this field. True or False?",
                    'options' => ['True', 'False'],
                    'correct_answer' => 0,
                    'explanation' => "This is a demo question about {$topic}. In a real scenario with API keys, AI would generate actual educational content."
                ];
            } else {
                $quiz[] = [
                    'question' => "Question #{$i}: What is an important aspect of {$topic}?",
                    'options' => [
                        "Correct answer about {$topic}",
                        "Incorrect option A",
                        "Incorrect option B",
                        "Incorrect option C"
                    ],
                    'correct_answer' => 0,
                    'explanation' => "This is a demo question. With a valid API key, AI would generate real educational questions about {$topic}."
                ];
            }
        }

        return $quiz;
    }

    /**
     * Generate images for all slides based on their content
     */
    private function generateImagesForSlides(array $slides, string $topic): array
    {
        $apiKey = env('OPENAI_API_KEY');
        
        if (!$apiKey || strlen($apiKey) < 20) {
            Log::info('OpenAI API key not configured, skipping image generation');
            return $slides;
        }

        $tmpImageDir = storage_path('app/tmp/slide_images');
        if (!is_dir($tmpImageDir)) {
            @mkdir($tmpImageDir, 0775, true);
        }

        $startTime = time();
        $maxImageGenerationTime = 240; // Max 4 minutes for images (leaving 1 minute buffer)
        $slidesGenerated = 0;

        foreach ($slides as $index => &$slide) {
            // Check if we're running out of time
            $elapsed = time() - $startTime;
            if ($elapsed > $maxImageGenerationTime) {
                Log::warning("Image generation timeout approaching, skipping remaining images. Generated {$slidesGenerated} out of " . count($slides) . " slides.");
                break;
            }

            try {
                // Check if flowchart should be used
                $useFlowchart = $this->shouldUseFlowchart($slide);
                
                // Generate image description from slide content
                $imagePrompt = $this->createImagePromptFromSlide($slide, $topic);
                
                Log::info('Generated image prompt for slide', [
                    'slide_index' => $index,
                    'slide_title' => $slide['title'] ?? 'N/A',
                    'image_type' => $useFlowchart ? 'flowchart' : 'illustration',
                    'prompt_preview' => mb_substr($imagePrompt, 0, 200) . '...'
                ]);
                
                // Generate image using DALL-E
                $imageUrl = $this->generateImageWithDallE($imagePrompt);
                
                if ($imageUrl) {
                    // Download and save image
                    $imagePath = $this->downloadImageFromUrl($imageUrl, $tmpImageDir, 'slide_' . $index . '_' . uniqid());
                    if ($imagePath) {
                        $slide['image_path'] = $imagePath;
                        $slidesGenerated++;
                        Log::info('Image generated for slide', [
                            'slide_index' => $index,
                            'image_path' => $imagePath,
                            'elapsed_time' => time() - $startTime
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to generate image for slide ' . $index . ': ' . $e->getMessage());
                // Continue without image for this slide
            }
        }

        Log::info("Image generation completed. Generated {$slidesGenerated} images out of " . count($slides) . " slides in " . (time() - $startTime) . " seconds.");

        return $slides;
    }

    /**
     * Detect if a slide should have a flowchart based on its content
     */
    private function shouldUseFlowchart(array $slide): bool
    {
        $title = strtolower(trim($slide['title'] ?? ''));
        $contentArray = is_array($slide['content']) ? $slide['content'] : [];
        $summary = strtolower(trim($slide['summary'] ?? ''));
        
        // Keywords that indicate a flowchart would be appropriate
        $flowchartKeywords = [
            'process', 'workflow', 'algorithm', 'procedure', 'sequence', 'steps', 'step',
            'flow', 'flowchart', 'diagram', 'decision', 'if', 'then', 'else', 'loop',
            'iteration', 'cycle', 'pipeline', 'methodology', 'approach', 'method',
            'system', 'architecture', 'framework', 'model', 'pattern', 'pathway',
            'journey', 'timeline', 'phases', 'stages', 'levels', 'hierarchy',
            'input', 'output', 'start', 'end', 'begin', 'finish', 'first', 'next',
            'then', 'finally', 'after', 'before', 'during', 'order', 'sequence'
        ];
        
        // Check title
        foreach ($flowchartKeywords as $keyword) {
            if (strpos($title, $keyword) !== false) {
                return true;
            }
        }
        
        // Check content points
        $contentText = strtolower(implode(' ', $contentArray));
        foreach ($flowchartKeywords as $keyword) {
            if (strpos($contentText, $keyword) !== false) {
                return true;
            }
        }
        
        // Check summary
        foreach ($flowchartKeywords as $keyword) {
            if (strpos($summary, $keyword) !== false) {
                return true;
            }
        }
        
        // Check if content has numbered steps or sequential structure
        if (count($contentArray) >= 3) {
            $hasNumberedSteps = false;
            $hasSequentialWords = false;
            
            foreach ($contentArray as $point) {
                $pointLower = strtolower(trim($point));
                // Check for numbered steps (1., 2., Step 1, etc.)
                if (preg_match('/^\d+[\.\)]|^step\s+\d+/i', $pointLower)) {
                    $hasNumberedSteps = true;
                }
                // Check for sequential indicators
                if (preg_match('/\b(first|second|third|fourth|fifth|next|then|finally|last|initial|final)\b/i', $pointLower)) {
                    $hasSequentialWords = true;
                }
            }
            
            if ($hasNumberedSteps || $hasSequentialWords) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Create an image generation prompt from slide content
     * This creates a detailed, specific prompt that closely matches the slide content
     * Automatically detects if a flowchart would be more appropriate
     */
    private function createImagePromptFromSlide(array $slide, string $topic): string
    {
        $title = trim($slide['title'] ?? '');
        $contentArray = is_array($slide['content']) ? $slide['content'] : [];
        $summary = trim($slide['summary'] ?? '');
        
        // Check if this slide should use a flowchart
        $useFlowchart = $this->shouldUseFlowchart($slide);
        
        if ($useFlowchart) {
            // Generate flowchart-specific prompt
            return $this->createFlowchartPrompt($slide, $topic);
        }
        
        // Build a focused, detailed prompt based on the actual slide content
        $prompt = "Create an educational illustration that visually represents the following slide content: ";
        
        // Primary focus: Slide title (most important)
        if (!empty($title)) {
            $prompt .= "\"$title\". ";
        }
        
        // Secondary focus: Main content points
        if (!empty($contentArray) && count($contentArray) > 0) {
            $prompt .= "The slide covers these key points: ";
            foreach ($contentArray as $index => $point) {
                $point = trim($point);
                if (!empty($point)) {
                    $prompt .= $point;
                    if ($index < count($contentArray) - 1) {
                        $prompt .= ", ";
                    }
                }
            }
            $prompt .= ". ";
        }
        
        // Additional context: Summary if available
        if (!empty($summary)) {
            $prompt .= "Context: $summary. ";
        }
        
        // Overall topic context (if different from title)
        if (!empty($topic) && strtolower($title) !== strtolower($topic)) {
            $prompt .= "This is part of a presentation about: $topic. ";
        }
        
        // Style and technical requirements
        $prompt .= "Style: Professional educational illustration, clean and modern design, suitable for academic presentation. ";
        $prompt .= "The image should directly visualize the concepts mentioned in the slide content. ";
        $prompt .= "Use appropriate colors, diagrams, icons, or visual metaphors that represent the slide's subject matter. ";
        $prompt .= "Do not include any text, words, or numbers in the image. ";
        $prompt .= "Make it visually engaging and relevant to the specific slide content described above.";
        
        return $prompt;
    }

    /**
     * Create a flowchart-specific prompt for slides that describe processes or workflows
     */
    private function createFlowchartPrompt(array $slide, string $topic): string
    {
        $title = trim($slide['title'] ?? '');
        $contentArray = is_array($slide['content']) ? $slide['content'] : [];
        $summary = trim($slide['summary'] ?? '');
        
        $prompt = "Create a professional flowchart diagram that visually represents the following process/workflow: ";
        
        // Primary focus: Slide title
        if (!empty($title)) {
            $prompt .= "\"$title\". ";
        }
        
        // Detailed process steps
        if (!empty($contentArray) && count($contentArray) > 0) {
            $prompt .= "The process consists of these steps: ";
            foreach ($contentArray as $index => $point) {
                $point = trim($point);
                if (!empty($point)) {
                    // Remove step numbers if present for cleaner prompt
                    $cleanPoint = preg_replace('/^\d+[\.\)]\s*/', '', $point);
                    $cleanPoint = preg_replace('/^step\s+\d+[\.\):]\s*/i', '', $cleanPoint);
                    $prompt .= ($index + 1) . ". $cleanPoint";
                    if ($index < count($contentArray) - 1) {
                        $prompt .= "; ";
                    }
                }
            }
            $prompt .= ". ";
        }
        
        // Additional context
        if (!empty($summary)) {
            $prompt .= "Context: $summary. ";
        }
        
        // Overall topic
        if (!empty($topic) && strtolower($title) !== strtolower($topic)) {
            $prompt .= "This is part of a presentation about: $topic. ";
        }
        
        // Flowchart-specific instructions
        $prompt .= "Create a flowchart with: ";
        $prompt .= "rectangular boxes for process steps, ";
        $prompt .= "diamond shapes for decision points (if any), ";
        $prompt .= "rounded rectangles for start/end points, ";
        $prompt .= "arrows showing the flow direction between steps, ";
        $prompt .= "clear visual hierarchy and logical flow from top to bottom or left to right. ";
        $prompt .= "Style: Clean, professional flowchart design, suitable for educational presentation. ";
        $prompt .= "Use appropriate colors to distinguish different types of elements (processes, decisions, start/end). ";
        $prompt .= "The flowchart should clearly show the sequence and relationships between the steps described. ";
        $prompt .= "Do not include any text labels, words, or numbers in the flowchart boxes - use visual symbols, icons, or abstract representations instead.";
        
        return $prompt;
    }

    /**
     * Generate an image using DALL-E API
     */
    private function generateImageWithDallE(string $prompt): ?string
    {
        $apiKey = env('OPENAI_API_KEY');
        
        if (!$apiKey || strlen($apiKey) < 20) {
            return null;
        }

        try {
            $ch = curl_init('https://api.openai.com/v1/images/generations');
            
            $postData = json_encode([
                'model' => 'dall-e-3',
                'prompt' => $prompt,
                'n' => 1,
                'size' => '1024x1024',
                'quality' => 'standard',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json',
                ],
                CURLOPT_TIMEOUT => 90, // Increased timeout for DALL-E (can take longer)
                CURLOPT_CONNECTTIMEOUT => 15,
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
                $errorMessage = $errorData['error']['message'] ?? 'Unknown error';
                throw new \Exception('DALL-E API error: ' . $errorMessage);
            }

            $data = json_decode($response, true);
            
            if (isset($data['data'][0]['url'])) {
                return $data['data'][0]['url'];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('DALL-E image generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Download image from URL and save to local storage
     */
    private function downloadImageFromUrl(string $imageUrl, string $saveDir, string $filename): ?string
    {
        try {
            $imageData = @file_get_contents($imageUrl);
            
            if ($imageData === false) {
                Log::warning('Failed to download image from URL: ' . $imageUrl);
                return null;
            }

            // Determine file extension from content or default to png
            $extension = 'png';
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_buffer($finfo, $imageData);
            finfo_close($finfo);
            
            if ($mimeType === 'image/jpeg' || $mimeType === 'image/jpg') {
                $extension = 'jpg';
            } elseif ($mimeType === 'image/png') {
                $extension = 'png';
            } elseif ($mimeType === 'image/webp') {
                $extension = 'webp';
            }

            $filePath = $saveDir . '/' . $filename . '.' . $extension;
            
            if (@file_put_contents($filePath, $imageData) === false) {
                Log::warning('Failed to save image to: ' . $filePath);
                return null;
            }

            return $filePath;
        } catch (\Exception $e) {
            Log::error('Image download failed: ' . $e->getMessage());
            return null;
        }
    }
}
