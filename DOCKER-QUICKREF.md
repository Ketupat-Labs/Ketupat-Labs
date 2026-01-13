# CompuPlay Docker - Quick Reference

## 🚀 Quick Start
```bash
# Windows PowerShell
.\docker-start.ps1

# Linux/Mac
./docker-start.sh

# Or using Make
make install
```

## 📦 Services
- **Web**: http://localhost
- **WebSocket**: http://localhost:8080
- **Redis**: localhost:6379

## 🔧 Essential Commands

> **Note**: These commands use `docker compose`. If you have an older version, you may need to use `docker-compose`.

### Container Management
```bash
docker compose up -d          # Start all services
docker compose down           # Stop all services
docker compose restart        # Restart all services
docker compose ps             # Show status
docker compose logs -f        # View logs
```

### Laravel Commands
```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan cache:clear
docker compose exec app php artisan tinker
```

### Database
*Note: MySQL is hosted on Aiven Cloud, not locally.*

```bash
# Access MySQL (via app container)
docker compose exec app mysql -h [DB_HOST] -u [DB_USER] -p[DB_PASSWORD] [DB_NAME]
```

### Shell Access
```bash
docker compose exec app sh           # App container
docker compose exec -u root app sh   # As root
```

## 📁 File Structure
```
CompuPlay/
├── Dockerfile                 # Multi-stage build
├── docker-compose.yml         # Service orchestration
├── .dockerignore             # Build optimization
├── .env.docker               # Environment template
├── docker/
│   ├── nginx/                # Nginx configs
│   ├── php/                  # PHP configs
│   └── entrypoint.sh         # App initialization
├── docker-start.ps1          # Windows quick start
├── docker-start.sh           # Linux/Mac quick start
├── Makefile                  # Command shortcuts
└── DOCKER.md                 # Full documentation
```
