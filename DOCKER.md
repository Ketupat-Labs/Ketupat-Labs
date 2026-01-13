# Docker Setup for CompuPlay

This document provides comprehensive instructions for running the CompuPlay Laravel + React application using Docker.

## 📋 Prerequisites

- Docker Desktop (Windows/Mac) or Docker Engine (Linux)
- Docker Compose v2.0+
- At least 4GB of available RAM
- Git (for cloning the repository)

## 🏗️ Architecture

The application uses a multi-container architecture:

- **app**: PHP 8.2-FPM running Laravel application
- **nginx**: Nginx web server (port 80)
- **mysql**: MySQL 8.0 database (port 3306)
- **redis**: Redis 7 for caching and sessions (port 6379)
- **reverb**: Laravel Reverb WebSocket server (port 8080)

## 🚀 Quick Start

### 1. Clone and Setup

```bash
# Navigate to project directory
cd "c:\xampp\htdocs\CompuPlay - Test"

# Copy environment file
cp .env.docker .env

# Generate application key (will be done automatically on first run)
```

### 2. Build and Start Containers

```bash
# Build all containers
docker compose build

# Start all services in detached mode
docker compose up -d

# View logs
docker compose logs -f
```

### 3. Access the Application

- **Web Application**: http://localhost
- **WebSocket Server**: http://localhost:8080
- **Redis**: localhost:6379

### 4. Initial Setup

The entrypoint script automatically handles:
- ✅ Database migrations
- ✅ Application key generation
- ✅ Cache optimization
- ✅ Storage link creation
- ✅ Permission setup

## 📦 Database Import (Optional)

If you want to import your existing database:

1. Copy your SQL file to the docker/mysql directory:
```bash
cp CompuPlay.sql docker/mysql/
```

2. Update `docker/mysql/init.txt` to uncomment the import line

3. Rebuild the MySQL container:
```bash
docker compose down -v
docker compose up -d mysql
```

**Note**: The `.gitignore` blocks `.sql` files, so the init script is named `init.txt`. You can manually create `init.sql` if needed.

## 🛠️ Common Commands

### Container Management

```bash
# Start all services
docker compose up -d

# Stop all services
docker compose down

# Restart a specific service
docker compose restart app

# View running containers
docker compose ps

# View logs for all services
docker compose logs -f

# View logs for specific service
docker compose logs -f app
```

### Laravel Artisan Commands

```bash
# Run artisan commands
docker compose exec app php artisan migrate
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan queue:work

# Access Laravel Tinker
docker compose exec app php artisan tinker
```

### Database Access

```bash
# Access MySQL CLI (via app container as MySQL is external)
docker compose exec app mysql -h [DB_HOST] -u [DB_USER] -p[DB_PASSWORD] [DB_NAME]

# Backup database
docker compose exec app mysqldump -h [DB_HOST] -u [DB_USER] -p[DB_PASSWORD] [DB_NAME] > backup.sql
```

### Shell Access

```bash
# Access app container shell
docker compose exec app sh

# Access as root
docker compose exec -u root app sh
```

## 🔧 Configuration

### Environment Variables

Edit `.env` file to customize:

- **Database credentials**: `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- **Application URL**: `APP_URL`
- **Ports**: `APP_PORT`, `REVERB_PORT`
- **Debug mode**: `APP_DEBUG` (set to `true` for development)

### Ports

Default ports can be changed in `.env`:

```env
APP_PORT=80          # Nginx web server
DB_PORT=3306         # MySQL database
REDIS_PORT=6379      # Redis cache
REVERB_PORT=8080     # WebSocket server
```

## 🔄 Development Workflow

### Frontend Asset Development

For development with hot module replacement:

```bash
# Install dependencies locally (if not using Docker for frontend)
npm install

# Run Vite dev server
npm run dev
```

Or build assets inside Docker:

```bash
# Rebuild with latest assets
docker compose build app
docker compose up -d app
```

### Code Changes

- **PHP/Laravel changes**: Automatically reflected (no rebuild needed)
- **Frontend changes**: Run `npm run build` and restart nginx
- **Configuration changes**: Run `docker compose restart app`
- **Dockerfile changes**: Run `docker compose build` and `docker compose up -d`

## 🐛 Troubleshooting

### Container won't start

```bash
# Check logs
docker compose logs app

# Check all container status
docker compose ps
```

### Database connection issues

```bash
# Verify connection
docker compose exec app php artisan db:show
```

### Permission issues

```bash
# Fix storage permissions
docker compose exec -u root app chown -R www-data:www-data /var/www/html/storage
docker compose exec -u root app chmod -R 775 /var/www/html/storage
```

### Clear all data and restart

```bash
# Stop and remove all containers, networks, and volumes
docker compose down -v

# Rebuild and start fresh
docker compose build --no-cache
docker compose up -d
```

### Port already in use

If port 80 is already in use:

```bash
# Change APP_PORT in .env
APP_PORT=8000

# Restart containers
docker compose down
docker compose up -d
```

## 🚀 Production Deployment

### Optimization

1. Set environment to production:
```env
APP_ENV=production
APP_DEBUG=false
```

2. Build optimized assets:
```bash
npm run build
```

3. Build production image:
```bash
docker compose build --no-cache
```

### Security Checklist

- ✅ Change default database passwords
- ✅ Set `APP_DEBUG=false`
- ✅ Use strong `APP_KEY`
- ✅ Configure proper `APP_URL`
- ✅ Set up SSL/TLS (use reverse proxy like Traefik or Caddy)
- ✅ Restrict database access
- ✅ Use environment-specific `.env` files

### Scaling

To scale specific services:

```bash
# Scale app containers
docker compose up -d --scale app=3
```

## 📊 Monitoring

### Health Checks

```bash
# Check container health
docker compose ps

# Redis health
docker compose exec redis redis-cli ping
```

### Resource Usage

```bash
# View resource usage
docker stats

# View specific container
docker stats compuplay-app
```

## 🔄 Updates and Maintenance

### Update Application

```bash
# Pull latest code
git pull

# Rebuild containers
docker compose build

# Restart with new code
docker compose up -d

# Run migrations
docker compose exec app php artisan migrate --force
```

### Backup

```bash
# Backup database
docker compose exec app mysqldump -h [DB_HOST] -u [DB_USER] -p[DB_PASSWORD] [DB_NAME] > backup_$(date +%Y%m%d).sql

# Backup uploads
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz uploads/
```

## 📝 Notes

- The `vendor` and `node_modules` directories are excluded from the Docker context for faster builds
- Storage and uploads are mounted as volumes for persistence
- Redis is used for caching and sessions by default
- The application automatically runs migrations on startup
- WebSocket functionality is handled by Laravel Reverb on port 8080

## 🆘 Support

For issues specific to Docker setup, check:
1. Docker logs: `docker-compose logs`
2. Container status: `docker-compose ps`
3. Laravel logs: `storage/logs/laravel.log`
4. Nginx logs: `docker-compose logs nginx`

---

**Happy Coding! 🎉**
