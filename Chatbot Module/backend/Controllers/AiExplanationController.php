<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * AI Explanation Controller
 * User Story US025: AI explanations for highlighted text
 * Integrated with Google Gemini AI API
 */
class AiExplanationController extends Controller
{
    /**
     * Generate AI explanation for highlighted text
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function explain(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:1000',
            'context' => 'nullable|string|max:2000',
        ]);

        $highlightedText = $request->input('text');
        $context = $request->input('context', '');

        try {
            // Generate AI explanation using Google Gemini
            $explanation = $this->generateExplanation($highlightedText, $context);

            return response()->json([
                'success' => true,
                'explanation' => $explanation,
                'highlighted_text' => $highlightedText,
                'generated_at' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            Log::error('AI Explanation Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate explanation. Please try again.',
            ], 500);
        }
    }

    /**
     * Generate AI explanation using Google Gemini API
     *
     * @param string $text
     * @param string $context
     * @return string
     */
    private function generateExplanation($text, $context)
    {
        // Get Gemini API key from environment
        $apiKey = env('GEMINI_API_KEY');

        // If no API key, use fallback explanations
        if (empty($apiKey)) {
            return $this->getFallbackExplanation($text, $context);
        }

        try {
            // Build the prompt for Gemini
            $prompt = $this->buildPrompt($text, $context);

            // Call Google Gemini API (using gemini-2.0-flash - the latest stable model)
            $response = Http::timeout(30)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}",
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 500,
                    ]
                ]
            );

            if ($response->successful()) {
                $data = $response->json();

                // Extract the generated text from Gemini response
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    return $data['candidates'][0]['content']['parts'][0]['text'];
                }
            }

            // If API call fails, use fallback
            Log::warning('Gemini API call failed, using fallback');
            return $this->getFallbackExplanation($text, $context);

        } catch (\Exception $e) {
            Log::error('Gemini API Error: ' . $e->getMessage());
            return $this->getFallbackExplanation($text, $context);
        }
    }

    /**
     * Build a clear prompt for Gemini AI
     *
     * @param string $text
     * @param string $context
     * @return string
     */
    private function buildPrompt($text, $context)
    {
        $prompt = "You are a helpful educational assistant. A student has highlighted the following text and needs a clear explanation:\n\n";
        $prompt .= "**Highlighted Text:** \"{$text}\"\n\n";

        if (!empty($context)) {
            $prompt .= "**Context:** \"...{$context}...\"\n\n";
        }

        $prompt .= "Please provide:\n";
        $prompt .= "1. A clear and concise definition\n";
        $prompt .= "2. Key points or characteristics (use bullet points with **bold** headers)\n";
        $prompt .= "3. Simple examples if applicable\n";
        $prompt .= "4. Keep it educational and easy to understand\n\n";
        $prompt .= "Format your response using Markdown (bold, lists, etc.) for better readability.\n";
        $prompt .= "Keep the explanation under 300 words.";

        return $prompt;
    }

    /**
     * Fallback explanations when API is not available
     *
     * @param string $text
     * @param string $context
     * @return string
     */
    private function getFallbackExplanation($text, $context)
    {
        $explanations = [
            'photosynthesis' => "**Photosynthesis** is the biological process by which plants, algae, and some bacteria convert light energy (usually from the sun) into chemical energy stored in glucose. This process occurs primarily in the chloroplasts of plant cells.\n\n**Key Points:**\n- **Chemical Formula:** 6CO₂ + 6H₂O + light energy → C₆H₁₂O₆ + 6O₂\n- **Location:** Chloroplasts (containing chlorophyll)\n- **Importance:** Produces oxygen and organic compounds essential for life on Earth\n- **Two Stages:** Light-dependent reactions and light-independent reactions (Calvin cycle)",

            'algorithm' => "**Algorithm** is a step-by-step procedure or set of rules designed to solve a specific problem or perform a computation. Algorithms are fundamental to computer science and programming.\n\n**Key Characteristics:**\n- **Input:** Takes zero or more inputs\n- **Output:** Produces one or more outputs\n- **Definiteness:** Each step is clearly defined\n- **Finiteness:** Must terminate after a finite number of steps\n- **Effectiveness:** Steps are basic enough to be executed\n\n**Examples:** Sorting algorithms, search algorithms, encryption algorithms",

            'default' => "**{TEXT}** - Let me explain this term:\n\nThis concept refers to a specific idea or principle that may have different meanings depending on the context. Here's a general explanation:\n\n**Definition:** {TEXT} is a term commonly used in academic and professional contexts.\n\n**Key Points:**\n- Understanding this concept is important for comprehending the broader topic\n- It often relates to fundamental principles in its field\n- Practical applications can be found in various real-world scenarios\n\n**Context Matters:** The exact meaning may vary based on the subject area (science, mathematics, literature, etc.).\n\n_Note: Connect to Google Gemini API for real-time AI explanations. Add your GEMINI_API_KEY to .env file._",
        ];

        // Check for exact matches first
        $textLower = strtolower(trim($text));
        foreach ($explanations as $keyword => $explanation) {
            if (strpos($textLower, $keyword) !== false) {
                return $explanation;
            }
        }

        // Generate contextual explanation
        $defaultExplanation = str_replace('{TEXT}', ucfirst($text), $explanations['default']);

        // If context is provided, add it to the explanation
        if (!empty($context)) {
            $defaultExplanation .= "\n\n**Surrounding Context:**\n_\"..." . substr($context, 0, 200) . "...\"_\n\nBased on this context, the term appears in a specific domain. Consider the subject matter when interpreting its meaning.";
        }

        return $defaultExplanation;
    }

    /**
     * Get history of AI explanations for the current user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function history()
    {
        // Future: Store and retrieve explanation history from database
        // For now, return empty array
        return response()->json([
            'success' => true,
            'history' => [],
        ]);
    }
}
