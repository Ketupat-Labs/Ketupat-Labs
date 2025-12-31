<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    /**
     * Cached result of Gemini ListModels (per request lifecycle).
     */
    private ?array $geminiModelsCache = null;
    /**
     * Handle chatbot chat requests
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function chat(Request $request)
    {
        try {
            // Get current user
            $user = $this->getCurrentUser();
            if (!$user) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Unauthorized',
                ], 401);
            }

            // Check if chatbot is enabled for this user
            if (isset($user->chatbot_enabled) && !$user->chatbot_enabled) {
                return response()->json([
                    'status' => 403,
                    'message' => 'Chatbot is disabled in your settings',
                ], 403);
            }

            $request->validate([
                'message' => 'required|string|max:2000',
                'context' => 'nullable|string|max:5000', // Optional context from highlighted text
            ]);

            $userMessage = $request->input('message');
            $context = $request->input('context'); // Highlighted text context

            // Build the prompt with context if provided
            $prompt = $userMessage;
            if ($context) {
                $prompt = "Context: " . $context . "\n\nQuestion: " . $userMessage;
            }

            // Generate response using Gemini API
            $reply = $this->generateResponse($prompt, $context);

            return response()->json([
                'status' => 200,
                'message' => 'Success',
                'data' => [
                    'reply' => $reply,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Chatbot error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            // Client-visible error (no silent fallbacks).
            $status = 400;
            $msg = $e->getMessage();

            return response()->json([
                'status' => $status,
                'message' => $msg ?: 'Ralat memproses permintaan anda. Sila cuba lagi.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], $status);
        }
    }

    /**
    * Generate a response from the chatbot using Gemini API
     *
     * @param string $prompt
     * @param string|null $context
     * @return string
     */
    private function generateResponse(string $prompt, ?string $context = null): string
    {
        $openaiKey = env('OPENAI_API_KEY');
        $geminiKey = env('GEMINI_API_KEY');

        // Prefer OpenAI official API
        if ($openaiKey && strlen($openaiKey) > 20) {
            $systemMessage = "Anda adalah Ketupat, pembantu AI untuk platform pendidikan. " .
                "Tugas anda ialah menerangkan konsep dengan jelas, ringkas, dan mesra. " .
                "PENTING: Sentiasa jawab dalam Bahasa Melayu sahaja.";

            $userText = $context
                ? ("Konteks dari teks yang dipilih:\n" . $context . "\n\nSoalan pengguna:\n" . $prompt)
                : $prompt;

            $reply = $this->callOpenAIChat($systemMessage, $userText, 900);
            if (trim($reply) === '') {
                throw new \Exception('AI tidak dapat menghasilkan jawapan. Sila cuba lagi.');
            }
            return trim($reply);
        }

        // Optional fallback to Gemini (if configured)
        if ($geminiKey && strlen($geminiKey) > 20) {
            try {
                $systemMessage = "Anda adalah Ketupat, pembantu AI untuk platform pendidikan. " .
                    "Tugas anda ialah menerangkan konsep dengan jelas, ringkas, dan mesra. " .
                    "PENTING: Sentiasa jawab dalam Bahasa Melayu sahaja.";

                $userText = $context
                    ? ("Konteks dari teks yang dipilih:\n" . $context . "\n\nSoalan pengguna:\n" . $prompt)
                    : $prompt;

                $finalPrompt = $systemMessage . "\n\n" . $userText;
                $model = env('GEMINI_MODEL', 'gemini-1.5-flash-latest');
                $response = $this->callGeminiGenerateContent($geminiKey, $model, $finalPrompt, 1000);
                if ($response === null) {
                    $fallbackModel = $this->pickSupportedGeminiModel($geminiKey);
                    if (!$fallbackModel) {
                        throw new \Exception('Model Gemini tidak tersedia untuk projek ini. Sila semak akses Gemini dan tetapan model.');
                    }
                    Log::warning('Chatbot Gemini model not available, falling back to: ' . $fallbackModel);
                    $response = $this->callGeminiGenerateContent($geminiKey, $fallbackModel, $finalPrompt, 1000);
                }
                if ($response === null || trim($response) === '') {
                    throw new \Exception('AI tidak dapat menghasilkan jawapan. Sila cuba lagi.');
                }
                return trim($response);
            } catch (\Exception $e) {
                Log::error('Gemini fallback error (chatbot): ' . $e->getMessage(), ['exception' => $e]);
                throw $e;
            }
        }

        // No demo / fake replies
        throw new \Exception('AI tidak dikonfigurasikan. Sila tambah OPENAI_API_KEY dalam fail .env.');
    }

    /**
     * OpenAI official API (chat completions) call.
     */
    private function callOpenAIChat(string $systemMessage, string $userMessage, int $maxTokens = 800): string
    {
        $apiKey = env('OPENAI_API_KEY');
        if (!$apiKey || strlen($apiKey) < 20) {
            throw new \Exception('OpenAI API key not configured. Please add OPENAI_API_KEY to your .env file.');
        }

        $model = env('OPENAI_MODEL', 'gpt-4o-mini');

        $payload = json_encode([
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemMessage],
                ['role' => 'user', 'content' => $userMessage],
            ],
            'temperature' => 0.6,
            'max_tokens' => $maxTokens,
        ]);

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
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
            throw new \Exception('Ralat sambungan: ' . $curlError);
        }

        $data = json_decode((string)$response, true);
        if ($httpCode !== 200) {
            $msg = $data['error']['message'] ?? 'Unknown error';
            if ($httpCode === 401) {
                throw new \Exception('Kunci OpenAI tidak sah (401). Sila semak OPENAI_API_KEY dalam .env.');
            }
            if ($httpCode === 429) {
                throw new \Exception('Terlalu banyak permintaan (429). Sila cuba sebentar lagi.');
            }
            throw new \Exception('OpenAI API error: ' . $msg);
        }

        $text = $data['choices'][0]['message']['content'] ?? '';
        return trim((string)$text);
    }

    /**
     * Returns text on success, null on "model not found".
     */
    private function callGeminiGenerateContent(string $apiKey, string $model, string $prompt, int $maxTokens): ?string
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $apiKey;

        $postData = json_encode([
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.6,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => $maxTokens,
            ]
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
            throw new \Exception('Ralat sambungan: ' . $curlError);
        }

        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? 'Unknown error';

            // Model not found: let caller try auto-selection
            if ($httpCode === 404 && (stripos($errorMessage, 'not found') !== false || stripos($errorMessage, 'models/') !== false)) {
                Log::warning('Gemini model not found for chatbot: ' . $model . ' - ' . $errorMessage);
                return null;
            }

            if ($httpCode === 401) {
                throw new \Exception('Kunci Gemini tidak sah (401). Sila semak GEMINI_API_KEY dalam .env.');
            }
            if ($httpCode === 429) {
                throw new \Exception('Terlalu banyak permintaan (429). Sila cuba sebentar lagi.');
            }

            throw new \Exception('Gemini API error: ' . $errorMessage);
        }

        $data = json_decode($response, true);
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!$text) {
            throw new \Exception('Struktur respons Gemini tidak dijangka.');
        }

        return trim((string)$text);
    }

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
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new \Exception('Ralat sambungan (ListModels): ' . $curlError);
        }

        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? 'Unknown error';
            throw new \Exception('Gagal menyenaraikan model Gemini: ' . $errorMessage);
        }

        $data = json_decode($response, true);
        $models = $data['models'] ?? [];
        $this->geminiModelsCache = is_array($models) ? $models : [];
        return $this->geminiModelsCache;
    }

    private function pickSupportedGeminiModel(string $apiKey): ?string
    {
        $models = $this->listGeminiModels($apiKey);
        if (!$models) {
            return null;
        }

        $candidates = [];
        foreach ($models as $m) {
            $name = $m['name'] ?? '';
            $supported = $m['supportedGenerationMethods'] ?? [];
            if (!$name || !is_array($supported)) {
                continue;
            }
            if (!in_array('generateContent', $supported, true)) {
                continue;
            }
            // API returns like "models/gemini-2.5-flash". We need "gemini-2.5-flash".
            $short = str_starts_with($name, 'models/') ? substr($name, 7) : $name;
            $candidates[] = $short;
        }

        if (!$candidates) {
            return null;
        }

        // Prefer flash models
        foreach ($candidates as $c) {
            if (stripos($c, 'gemini-1.5-flash') !== false) return $c;
        }
        foreach ($candidates as $c) {
            if (stripos($c, 'flash') !== false) return $c;
        }

        return $candidates[0];
    }

    /**
     * Get current user from session
     */
    protected function getCurrentUser()
    {
        $user = null;
        if (session('user_id')) {
            $user = \App\Models\User::find(session('user_id'));
        }

        if (!$user) {
            $user = \Illuminate\Support\Facades\Auth::user();
        }

        return $user;
    }
}
