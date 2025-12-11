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
                'message' => 'Error processing your request. Please try again.',
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
            // Build the system message
            $systemMessage = "You are Ketupat, a helpful AI assistant for an educational platform. " .
                            "You help students understand content, answer questions, and provide explanations. " .
                            "Be concise, friendly, and educational in your responses.";
            
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
                    'content' => "Context from highlighted text: " . $context . "\n\nUser question: " . $prompt
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
                return "I'm having trouble connecting to the AI service. Please check your internet connection and try again.";
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
                    return "I'm having trouble authenticating with the AI service. Please check the API configuration.";
                } elseif ($httpCode === 429) {
                    return "I'm receiving too many requests right now. Please try again in a moment.";
                } elseif ($httpCode === 500) {
                    return "The AI service is experiencing issues. Please try again later.";
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
            strpos($lowerPrompt, 'hey') !== false) {
            return "Hello! I'm Ketupat, your AI assistant. How can I help you today?";
        }
        
        if (strpos($lowerPrompt, 'help') !== false) {
            return "I'm here to help! You can ask me questions about the content, get explanations, or request assistance with your studies. What would you like to know?";
        }
        
        if (strpos($lowerPrompt, 'thank') !== false) {
            return "You're welcome! Is there anything else I can help you with?";
        }
        
        // If context is provided, acknowledge it
        if ($context) {
            return "Based on the text you highlighted: \"" . substr($context, 0, 100) . "...\", " . 
                   "I understand you're asking about this. " .
                   "However, I'm currently unable to provide detailed AI-powered responses. " .
                   "Please ensure the OpenAI API key is properly configured in the .env file.";
        }
        
        // Default response
        return "I understand your question. However, I'm currently unable to provide detailed AI-powered responses. " .
               "Please ensure the OpenAI API key is properly configured. " .
               "For now, I can help you with general questions. What would you like to know?";
    }
}
