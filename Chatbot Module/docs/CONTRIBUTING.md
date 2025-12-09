# 🤝 Contributing Guidelines

Thank you for considering contributing to the Chatbot Module! This document provides guidelines and instructions for contributing.

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Workflow](#development-workflow)
- [Coding Standards](#coding-standards)
- [Commit Guidelines](#commit-guidelines)
- [Pull Request Process](#pull-request-process)
- [Testing](#testing)
- [Documentation](#documentation)

## 🤝 Code of Conduct

### Our Pledge

- Be respectful and inclusive
- Welcome newcomers and help them learn
- Focus on what is best for the community
- Show empathy towards other team members

### Unacceptable Behavior

- Harassment or discriminatory language
- Trolling or insulting comments
- Public or private harassment
- Publishing others' private information

## 🚀 Getting Started

### 1. Fork the Repository

```bash
# Fork on GitHub, then clone your fork
git clone https://github.com/YOUR_USERNAME/chatbot-module.git
cd chatbot-module
```

### 2. Add Upstream Remote

```bash
git remote add upstream https://github.com/original-team/chatbot-module.git
```

### 3. Create a Branch

```bash
# Create a feature branch
git checkout -b feature/your-feature-name

# Or a bugfix branch
git checkout -b bugfix/issue-number-description
```

## 🔄 Development Workflow

### 1. Keep Your Fork Updated

```bash
# Fetch upstream changes
git fetch upstream

# Merge upstream changes
git checkout main
git merge upstream/main

# Push to your fork
git push origin main
```

### 2. Make Your Changes

```bash
# Work on your feature branch
git checkout feature/your-feature-name

# Make changes...

# Stage changes
git add .

# Commit (see commit guidelines below)
git commit -m "feat: add new feature"
```

### 3. Push Changes

```bash
# Push to your fork
git push origin feature/your-feature-name
```

### 4. Create Pull Request

1. Go to your fork on GitHub
2. Click "New Pull Request"
3. Select your feature branch
4. Fill in the PR template
5. Submit for review

## 📝 Coding Standards

### JavaScript/Node.js

#### Style Guide

- Use **ES6+** syntax
- Use **2 spaces** for indentation
- Use **camelCase** for variables and functions
- Use **PascalCase** for classes
- Use **UPPER_CASE** for constants

#### Example:

```javascript
// Good
const apiKey = process.env.GEMINI_API_KEY;
const MAX_RETRIES = 3;

class ChatbotService {
  async sendMessage(message) {
    // Implementation
  }
}

// Bad
const api_key = process.env.GEMINI_API_KEY;
const maxretries = 3;

class chatbot_service {
  async send_message(message) {
    // Implementation
  }
}
```

### PHP/Laravel

#### Style Guide

- Follow **PSR-12** standards
- Use **4 spaces** for indentation
- Use **camelCase** for methods
- Use **snake_case** for database columns
- Add **type hints** and **return types**

#### Example:

```php
// Good
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = ['user_id', 'message', 'role'];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

// Bad
namespace App\Models;
class chat extends Model{
protected $fillable=['user_id','message','role'];
function user(){
return $this->belongsTo(User::class);
}
}
```

### HTML/CSS

- Use **semantic HTML5** elements
- Use **2 spaces** for indentation
- Use **kebab-case** for CSS classes
- Mobile-first responsive design

```html
<!-- Good -->
<section class="chat-container">
  <div class="message-list">
    <article class="message-item">
      <!-- Content -->
    </article>
  </div>
</section>

<!-- Bad -->
<div class="ChatContainer">
  <div class="MessageList">
    <div class="MessageItem">
      <!-- Content -->
    </div>
  </div>
</div>
```

## 📝 Commit Guidelines

### Commit Message Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- **feat**: New feature
- **fix**: Bug fix
- **docs**: Documentation changes
- **style**: Code style changes (formatting)
- **refactor**: Code refactoring
- **test**: Adding tests
- **chore**: Maintenance tasks

### Examples

```bash
# Feature
git commit -m "feat(chatbot): add typing indicator"

# Bug fix
git commit -m "fix(api): resolve CORS issue in production"

# Documentation
git commit -m "docs(readme): update installation instructions"

# Refactoring
git commit -m "refactor(server): improve error handling"
```

### Full Example

```
feat(chatbot): add message history pagination

Added pagination support for chat history with configurable
page size. Improves performance when loading large chat logs.

Changes:
- Added limit and offset parameters to API
- Implemented pagination in frontend
- Added tests for pagination logic

Closes #123
```

## 🔍 Pull Request Process

### PR Checklist

Before submitting, ensure:

- [ ] Code follows style guidelines
- [ ] All tests pass (`npm test`)
- [ ] New tests added for new features
- [ ] Documentation updated
- [ ] No console.log() or debugging code
- [ ] Branch is up to date with main
- [ ] Descriptive PR title and description
- [ ] Linked to relevant issues

### PR Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
How has this been tested?

## Screenshots (if applicable)
Add screenshots here

## Related Issues
Closes #123
```

### Review Process

1. **Automated Checks**: CI/CD runs tests
2. **Code Review**: Team members review code
3. **Feedback**: Address reviewer comments
4. **Approval**: Get 2+ approvals
5. **Merge**: Maintainer merges PR

## 🧪 Testing

### Writing Tests

```javascript
// Example test
describe('ChatbotService', () => {
  test('should send message successfully', async () => {
    const service = new ChatbotService();
    const result = await service.sendMessage('Hello');
    
    expect(result.success).toBe(true);
    expect(result.message).toBeDefined();
  });
  
  test('should handle API errors', async () => {
    const service = new ChatbotService();
    
    await expect(
      service.sendMessage('')
    ).rejects.toThrow('Message cannot be empty');
  });
});
```

### Running Tests

```bash
# Run all tests
npm test

# Run specific test file
npm test chatbot.test.js

# Run with coverage
npm test -- --coverage

# Watch mode
npm test -- --watch
```

### Test Coverage Requirements

- **Minimum**: 80% code coverage
- **Critical paths**: 100% coverage
- All new features must include tests

## 📚 Documentation

### Code Documentation

```javascript
/**
 * Sends a message to the chatbot and returns AI response
 * @param {string} message - The user's message
 * @param {Object} context - Additional context (optional)
 * @param {number} context.userId - User ID
 * @param {number} context.documentId - Document ID
 * @returns {Promise<Object>} AI response with message and metadata
 * @throws {Error} If message is empty or API fails
 */
async function sendMessage(message, context = {}) {
  // Implementation
}
```

### README Updates

When adding features, update:
- Feature list
- Usage examples
- API documentation
- Configuration options

## 🏆 Recognition

Contributors will be:
- Added to CONTRIBUTORS.md
- Mentioned in release notes
- Credited in documentation

## 📞 Getting Help

- **Discord**: [Join our server](#)
- **Email**: dev-team@ketupatslabs.com
- **Issues**: [GitHub Issues](https://github.com/your-team/chatbot-module/issues)

## 🎯 Good First Issues

Looking to contribute but don't know where to start?

Look for issues tagged with:
- `good first issue`
- `help wanted`
- `documentation`

## 🚀 Advanced Topics

### Project Structure

```
backend/
├── controllers/     # Route handlers
├── services/        # Business logic
├── models/          # Data models
├── middleware/      # Express middleware
├── utils/           # Helper functions
└── tests/           # Test files
```

### Adding New Features

1. Discuss in GitHub issue first
2. Create feature branch
3. Implement with tests
4. Update documentation
5. Submit PR

### Debugging

```bash
# Enable debug logs
DEBUG=* npm run dev

# Node debugger
node --inspect backend/server.js
```

---

## ✅ Summary

1. Fork and clone the repository
2. Create a feature branch
3. Follow coding standards
4. Write tests
5. Update documentation
6. Submit pull request
7. Respond to feedback

**Thank you for contributing! 🎉**

---

**Questions?** Open an issue or reach out to the team!
