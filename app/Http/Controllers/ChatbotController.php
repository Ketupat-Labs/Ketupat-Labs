<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
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

            // Generate response using OpenAI API
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

            return response()->json([
                'status' => 500,
                'message' => 'Ralat memproses permintaan anda. Sila cuba lagi.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Generate a response from the chatbot using OpenAI API
     * 
     * @param string $prompt
     * @param string|null $context
     * @return string
     */
    private function generateResponse(string $prompt, ?string $context = null): string
    {
        $apiKey = env('OPENAI_API_KEY');
        
        // Fallback to placeholder if API key is not configured
        if (!$apiKey) {
            Log::warning('OpenAI API key not configured, using fallback response');
            return $this->getFallbackResponse($prompt, $context);
        }
        
        try {
            // Build the system message in Bahasa Melayu
            $systemMessage = "Anda adalah Ketupat, pembantu AI yang membantu untuk platform pendidikan. " .
                            "Anda membantu pelajar memahami kandungan, menjawab soalan, dan memberikan penjelasan. " .
                            "Sila bersikap ringkas, mesra, dan mendidik dalam respons anda. " .
                            "PENTING: Sentiasa jawab dalam Bahasa Melayu sahaja.";
            
            // Build messages array
            $messages = [
                [
                    'role' => 'system',
                    'content' => $systemMessage
                ]
            ];
            
            // Add context if provided
            if ($context) {
                $messages[] = [
                    'role' => 'user',
                    'content' => "Konteks dari teks yang dipilih: " . $context . "\n\nSoalan pengguna: " . $prompt
                ];
            } else {
                $messages[] = [
                    'role' => 'user',
                    'content' => $prompt
                ];
            }
            
            // Make request to OpenAI API using cURL (native PHP)
            $ch = curl_init('https://api.openai.com/v1/chat/completions');
            
            $postData = json_encode([
                'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 1000,
            ]);
            
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json',
                ],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                Log::error('OpenAI API cURL error: ' . $curlError);
                return "Saya menghadapi masalah menyambung ke perkhidmatan AI. Sila semak sambungan internet anda dan cuba lagi.";
            }
            
            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);
                
                if (isset($data['choices'][0]['message']['content'])) {
                    return trim($data['choices'][0]['message']['content']);
                } else {
                    Log::error('Unexpected OpenAI API response structure', ['response' => $data]);
                    return $this->getFallbackResponse($prompt, $context);
                }
            } else {
                $errorData = json_decode($response, true);
                
                Log::error('OpenAI API request failed', [
                    'status' => $httpCode,
                    'error' => $errorData ?? $response
                ]);
                
                // Return user-friendly error message
                if ($httpCode === 401) {
                    return "Saya menghadapi masalah pengesahan dengan perkhidmatan AI. Sila semak konfigurasi API.";
                } elseif ($httpCode === 429) {
                    return "Saya menerima terlalu banyak permintaan sekarang. Sila cuba sebentar lagi.";
                } elseif ($httpCode === 500) {
                    return "Perkhidmatan AI mengalami masalah. Sila cuba lagi kemudian.";
                } else {
                    return $this->getFallbackResponse($prompt, $context);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('OpenAI API error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return $this->getFallbackResponse($prompt, $context);
        }
    }
    
    /**
     * Get a fallback response when OpenAI API is unavailable
     * 
     * @param string $prompt
     * @param string|null $context
     * @return string
     */
    private function getFallbackResponse(string $prompt, ?string $context = null): string
    {
        // Check if the message contains common greetings
        $lowerPrompt = strtolower($prompt);
        
        if (strpos($lowerPrompt, 'hello') !== false || 
            strpos($lowerPrompt, 'hi') !== false || 
            strpos($lowerPrompt, 'hey') !== false ||
            strpos($lowerPrompt, 'hai') !== false ||
            strpos($lowerPrompt, 'helo') !== false) {
            return "Hai! Saya Ketupat, pembantu AI anda. Bagaimana saya boleh membantu anda hari ini?";
        }
        
        if (strpos($lowerPrompt, 'help') !== false || 
            strpos($lowerPrompt, 'tolong') !== false ||
            strpos($lowerPrompt, 'bantu') !== false) {
            return "Saya di sini untuk membantu! Anda boleh bertanya soalan tentang kandungan, mendapat penjelasan, atau meminta bantuan dengan pembelajaran anda. Apa yang anda ingin tahu?";
        }
        
        if (strpos($lowerPrompt, 'thank') !== false || 
            strpos($lowerPrompt, 'terima kasih') !== false) {
            return "Sama-sama! Adakah ada apa-apa lagi yang boleh saya bantu?";
        }
        
        // If context is provided, acknowledge it
        if ($context) {
            return "Berdasarkan teks yang anda pilih: \"" . substr($context, 0, 100) . "...\", " . 
                   "Saya faham anda bertanya tentang ini. " .
                   "Walau bagaimanapun, saya tidak dapat memberikan respons AI terperinci buat masa ini. " .
                   "Sila pastikan kunci API OpenAI dikonfigurasikan dengan betul dalam fail .env.";
        }
        
        // Default response
        return "Saya faham soalan anda. Walau bagaimanapun, saya tidak dapat memberikan respons AI terperinci buat masa ini. " .
               "Sila pastikan kunci API OpenAI dikonfigurasikan dengan betul. " .
               "Buat masa ini, saya boleh membantu anda dengan soalan umum. Apa yang anda ingin tahu?";
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
