# CompuPlay Database Setup

## Quick Start

### Option 1: Import Complete Database (Recommended for GitHub Users)

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE compuplay CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import the complete schema
mysql -u root -p compuplay < CompuPlay.sql
```

### Option 2: Use Laravel Migrations (For Development)

```bash
# Run all migrations
php artisan migrate

# Seed sample data (if available)
php artisan db:seed
```

## Database Information

- **Total Tables**: 45 core application tables
- **Character Set**: utf8mb4
- **Collation**: utf8mb4_unicode_ci
- **Engine**: InnoDB

## Table Naming Convention

All tables use **singular** form for consistency:
- ✅ `user`, `activity`, `lesson`, `class`
- ✅ `activity_assignment`, `lesson_assignment`
- ✅ `activity_submission`, `user_badge`

## Core Tables

### User Management
- `user` - User accounts (students, teachers, admins)
- `class` - Classrooms
- `class_student` - Student-classroom enrollment

### Learning Content
- `lesson` - Learning lessons
- `activity` - Interactive activities (quizzes, games)
- `document` - Learning materials

### Assignments & Submissions
- `lesson_assignment` - Lesson assignments to classes
- `activity_assignment` - Activity assignments to classes
- `submission` - Lesson submissions
- `activity_submission` - Activity submissions with detailed results

### Social Features
- `forum` - Discussion forums
- `forum_post` - Forum posts
- `comment` - Comments on posts
- `friend` - Friend relationships
- `message` - Direct messages
- `conversation` - Message conversations

### Gamification
- `badge` - Achievement badges
- `user_badge` - User earned badges
- `quiz_attempt` - Quiz attempts and scores

## Notes

- The `CompuPlay.sql` file is auto-generated and includes all table structures
- Foreign key constraints are properly defined
- All timestamps use UTC timezone
- JSON columns are used for flexible data storage (activity content, quiz results, etc.)

## Troubleshooting

If you encounter foreign key errors during import:
```bash
# Disable foreign key checks temporarily
mysql -u root -p compuplay -e "SET FOREIGN_KEY_CHECKS=0; SOURCE CompuPlay.sql; SET FOREIGN_KEY_CHECKS=1;"
```

## Regenerating the SQL File

To regenerate `CompuPlay.sql` with latest schema:
```bash
php database/export_for_github.php
```
