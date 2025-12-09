<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Chat;
use App\Models\User;

class ChatbotController extends Controller
{
    /**
     * Send a message to Ketupats Chatbot and get AI response
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'conversation_id' => 'nullable|string'
        ]);

        $user = auth()->user();
        $userMessage = $request->input('message');
        $conversationId = $request->input('conversation_id', 'general_' . $user->id);

        try {
            // Store user message
            $userChat = Chat::create([
                'document_id' => null, // General chatbot, not document-specific
                'role' => 'user',
                'message' => $userMessage,
            ]);

            // Get conversation history for context
            $conversationHistory = $this->getConversationHistory($conversationId);

            // Generate AI response using Gemini
            $aiResponse = $this->generateAIResponse($userMessage, $conversationHistory, $user);

            // Store AI response
            $assistantChat = Chat::create([
                'document_id' => null,
                'role' => 'assistant',
                'message' => $aiResponse,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'user_message' => [
                        'id' => $userChat->id,
                        'message' => $userMessage,
                        'role' => 'user',
                        'timestamp' => $userChat->created_at->toISOString(),
                    ],
                    'assistant_message' => [
                        'id' => $assistantChat->id,
                        'message' => $aiResponse,
                        'role' => 'assistant',
                        'timestamp' => $assistantChat->created_at->toISOString(),
                    ],
                    'conversation_id' => $conversationId
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Chatbot Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Sorry, I encountered an error. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get conversation history
     */
    private function getConversationHistory($conversationId, $limit = 10)
    {
        // Get recent chat messages for context
        $recentChats = Chat::whereNull('document_id')
            ->orderBy('created_at', 'desc')
            ->limit($limit * 2) // Get both user and assistant messages
            ->get()
            ->reverse()
            ->map(function ($chat) {
                return [
                    'role' => $chat->role,
                    'message' => $chat->message
                ];
            });

        return $recentChats;
    }

    /**
     * Generate AI response using Google Gemini API
     */
    private function generateAIResponse($userMessage, $conversationHistory, $user)
    {
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            throw new \Exception('Gemini API key not configured');
        }

        // Build context-aware prompt
        $systemPrompt = $this->buildSystemPrompt($user);

        // Build conversation context
        $conversationContext = $this->buildConversationContext($conversationHistory);

        // Combine prompts
        $fullPrompt = $systemPrompt . "\n\n" . $conversationContext . "\n\nUser: " . $userMessage . "\n\nAssistant:";

        // Primary model (fast & cost‑efficient): gemini-flash-latest
        $modelEndpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key={$apiKey}";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $fullPrompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 1024,
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ]
            ]
        ];

        $response = Http::withHeaders(['Content-Type' => 'application/json'])->post($modelEndpoint, $payload);

        // Fallback to gemini-pro-latest if flash-latest returns 404 (model rename or temporary unavailability)
        if ($response->status() === 404) {
            $fallbackEndpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro-latest:generateContent?key={$apiKey}";
            Log::warning('Flash model 404, retrying with pro-latest');
            $response = Http::withHeaders(['Content-Type' => 'application/json'])->post($fallbackEndpoint, $payload);
        }

        if (!$response->successful()) {
            // Capture structured error if present
            $body = $response->body();
            Log::error('Gemini API raw error response: ' . $body);
            throw new \Exception('Gemini API request failed: ' . $body);
        }

        $responseData = $response->json();

        if (!isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
            throw new \Exception('Invalid response from Gemini API');
        }

        return trim($responseData['candidates'][0]['content']['parts'][0]['text']);
    }

    /**
     * Build system prompt for the chatbot
     */
    private function buildSystemPrompt($user)
    {
        $userName = $user->name;
        $userRole = $user->role ?? 'student';

        return "You are Ketupats Assistant, a specialized AI assistant for Computer Science education at Ketupats Labs.

IMPORTANT SCOPE RESTRICTION:
- You ONLY answer questions related to Computer Science topics including:
  * Programming (Python, Java, C++, JavaScript, etc.)
  * Data Structures and Algorithms
  * Software Engineering
  * Web Development
  * Database Systems
  * Computer Networks
  * Operating Systems
  * Artificial Intelligence and Machine Learning
  * Cybersecurity
  * Computer Architecture
  * Theory of Computation
  * Any other Computer Science related topics

- If a user asks about topics OUTSIDE Computer Science (e.g., biology, physics, history, cooking, sports, personal advice, etc.), respond EXACTLY with:
  \"I'm sorry, but I can only assist with questions related to Computer Science. Please ask me about programming, algorithms, software development, or other Computer Science topics. How can I help you with Computer Science today?\"

Your role:
- Help users learn Computer Science concepts
- Explain programming concepts clearly
- Assist with coding problems and algorithms
- Provide clear, concise, and educational answers
- Be friendly and encouraging
- Use examples when explaining CS concepts

Current user: {$userName} (Role: {$userRole})

Guidelines:
- Keep responses focused on Computer Science
- Use simple language to explain complex CS concepts
- Provide code examples when helpful
- Be encouraging and positive about learning CS
- If unsure about a CS topic, admit it honestly";
    }

    /**
     * Build conversation context from history
     */
    private function buildConversationContext($conversationHistory)
    {
        if ($conversationHistory->isEmpty()) {
            return "This is the start of a new conversation.";
        }

        $context = "Previous conversation:\n";

        foreach ($conversationHistory->take(5) as $chat) { // Last 5 messages for context
            $role = $chat['role'] === 'user' ? 'User' : 'Assistant';
            $context .= "{$role}: {$chat['message']}\n";
        }

        return $context;
    }

    /**
     * Get chat history
     */
    public function getHistory(Request $request)
    {
        $limit = $request->input('limit', 50);

        $history = Chat::whereNull('document_id')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->map(function ($chat) {
                return [
                    'id' => $chat->id,
                    'role' => $chat->role,
                    'message' => $chat->message,
                    'timestamp' => $chat->created_at->toISOString(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    /**
     * Clear chat history
     */
    public function clearHistory()
    {
        $deleted = Chat::whereNull('document_id')->delete();

        return response()->json([
            'success' => true,
            'message' => 'Chat history cleared',
            'deleted_count' => $deleted
        ]);
    }

    /**
     * Get quick suggestions
     */
    public function getSuggestions()
    {
        $suggestions = [
            "What is an algorithm?",
            "Explain object-oriented programming",
            "What are data structures?",
            "How does recursion work?",
            "Explain the difference between SQL and NoSQL",
            "What is machine learning?",
        ];

        return response()->json([
            'success' => true,
            'data' => $suggestions
        ]);
    }
}
