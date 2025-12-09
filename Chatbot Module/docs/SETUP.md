# 🚀 Setup Guide - Chatbot Module

This guide will help you set up the Chatbot Module for local development and production deployment.

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

- **Node.js** (v14 or higher) - [Download](https://nodejs.org/)
- **npm** or **yarn** - Package manager
- **PHP** (v8.1 or higher) - [Download](https://www.php.net/downloads)
- **Composer** - PHP dependency manager
- **Laravel** (v11 or higher) - PHP framework
- **MySQL/SQLite** - Database
- **Git** - Version control
- **Google Gemini API Key** - [Get one here](https://makersuite.google.com/app/apikey)

## 🔧 Installation Steps

### 1. Clone the Repository

```bash
# Clone the chatbot module
cd your-project-directory
git clone https://github.com/your-team/chatbot-module.git

# Navigate to the module directory
cd chatbot-module
```

### 2. Backend Setup (Node.js)

```bash
# Navigate to backend directory
cd backend

# Install dependencies
npm install

# Create .env file
cp .env.example .env

# Edit .env with your configuration
nano .env
```

**Backend `.env` Configuration:**
```env
# Server Configuration
PORT=3000
NODE_ENV=development

# Google Gemini AI
GEMINI_API_KEY=your_gemini_api_key_here

# Database
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ketupats_labs
DB_USERNAME=root
DB_PASSWORD=

# Session
SESSION_SECRET=your_random_session_secret

# CORS
ALLOWED_ORIGINS=http://localhost:8000,http://127.0.0.1:8000

# Logging
LOG_LEVEL=debug
```

### 3. Laravel Integration

```bash
# Navigate to your Laravel project
cd ../../../sample-laravel-app

# Copy Chat model (if not already done)
# It should already be in app/Models/Chat.php

# Run migration
php artisan migrate

# Verify migration
php artisan migrate:status
```

### 4. Database Setup

#### Option A: MySQL

```bash
# Create database
mysql -u root -p
```

```sql
CREATE DATABASE ketupats_labs;
USE ketupats_labs;
```

#### Option B: SQLite (Simpler)

```bash
# Database file is already created at:
# sample-laravel-app/database/database.sqlite
```

### 5. Install Frontend Dependencies (Optional)

If you're using build tools for the frontend:

```bash
cd frontend
npm install
npm run build
```

### 6. Start the Servers

#### Start Backend Server

```bash
cd backend
npm start

# Or for development with auto-reload:
npm run dev
```

You should see:
```
✓ Chatbot server running on http://localhost:3000
✓ Socket.IO enabled
✓ Connected to database
```

#### Start Laravel Server

```bash
cd sample-laravel-app
php artisan serve
```

You should see:
```
Server running on http://127.0.0.1:8000
```

### 7. Test the Installation

Open your browser and navigate to:
- **Laravel App**: http://127.0.0.1:8000
- **Chatbot Backend**: http://localhost:3000
- **Health Check**: http://localhost:3000/health

## 🔑 Getting Google Gemini API Key

1. Visit [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Sign in with your Google account
3. Click "Create API Key"
4. Copy the key and add it to your `.env` file

**Free Tier Limits:**
- 15 requests per minute
- 1,500 requests per day
- 1 million tokens per month

## 🧪 Testing

### Run Backend Tests

```bash
cd backend
npm test
```

### Test API Endpoints

```bash
# Test health endpoint
curl http://localhost:3000/health

# Test chat endpoint (requires auth)
curl -X POST http://localhost:3000/api/chat/message \
  -H "Content-Type: application/json" \
  -d '{"user_id": 1, "message": "Hello", "role": "user"}'
```

### Test WebSocket Connection

Create a test file `test-socket.html`:

```html
<!DOCTYPE html>
<html>
<head>
    <title>Socket Test</title>
    <script src="https://cdn.socket.io/4.6.0/socket.io.min.js"></script>
</head>
<body>
    <h1>WebSocket Test</h1>
    <div id="status">Connecting...</div>
    <script>
        const socket = io('http://localhost:3000');
        
        socket.on('connect', () => {
            document.getElementById('status').textContent = 'Connected!';
            console.log('Connected to WebSocket');
        });
        
        socket.on('disconnect', () => {
            document.getElementById('status').textContent = 'Disconnected';
        });
    </script>
</body>
</html>
```

## 🐛 Troubleshooting

### Port Already in Use

```bash
# Kill process on port 3000
# Windows:
netstat -ano | findstr :3000
taskkill /PID <PID> /F

# macOS/Linux:
lsof -ti:3000 | xargs kill -9
```

### Database Connection Error

1. Check your `.env` database credentials
2. Ensure MySQL/SQLite is running
3. Verify database exists: `php artisan db:show`

### Gemini API Error

1. Verify your API key is correct
2. Check you haven't exceeded rate limits
3. Ensure you have API access enabled

### CORS Error

Add your frontend URL to `ALLOWED_ORIGINS` in backend `.env`:

```env
ALLOWED_ORIGINS=http://localhost:8000,http://127.0.0.1:8000
```

### Module Not Found Error

```bash
# Clear npm cache and reinstall
rm -rf node_modules package-lock.json
npm install
```

## 🚀 Production Deployment

### Environment Configuration

```env
NODE_ENV=production
PORT=3000
LOG_LEVEL=error
```

### Build Frontend

```bash
cd frontend
npm run build
```

### PM2 (Process Manager)

```bash
# Install PM2
npm install -g pm2

# Start server with PM2
cd backend
pm2 start server.js --name chatbot-module

# View logs
pm2 logs chatbot-module

# Monitor
pm2 monit
```

### Docker Deployment (Optional)

Create `Dockerfile`:

```dockerfile
FROM node:18-alpine
WORKDIR /app
COPY backend/package*.json ./
RUN npm ci --only=production
COPY backend/ .
EXPOSE 3000
CMD ["node", "server.js"]
```

Build and run:

```bash
docker build -t chatbot-module .
docker run -p 3000:3000 --env-file .env chatbot-module
```

## 📊 Monitoring

### Health Check Endpoint

```bash
curl http://localhost:3000/health
```

Response:
```json
{
  "status": "healthy",
  "uptime": 3600,
  "timestamp": "2025-11-18T12:00:00Z"
}
```

### Logs

```bash
# View logs
tail -f backend/logs/app.log

# PM2 logs
pm2 logs chatbot-module
```

## 🔐 Security Checklist

- ✅ API keys stored in `.env` (not in code)
- ✅ `.env` added to `.gitignore`
- ✅ CORS properly configured
- ✅ Authentication required for all endpoints
- ✅ Input validation implemented
- ✅ Rate limiting enabled
- ✅ HTTPS in production

## 📞 Support

If you encounter issues:

1. Check the [Troubleshooting](#troubleshooting) section
2. Review logs for error messages
3. Search existing GitHub issues
4. Create a new issue with:
   - Error message
   - Steps to reproduce
   - Environment details (OS, Node version, etc.)

## 🔗 Next Steps

- Read the [API Documentation](API.md)
- Review [Contributing Guidelines](CONTRIBUTING.md)
- Check out [Architecture Overview](../README.md#architecture)

---

**Setup completed! 🎉 You're ready to start developing!**
