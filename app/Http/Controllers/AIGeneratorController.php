<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

                    // Generate slides from document using Gemini AI
                    $slides = $this->generateSlidesFromDocument($documentText, $numberOfSlides, $detailLevel, $topic);
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

            // Bullets
            $bodyShape = $slide->createRichTextShape();
            $bodyShape->setHeight(420)->setWidth(900)->setOffsetX(50)->setOffsetY(110);
            $bodyShape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $contentLines = $this->normalizeSlideContent($s['content'] ?? []);
            foreach ($contentLines as $idx => $line) {
                $p = $idx === 0 ? $bodyShape->getActiveParagraph() : $bodyShape->createParagraph();
                $p->getBulletStyle()->setBulletType(Bullet::TYPE_BULLET);
                $p->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $run = $bodyShape->createTextRun((string)$line);
                $run->getFont()->setSize(18)->setColor(new Color('FF111111'));
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
                $run = $bodyShape->createTextRun($label . (string)$opt);
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
                    // Preferred: use PDF parser library
                    try {
                        $parser = new PdfParser();
                        $pdf = $parser->parseFile($filePath);
                        $text = $pdf->getText();
                        if ($text && strlen(trim($text)) > 20) {
                            return $text;
                        }
                    } catch (\Throwable $e) {
                        Log::warning('PDF parser library failed, trying pdftotext binary: ' . $e->getMessage());
                    }

                    // Fallback: pdftotext binary if installed
                    if (function_exists('shell_exec')) {
                        $output = @shell_exec("pdftotext " . escapeshellarg($filePath) . " -");
                        if ($output && trim($output)) {
                            return $output;
                        }
                    }

                    throw new \Exception('Could not extract text from PDF. Please ensure the PDF is not scanned-only or install pdftotext.');

                case 'doc':
                case 'docx':
                    try {
                        $text = $this->extractTextFromWord($filePath);
                        if ($text && strlen(trim($text)) > 20) {
                            return $text;
                        }
                    } catch (\Throwable $e) {
                        Log::warning('DOCX/DOC parse failed: ' . $e->getMessage());
                    }

                    throw new \Exception('Could not extract text from DOC/DOCX. Please ensure the document is not password-protected or try saving as TXT.');

                default:
                    throw new \Exception('Unsupported file format. Please use TXT files.');
            }
        } catch (\Exception $e) {
            Log::error('Document extraction error: ' . $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Extract text content from DOC/DOCX using PhpWord
     */
    private function extractTextFromWord(string $filePath): string
    {
        $phpWord = WordIOFactory::load($filePath);
        $parts = [];

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $text = $this->getWordElementText($element);
                if ($text !== null && $text !== '') {
                    $parts[] = $text;
                }
            }
        }

        return trim(implode("\n", array_filter($parts)));
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

        // Limit document text to prevent token overflow
        $limitedText = substr($documentText, 0, 3000);

        $response = null;

        if ($openaiKey && strlen($openaiKey) > 50) {
            try {
                $systemMessage = "You are an educational content creator. Generate presentation slides in JSON format based on the provided document content. " .
                                "Each slide should have: title, content (main points as bullet points), and a brief summary. " .
                                "Return ONLY valid JSON array with no markdown formatting or code blocks.";

                $userMessage = "Based on this document content:\n\n{$limitedText}\n\n" .
                              "Create {$numberOfSlides} educational slides. " .
                              "Detail level: {$detailLevel}. " .
                              "Topic context: {$topic}. " .
                              "Format: JSON array where each slide is an object with 'title', 'content' (array of bullet points), and 'summary' fields. " .
                              "Make the content educational, clear, and well-structured based on the document.";

                $response = $this->callOpenAI($systemMessage, $userMessage, 4000);
            } catch (\Exception $e) {
                Log::warning('OpenAI API failed for document slides, trying Gemini: ' . $e->getMessage());
            }
        }

        if (!$response && $geminiKey && strlen($geminiKey) > 30) {
            try {
                $prompt = "You are an educational content creator. Based on this document content:\n\n{$limitedText}\n\n" .
                         "Create {$numberOfSlides} educational presentation slides.\n" .
                         "Detail level: {$detailLevel}\n" .
                         "Topic context: {$topic}\n\n" .
                         "Return ONLY a valid JSON array with no markdown formatting or code blocks. " .
                         "Each slide must be an object with:\n" .
                         "- 'title': string (slide title)\n" .
                         "- 'content': array of strings (bullet points)\n" .
                         "- 'summary': string (brief summary)\n\n" .
                         "Make the content educational, clear, and well-structured based on the document.";

                $response = $this->callGemini($prompt, 4000);
            } catch (\Exception $e) {
                Log::error('Gemini API also failed for document slides: ' . $e->getMessage());
            }
        }

        if (!$response) {
            // No demo mode - throw exception if both APIs fail
            throw new \Exception('Failed to generate slides from document. Please check your API configuration or try again later.');
        }        // Parse the response
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
        ]);

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
}
