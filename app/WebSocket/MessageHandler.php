<?php

namespace App\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class MessageHandler implements MessageComponentInterface {
    protected $clients;
    protected $users; // Map user_id to connections
    protected $conversations; // Map conversation_id to user_ids
    
    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->users = [];
        $this->conversations = [];
    }
    
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }
    
    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        if (!$data || !isset($data['type'])) {
            return;
        }
        
        switch ($data['type']) {
            case 'auth':
                $this->handleAuth($from, $data);
                break;
            case 'join_conversation':
                $this->handleJoinConversation($from, $data);
                break;
            case 'leave_conversation':
                $this->handleLeaveConversation($from, $data);
                break;
            case 'typing':
                $this->handleTyping($from, $data);
                break;
            case 'stop_typing':
                $this->handleStopTyping($from, $data);
                break;
            case 'new_message':
                $this->handleNewMessage($from, $data);
                break;
            case 'online_status':
                $this->handleOnlineStatus($from, $data);
                break;
        }
    }
    
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        
        // Remove user from users map
        foreach ($this->users as $userId => $connections) {
            $this->users[$userId] = array_filter($connections, function($c) use ($conn) {
                return $c !== $conn;
            });
            if (empty($this->users[$userId])) {
                unset($this->users[$userId]);
            }
        }
        
        // Remove from conversations
        foreach ($this->conversations as $convId => $userIds) {
            foreach ($userIds as $userId => $connections) {
                $this->conversations[$convId][$userId] = array_filter($connections, function($c) use ($conn) {
                    return $c !== $conn;
                });
                if (empty($this->conversations[$convId][$userId])) {
                    unset($this->conversations[$convId][$userId]);
                }
            }
        }
        
        echo "Connection {$conn->resourceId} has disconnected\n";
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
    
    private function handleAuth(ConnectionInterface $conn, $data) {
        $userId = $data['user_id'] ?? null;
        
        if (!$userId) {
            $conn->send(json_encode([
                'type' => 'error',
                'message' => 'Invalid user_id'
            ]));
            return;
        }
        
        // Store user connection
        if (!isset($this->users[$userId])) {
            $this->users[$userId] = [];
        }
        $this->users[$userId][] = $conn;
        $conn->userId = $userId;
        
        $conn->send(json_encode([
            'type' => 'auth_success',
            'user_id' => $userId
        ]));
        
        echo "User {$userId} authenticated (Connection {$conn->resourceId})\n";
    }
    
    private function handleJoinConversation(ConnectionInterface $conn, $data) {
        $conversationId = $data['conversation_id'] ?? null;
        $userId = $conn->userId ?? null;
        
        if (!$conversationId || !$userId) {
            return;
        }
        
        if (!isset($this->conversations[$conversationId])) {
            $this->conversations[$conversationId] = [];
        }
        
        if (!isset($this->conversations[$conversationId][$userId])) {
            $this->conversations[$conversationId][$userId] = [];
        }
        
        $this->conversations[$conversationId][$userId][] = $conn;
        
        echo "User {$userId} joined conversation {$conversationId}\n";
    }
    
    private function handleLeaveConversation(ConnectionInterface $conn, $data) {
        $conversationId = $data['conversation_id'] ?? null;
        $userId = $conn->userId ?? null;
        
        if (!$conversationId || !$userId) {
            return;
        }
        
        if (isset($this->conversations[$conversationId][$userId])) {
            $this->conversations[$conversationId][$userId] = array_filter(
                $this->conversations[$conversationId][$userId],
                function($c) use ($conn) {
                    return $c !== $conn;
                }
            );
            
            if (empty($this->conversations[$conversationId][$userId])) {
                unset($this->conversations[$conversationId][$userId]);
            }
        }
    }
    
    private function handleTyping(ConnectionInterface $conn, $data) {
        $conversationId = $data['conversation_id'] ?? null;
        $userId = $conn->userId ?? null;
        
        if (!$conversationId || !$userId) {
            return;
        }
        
        // Broadcast typing indicator to other participants
        $this->broadcastToConversation($conversationId, $userId, [
            'type' => 'user_typing',
            'conversation_id' => $conversationId,
            'user_id' => $userId
        ]);
    }
    
    private function handleStopTyping(ConnectionInterface $conn, $data) {
        $conversationId = $data['conversation_id'] ?? null;
        $userId = $conn->userId ?? null;
        
        if (!$conversationId || !$userId) {
            return;
        }
        
        // Broadcast stop typing to other participants
        $this->broadcastToConversation($conversationId, $userId, [
            'type' => 'user_stopped_typing',
            'conversation_id' => $conversationId,
            'user_id' => $userId
        ]);
    }
    
    private function handleNewMessage(ConnectionInterface $conn, $data) {
        $conversationId = $data['conversation_id'] ?? null;
        $userId = $conn->userId ?? null;
        
        if (!$conversationId || !$userId) {
            return;
        }
        
        // Broadcast new message to all participants except sender
        $this->broadcastToConversation($conversationId, $userId, [
            'type' => 'new_message',
            'conversation_id' => $conversationId,
            'message' => $data['message'] ?? null
        ]);
    }
    
    private function handleOnlineStatus(ConnectionInterface $conn, $data) {
        $userId = $conn->userId ?? null;
        $isOnline = $data['is_online'] ?? true;
        
        if (!$userId) {
            return;
        }
        
        // Broadcast online status to all conversations this user is in
        foreach ($this->conversations as $convId => $userIds) {
            if (isset($userIds[$userId])) {
                $this->broadcastToConversation($convId, $userId, [
                    'type' => 'online_status_update',
                    'user_id' => $userId,
                    'is_online' => $isOnline
                ]);
            }
        }
    }
    
    private function broadcastToConversation($conversationId, $excludeUserId, $message) {
        if (!isset($this->conversations[$conversationId])) {
            return;
        }
        
        $messageJson = json_encode($message);
        
        foreach ($this->conversations[$conversationId] as $userId => $connections) {
            if ($userId == $excludeUserId) {
                continue; // Don't send to sender
            }
            
            foreach ($connections as $conn) {
                if ($conn->isConnected()) {
                    $conn->send($messageJson);
                }
            }
        }
    }
}

