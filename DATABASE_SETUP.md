# CompuPlay Database Setup

## Importing the Database

This project includes a complete database dump in `CompuPlay.sql` with all demo/test data.

### MySQL Import Instructions

1. Create the database:
```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS compuplay_tracking;"
```

2. Import the SQL file:
```bash
mysql -u root -p compuplay_tracking < CompuPlay.sql
```

3. Update your `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=compuplay_tracking
DB_USERNAME=root
DB_PASSWORD=your_password
```

4. Run migrations (if needed):
```bash
php artisan migrate
```

### What's Included

The `CompuPlay.sql` file contains:
- All database tables structure
- Demo users (teachers and students)
- Sample lessons and activities
- Test classrooms and enrollments
- Sample forum posts and messages
- Badge and achievement data

### Notes

- The SQL file is compatible with MySQL 5.7+
- All passwords in demo data are hashed
- This is for development/demo purposes only
