# 📚 API Documentation - Chatbot Module

## Base URL
```
http://localhost:3000/api
```

## Authentication
All endpoints require authentication. Include the user token in the request header:
```
Authorization: Bearer {token}
```

---

## Endpoints

### 1. Send Chat Message

**POST** `/chat/message`

Send a message to the chatbot and get an AI-generated response.

**Request Body:**
```json
{
  "user_id": 123,
  "document_id": 456,
  "message": "What is photosynthesis?",
  "role": "user",
  "context": {
    "previous_messages": [],
    "document_content": "..."
  }
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 789,
    "user_id": 123,
    "document_id": 456,
    "role": "assistant",
    "message": "Photosynthesis is the process by which plants...",
    "created_at": "2025-11-18T12:00:00Z",
    "updated_at": "2025-11-18T12:00:00Z"
  },
  "metadata": {
    "response_time": 1234,
    "tokens_used": 150
  }
}
```

---

### 2. Get Chat History

**GET** `/chat/history/:document_id`

Retrieve all chat messages for a specific document.

**URL Parameters:**
- `document_id` (required) - The document ID

**Query Parameters:**
- `limit` (optional) - Number of messages to return (default: 50)
- `offset` (optional) - Pagination offset (default: 0)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 123,
      "document_id": 456,
      "role": "user",
      "message": "Hello",
      "created_at": "2025-11-18T11:00:00Z"
    },
    {
      "id": 2,
      "role": "assistant",
      "message": "Hi! How can I help you?",
      "created_at": "2025-11-18T11:00:05Z"
    }
  ],
  "pagination": {
    "total": 100,
    "limit": 50,
    "offset": 0,
    "has_more": true
  }
}
```

---

### 3. Delete Chat History

**DELETE** `/chat/history/:document_id`

Clear all chat messages for a specific document.

**URL Parameters:**
- `document_id` (required) - The document ID

**Response:**
```json
{
  "success": true,
  "message": "Chat history deleted successfully",
  "deleted_count": 25
}
```

---

### 4. Get User Conversations

**GET** `/chat/conversations`

Get all conversations for the authenticated user.

**Query Parameters:**
- `limit` (optional) - Number of conversations to return (default: 20)
- `offset` (optional) - Pagination offset (default: 0)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "document_id": 456,
      "document_title": "Biology Chapter 1",
      "last_message": "Thanks for the help!",
      "last_message_at": "2025-11-18T12:00:00Z",
      "message_count": 15,
      "unread_count": 2
    }
  ]
}
```

---

### 5. Search Chat Messages

**GET** `/chat/search`

Search through chat history.

**Query Parameters:**
- `q` (required) - Search query
- `document_id` (optional) - Filter by document
- `limit` (optional) - Results limit (default: 20)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "message": "Photosynthesis is important because...",
      "document_id": 456,
      "created_at": "2025-11-18T10:00:00Z",
      "highlight": "...important because..."
    }
  ],
  "total_results": 5
}
```

---

### 6. Rate Response

**POST** `/chat/:message_id/rate`

Rate a chatbot response.

**URL Parameters:**
- `message_id` (required) - The message ID

**Request Body:**
```json
{
  "rating": 5,
  "feedback": "Very helpful response!"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Thank you for your feedback!"
}
```

---

## WebSocket Events

Connect to WebSocket for real-time updates:
```javascript
const socket = io('http://localhost:3000');
```

### Client Events (Emit)

#### `send_message`
```javascript
socket.emit('send_message', {
  user_id: 123,
  document_id: 456,
  message: 'Hello'
});
```

#### `typing_start`
```javascript
socket.emit('typing_start', {
  user_id: 123,
  document_id: 456
});
```

#### `typing_stop`
```javascript
socket.emit('typing_stop', {
  user_id: 123,
  document_id: 456
});
```

### Server Events (Listen)

#### `new_message`
```javascript
socket.on('new_message', (data) => {
  console.log('New message:', data);
});
```

#### `user_typing`
```javascript
socket.on('user_typing', (data) => {
  console.log('User is typing...');
});
```

#### `error`
```javascript
socket.on('error', (error) => {
  console.error('Error:', error);
});
```

---

## Error Responses

### 400 Bad Request
```json
{
  "success": false,
  "error": {
    "code": "INVALID_INPUT",
    "message": "Message cannot be empty",
    "details": {}
  }
}
```

### 401 Unauthorized
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Authentication required"
  }
}
```

### 404 Not Found
```json
{
  "success": false,
  "error": {
    "code": "NOT_FOUND",
    "message": "Chat message not found"
  }
}
```

### 500 Internal Server Error
```json
{
  "success": false,
  "error": {
    "code": "INTERNAL_ERROR",
    "message": "An unexpected error occurred"
  }
}
```

---

## Rate Limiting

- **Rate Limit**: 100 requests per minute per user
- **Burst Limit**: 20 requests per second

**Headers:**
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1700308800
```

---

## Examples

### cURL Example
```bash
curl -X POST http://localhost:3000/api/chat/message \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your_token_here" \
  -d '{
    "user_id": 123,
    "document_id": 456,
    "message": "Explain quantum physics",
    "role": "user"
  }'
```

### JavaScript Example
```javascript
const response = await fetch('http://localhost:3000/api/chat/message', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer your_token_here'
  },
  body: JSON.stringify({
    user_id: 123,
    document_id: 456,
    message: 'Explain quantum physics',
    role: 'user'
  })
});

const data = await response.json();
console.log(data);
```

### Python Example
```python
import requests

url = 'http://localhost:3000/api/chat/message'
headers = {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer your_token_here'
}
data = {
    'user_id': 123,
    'document_id': 456,
    'message': 'Explain quantum physics',
    'role': 'user'
}

response = requests.post(url, json=data, headers=headers)
print(response.json())
```

---

## Support

For API support, contact: api-support@ketupatslabs.com
