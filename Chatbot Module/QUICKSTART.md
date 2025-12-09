# ⚡ Quick Start Guide - Chatbot Module

Get up and running in 5 minutes!

## 🚀 Quick Setup (5 Steps)

### 1️⃣ Install Dependencies
```bash
cd backend
npm install
```

### 2️⃣ Configure Environment
```bash
# Create .env file
echo "PORT=3000" > .env
echo "GEMINI_API_KEY=your_api_key_here" >> .env
echo "NODE_ENV=development" >> .env
```

### 3️⃣ Setup Database
```bash
# If using Laravel
cd ../../sample-laravel-app
php artisan migrate
```

### 4️⃣ Start Server
```bash
cd ../Chatbot\ Module/backend
npm start
```

### 5️⃣ Test It!
Open: http://localhost:3000

## 📦 What's Included

```
Chatbot Module/
├── backend/           # Node.js server files
│   ├── server.js     # Main server
│   ├── App.js        # Application logic
│   ├── Chatbot.js    # Chatbot controller
│   └── Chat.php      # Laravel model
│
├── frontend/         # HTML interface
│   └── index.html    # Chatbot UI
│
├── database/         # Database migrations
│   └── create_chat_table.php
│
└── docs/            # Documentation
    ├── API.md       # API reference
    ├── SETUP.md     # Detailed setup
    └── CONTRIBUTING.md  # How to contribute
```

## 🎯 Basic Usage

### Send a Message
```javascript
fetch('http://localhost:3000/api/chat/message', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    user_id: 1,
    message: 'Hello!',
    role: 'user'
  })
});
```

### View in Browser
Open `frontend/index.html` in your browser!

## 🔧 Common Commands

```bash
# Start server
npm start

# Development mode (auto-reload)
npm run dev

# Run tests
npm test

# Check health
curl http://localhost:3000/health
```

## ❓ Troubleshooting

**Port already in use?**
```bash
# Change port in .env
PORT=3001
```

**Can't connect to database?**
```bash
# Check Laravel .env database settings
DB_CONNECTION=sqlite  # or mysql
```

**API key error?**
Get a free key: https://makersuite.google.com/app/apikey

## 📚 Learn More

- [Full Setup Guide](docs/SETUP.md)
- [API Documentation](docs/API.md)
- [Contributing Guide](docs/CONTRIBUTING.md)

## 🐛 Issues?

Create an issue on GitHub with:
- Error message
- Steps to reproduce
- Your environment (OS, Node version)

## 🎉 You're Ready!

Start building awesome chatbot features!

---

**Made with ❤️ by Ketupats Labs Team**
