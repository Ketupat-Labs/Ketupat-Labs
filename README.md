# CompuPlay - Quick Setup Guide

## One-Command Database Setup

After cloning this repository, follow these simple steps:

### Step 1: Install Dependencies
```bash
composer install
npm install
```

### Step 2: Configure Environment
```bash
# Copy the environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 3: Setup Database

**Edit `.env` file** with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=compuplay
DB_USERNAME=root
DB_PASSWORD=your_password
```

**Then run ONE command:**
```bash
php artisan db:setup
```

That's it! ✅ The database will be created and all tables will be imported automatically.

---

## Alternative: Manual Database Setup

If you prefer manual setup:

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE compuplay CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema
mysql -u root -p compuplay < CompuPlay.sql
```

---

## After Database Setup

### Build Assets
```bash
npm run build
```

### Start Development Server
```bash
php artisan serve
```

Visit: `http://127.0.0.1:8000`

---

## Default Login Credentials

After setup, you can login with:

**Teacher Account:**
- Email: `teacher@compuplay.com`
- Password: `password`

**Student Account:**
- Email: `student@compuplay.com`
- Password: `password`

---

## Troubleshooting

### Database Connection Error
- Check your `.env` file has correct database credentials
- Make sure MySQL is running
- Verify database user has proper permissions

### Migration Errors
- Run: `php artisan migrate:fresh` (⚠️ This will delete all data)
- Or use: `php artisan db:setup` to recreate from CompuPlay.sql

### Permission Errors
```bash
chmod -R 775 storage bootstrap/cache
```

---

## Project Structure

```
compuplay/
├── app/                    # Application code
├── database/
│   ├── migrations/         # Database migrations
│   └── schema/            # Schema backups
├── resources/
│   ├── views/             # Blade templates
│   └── js/                # React components
├── public/                # Public assets
├── CompuPlay.sql          # Complete database export
└── DATABASE_README.md     # Detailed database info
```

---

## Need Help?

- Check `DATABASE_README.md` for detailed database information
- Review Laravel documentation: https://laravel.com/docs
- Check the issues page on GitHub

---

## License

This project is open-source software licensed under the MIT license.
