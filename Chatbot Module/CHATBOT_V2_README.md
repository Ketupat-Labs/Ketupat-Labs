# Ketupats Chatbot Module - Updated Version

## 🎉 Latest Features (December 2025)

### ✨ What's New
- **Computer Science Focused**: Chatbot now only answers CS-related questions
- **Conversation History**: View up to 100 previous messages
- **Refresh Button**: Reload conversation history anytime
- **Updated Gemini API**: Using latest `gemini-flash-latest` model
- **Improved Error Handling**: Better error messages and fallback to `gemini-pro-latest`
- **Enhanced UI**: History indicator, message count, and improved animations

---

## 📁 Updated Files

### Backend
- **`backend/Controllers/ChatbotController.php`** *(Updated)*
  - Computer Science scope restriction in system prompt
  - Updated Gemini API endpoints (gemini-flash-latest)
  - Fallback to gemini-pro-latest on 404
  - Enhanced error logging
  - New CS-focused suggestions

### Frontend
- **`frontend/components/KetupatsChatbot.jsx`** *(Updated)*
  - Loads history on every open (not just first time)
  - Refresh button in header
  - Conversation history indicator
  - Debug console logs
  - Increased history limit to 100 messages
  - Updated placeholder text ("Ask about Computer Science...")

---

## 🚀 Quick Setup

### 1. Copy Files to Your Laravel Project

```bash
# Backend Controller
cp "backend/Controllers/ChatbotController.php" "your-laravel-app/app/Http/Controllers/"

# Frontend Component
cp "frontend/components/KetupatsChatbot.jsx" "your-laravel-app/resources/js/components/"
```

### 2. Ensure Routes are Configured

In `routes/web.php`:
```php
Route::middleware(['auth'])->prefix('api/chatbot')->group(function () {
    Route::post('/message', [ChatbotController::class, 'sendMessage']);
    Route::get('/history', [ChatbotController::class, 'getHistory']);
    Route::delete('/history', [ChatbotController::class, 'clearHistory']);
    Route::get('/suggestions', [ChatbotController::class, 'getSuggestions']);
});
```

### 3. Update Database Migration

Ensure `document_id` is **nullable** in the chat table:
```php
$table->foreignId('document_id')->nullable()->constrained('document')->onDelete('cascade');
```

Run migration:
```bash
php artisan migrate:fresh
```

### 4. Set Gemini API Key

In `.env`:
```env
GEMINI_API_KEY=your_api_key_here
```

### 5. Initialize Chatbot

In `resources/js/global-components.jsx`:
```javascript
import KetupatsChatbot from './components/KetupatsChatbot';

const initChatbot = (targetId = 'chatbot-root') => {
  const container = document.getElementById(targetId);
  if (container) {
    const root = createRoot(container);
    root.render(<KetupatsChatbot autoOpen={false} />);
  }
};

// Call in DOMContentLoaded
if (isAuthenticated) {
  initChatbot('chatbot-root');
}
```

### 6. Add Container to View

In `resources/views/dashboard.blade.php`:
```html
<div id="chatbot-root"></div>
```

### 7. Compile Assets

```bash
npm run dev   # For development
npm run build # For production
```

---

## 🎯 Features

### Computer Science Scope Restriction
The chatbot **only answers Computer Science questions**:
- ✅ Programming (Python, Java, C++, JavaScript, etc.)
- ✅ Data Structures & Algorithms
- ✅ Software Engineering
- ✅ Web Development
- ✅ Databases
- ✅ AI/ML
- ✅ Networks
- ✅ Cybersecurity
- ❌ Biology, Physics, History, Cooking, Sports, etc.

**Example:**
- User: "What is photosynthesis?"
- Bot: "I'm sorry, but I can only assist with questions related to Computer Science..."

### Conversation History
- **100 messages** loaded automatically
- **Refresh button** (↻) to reload history
- **Message counter** shows total messages
- Persists across sessions

### Smart Suggestions
Six CS-focused quick suggestions:
1. What is an algorithm?
2. Explain object-oriented programming
3. What are data structures?
4. How does recursion work?
5. Explain SQL vs NoSQL
6. What is machine learning?

---

## 🔧 API Endpoints

### POST `/api/chatbot/message`
Send a message and get AI response.

**Request:**
```json
{
  "message": "What is a binary tree?",
  "conversation_id": "general_1"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user_message": {
      "id": 1,
      "message": "What is a binary tree?",
      "role": "user",
      "timestamp": "2025-12-09T12:00:00.000Z"
    },
    "assistant_message": {
      "id": 2,
      "message": "A binary tree is a hierarchical data structure...",
      "role": "assistant",
      "timestamp": "2025-12-09T12:00:05.000Z"
    }
  }
}
```

### GET `/api/chatbot/history?limit=100`
Get conversation history.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "role": "user",
      "message": "What is Python?",
      "timestamp": "2025-12-09T10:00:00.000Z"
    },
    {
      "id": 2,
      "role": "assistant",
      "message": "Python is a high-level programming language...",
      "timestamp": "2025-12-09T10:00:03.000Z"
    }
  ]
}
```

### DELETE `/api/chatbot/history`
Clear all chat history.

### GET `/api/chatbot/suggestions`
Get quick suggestion prompts.

---

## 🐛 Troubleshooting

### Issue: Chatbot shows "Sorry, I encountered an error"
**Solution:**
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify Gemini API key is set in `.env`
3. Clear caches: `php artisan optimize:clear`

### Issue: Conversation history not loading
**Solution:**
1. Open browser console (F12) and check for errors
2. Look for console logs: "Chat history loaded:", "Loaded X messages"
3. Verify `/api/chatbot/history` endpoint returns data

### Issue: 419 PAGE EXPIRED error
**Solution:**
```bash
php artisan cache:clear
php artisan config:clear
# Hard refresh browser (Ctrl+Shift+R)
```

### Issue: Gemini API 404 error
**Solution:**
The controller automatically falls back to `gemini-pro-latest` if `gemini-flash-latest` returns 404. Check logs for "Flash model 404, retrying with pro-latest".

---

## 📊 Database Schema

### `chat` table:
```sql
CREATE TABLE chat (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  document_id INTEGER NULL,  -- NULL for general chat
  role VARCHAR(50) NOT NULL,  -- 'user' or 'assistant'
  message TEXT NOT NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (document_id) REFERENCES document(id) ON DELETE CASCADE
);
```

---

## 🎨 UI Components

### Floating Chat Button
- Bottom-right corner
- Blue-purple gradient
- Animated sparkle badge (✨)
- Hover effects

### Chat Window
- 396px × 600px
- Slide-up animation
- Three buttons: Refresh (↻), Clear (🗑️), Close (✕)
- Auto-scroll to latest message

### Messages
- User messages: Right-aligned, blue-purple gradient
- Assistant messages: Left-aligned, white background
- Timestamp below each message
- Loading dots animation

---

## 🔑 Environment Variables

Required in `.env`:
```env
GEMINI_API_KEY=your_gemini_api_key_here
```

Get your free API key: [https://makersuite.google.com/app/apikey](https://makersuite.google.com/app/apikey)

**Free Tier Limits:**
- 15 requests/minute
- 1,500 requests/day
- 1M tokens/month

---

## 📝 Changelog

### v2.0 (December 2025)
- ✨ Computer Science scope restriction
- ✨ Conversation history (100 messages)
- ✨ Refresh history button
- ✨ Updated Gemini API (gemini-flash-latest)
- ✨ Improved error handling with fallback
- ✨ CS-focused suggestions
- 🐛 Fixed nullable document_id issue
- 🐛 Fixed infinite loading spinner
- 🎨 Enhanced UI with history indicator

### v1.0 (November 2025)
- Initial release
- Basic chat functionality
- Gemini API integration

---

## 👥 Team Collaboration

### Git Workflow
```bash
# Clone the module
git clone <your-repo-url> chatbot-module

# Create feature branch
git checkout -b feature/your-feature-name

# Commit changes
git add .
git commit -m "Add: your feature description"

# Push to remote
git push origin feature/your-feature-name
```

### Code Review Checklist
- [ ] Tested chatbot functionality
- [ ] Verified CS scope restriction works
- [ ] Checked conversation history loads
- [ ] Confirmed API key is not hardcoded
- [ ] Ran `npm run build` successfully
- [ ] No errors in browser console
- [ ] Laravel logs show no errors

---

## 📞 Support

For issues or questions:
1. Check the **Troubleshooting** section above
2. Review `docs/API.md` for API details
3. Check `docs/SETUP.md` for setup instructions
4. Open an issue on GitHub

---

## 📄 License

This project is part of Ketupats Labs educational platform.

---

**Last Updated:** December 9, 2025
**Version:** 2.0
**Status:** ✅ Production Ready
