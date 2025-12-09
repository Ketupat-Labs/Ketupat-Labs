# 🤖 Ketupats Labs - Chatbot Module

A comprehensive chatbot module for the Ketupats Labs educational platform. This module provides AI-powered chat functionality to assist students and teachers with document queries, course information, and general support.

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Project Structure](#project-structure)
- [Installation](#installation)
- [Usage](#usage)
- [API Documentation](#api-documentation)
- [Technologies Used](#technologies-used)
- [Contributing](#contributing)
- [Team Members](#team-members)

## 🎯 Overview

The Chatbot Module is an intelligent conversational interface that integrates with the Ketupats Labs platform. It provides:

- **AI-Powered Responses**: Uses Google Gemini API for intelligent answers
- **Context-Aware**: Understands document content and user context
- **Real-time Chat**: WebSocket support for instant messaging
- **Multi-User Support**: Handles multiple conversations simultaneously
- **Document Integration**: Can answer questions about uploaded documents

## ✨ Features

### Core Features
- ✅ Natural language processing
- ✅ Context-aware responses
- ✅ Document-based Q&A
- ✅ Real-time messaging
- ✅ Message history
- ✅ User authentication integration
- ✅ Responsive UI design
- ✅ Mobile-friendly interface

### Advanced Features
- 🔄 Typing indicators
- 💾 Message persistence
- 🔍 Search chat history
- 📊 Analytics tracking
- 🌙 Dark mode support
- 🔔 Notification system

## 📁 Project Structure

```
Chatbot Module/
├── backend/                    # Backend server files
│   ├── App.js                 # Main application logic
│   ├── Chatbot.js             # Chatbot controller
│   ├── server.js              # Express/Node.js server
│   └── Chat.php               # Laravel Chat model
│
├── frontend/                   # Frontend UI files
│   └── index.html             # Chatbot interface
│
├── database/                   # Database files
│   └── 2025_11_11_093902_create_chat_table.php  # Migration
│
├── docs/                       # Documentation
│   ├── API.md                 # API documentation
│   ├── SETUP.md               # Setup guide
│   └── CONTRIBUTING.md        # Contribution guidelines
│
├── assets/                     # Static assets
│   ├── css/                   # Stylesheets
│   ├── js/                    # JavaScript files
│   └── images/                # Images and icons
│
├── README.md                   # This file
├── .gitignore                 # Git ignore rules
└── package.json               # Node.js dependencies
```

## 🚀 Installation

### Prerequisites

- Node.js >= 14.x
- PHP >= 8.1
- MySQL/SQLite database
- Composer
- Laravel >= 11.x
- Google Gemini API Key

### Step 1: Clone the Repository

```bash
git clone https://github.com/your-team/chatbot-module.git
cd chatbot-module
```

### Step 2: Install Backend Dependencies

```bash
cd backend
npm install
```

### Step 3: Configure Environment

Create a `.env` file in the backend directory:

```env
PORT=3000
GEMINI_API_KEY=your_api_key_here
DATABASE_URL=mysql://user:password@localhost:3306/ketupats_labs
SESSION_SECRET=your_session_secret
```

### Step 4: Setup Database

```bash
# Run Laravel migration
php artisan migrate
```

### Step 5: Start the Server

```bash
# Backend server
cd backend
node server.js

# Or use nodemon for development
npm run dev
```

### Step 6: Access the Chatbot

Open your browser and navigate to:
- Frontend: `http://localhost:8000` (Laravel app)
- Backend API: `http://localhost:3000`

## 📖 Usage

### Basic Integration

Include the chatbot in your HTML:

```html
<div id="chatbot-container"></div>
<script src="chatbot.js"></script>
<script>
  const chatbot = new Chatbot({
    containerId: 'chatbot-container',
    apiUrl: 'http://localhost:3000',
    theme: 'light'
  });
  chatbot.init();
</script>
```

### Laravel Integration

Add to your blade template:

```php
@include('components.chatbot')
```

### JavaScript API

```javascript
// Send a message
chatbot.sendMessage('Hello, how can I help?');

// Listen for responses
chatbot.on('message', (message) => {
  console.log('Received:', message);
});

// Clear chat history
chatbot.clearHistory();
```

## 📚 API Documentation

### Endpoints

#### POST `/api/chat/message`
Send a chat message

**Request:**
```json
{
  "user_id": 123,
  "document_id": 456,
  "message": "What is this document about?",
  "role": "user"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 789,
    "role": "assistant",
    "message": "This document discusses...",
    "created_at": "2025-11-18T12:00:00Z"
  }
}
```

#### GET `/api/chat/history/:document_id`
Get chat history for a document

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "role": "user",
      "message": "Hello",
      "created_at": "2025-11-18T12:00:00Z"
    }
  ]
}
```

See [API.md](docs/API.md) for complete API documentation.

## 🛠️ Technologies Used

### Backend
- **Node.js** - Runtime environment
- **Express.js** - Web framework
- **Socket.io** - Real-time communication
- **Laravel** - PHP framework
- **Google Gemini API** - AI responses

### Frontend
- **HTML5/CSS3** - Structure and styling
- **JavaScript (ES6+)** - Interactivity
- **Tailwind CSS** - Utility-first CSS

### Database
- **MySQL/SQLite** - Data storage
- **Laravel Eloquent ORM** - Database abstraction

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

See [CONTRIBUTING.md](docs/CONTRIBUTING.md) for detailed guidelines.

## 👥 Team Members

| Name | Role | GitHub | Email |
|------|------|--------|-------|
| [Your Name] | Lead Developer | [@username] | email@example.com |
| [Member 2] | Backend Developer | [@username2] | email2@example.com |
| [Member 3] | Frontend Developer | [@username3] | email3@example.com |
| [Member 4] | QA Engineer | [@username4] | email4@example.com |

## 📄 License

This project is part of the Ketupats Labs educational platform.
Copyright © 2025 Ketupats Labs. All rights reserved.

## 📞 Support

For support, email support@ketupatslabs.com or create an issue in the repository.

## 🔗 Related Projects

- [Ketupats Labs Main App](https://github.com/your-org/ketupats-labs)
- [AI Analyzer Module](https://github.com/your-org/ai-analyzer)
- [Document Manager](https://github.com/your-org/document-manager)

## 📝 Changelog

### Version 1.0.0 (2025-11-18)
- Initial release
- Basic chat functionality
- AI integration
- Document context support
- Real-time messaging

---

**Built with ❤️ by the Ketupats Labs Team**
