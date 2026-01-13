# Makefile for CompuPlay Docker Management

# Try to detect docker compose command
DOCKER_COMPOSE := $(shell if docker compose version > /dev/null 2>&1; then echo "docker compose"; else echo "docker-compose"; fi)

.PHONY: help build up down restart logs shell clean

help: ## Show this help message
	@echo "CompuPlay Docker Management"
	@echo "==========================="
	@echo ""
	@echo "Using command: $(DOCKER_COMPOSE)"
	@echo ""
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

build: ## Build all Docker containers
	$(DOCKER_COMPOSE) build

up: ## Start all containers
	$(DOCKER_COMPOSE) up -d

down: ## Stop all containers
	$(DOCKER_COMPOSE) down

restart: ## Restart all containers
	$(DOCKER_COMPOSE) restart

logs: ## View logs from all containers
	$(DOCKER_COMPOSE) logs -f

logs-app: ## View logs from app container
	$(DOCKER_COMPOSE) logs -f app

logs-nginx: ## View logs from nginx container
	$(DOCKER_COMPOSE) logs -f nginx

shell: ## Access app container shell
	$(DOCKER_COMPOSE) exec app sh

shell-root: ## Access app container shell as root
	$(DOCKER_COMPOSE) exec -u root app sh

mysql: ## Access MySQL CLI
	$(DOCKER_COMPOSE) exec -u root app mysql -h $(shell grep DB_HOST .env | cut -d '=' -f2) -u $(shell grep DB_USERNAME .env | cut -d '=' -f2) -p$(shell grep DB_PASSWORD .env | cut -d '=' -f2) $(shell grep DB_DATABASE .env | cut -d '=' -f2)

redis: ## Access Redis CLI
	$(DOCKER_COMPOSE) exec redis redis-cli

artisan: ## Run artisan command (usage: make artisan CMD="migrate")
	$(DOCKER_COMPOSE) exec app php artisan $(CMD)

migrate: ## Run database migrations
	$(DOCKER_COMPOSE) exec app php artisan migrate

migrate-fresh: ## Fresh migration with seed
	$(DOCKER_COMPOSE) exec app php artisan migrate:fresh --seed

cache-clear: ## Clear all caches
	$(DOCKER_COMPOSE) exec app php artisan cache:clear
	$(DOCKER_COMPOSE) exec app php artisan config:clear
	$(DOCKER_COMPOSE) exec app php artisan route:clear
	$(DOCKER_COMPOSE) exec app php artisan view:clear

optimize: ## Optimize application for production
	$(DOCKER_COMPOSE) exec app php artisan config:cache
	$(DOCKER_COMPOSE) exec app php artisan route:cache
	$(DOCKER_COMPOSE) exec app php artisan view:cache

test: ## Run tests
	$(DOCKER_COMPOSE) exec app php artisan test

clean: ## Remove all containers, volumes, and images
	$(DOCKER_COMPOSE) down -v --rmi all

rebuild: ## Rebuild and restart all containers
	$(DOCKER_COMPOSE) down
	$(DOCKER_COMPOSE) build --no-cache
	$(DOCKER_COMPOSE) up -d

status: ## Show container status
	$(DOCKER_COMPOSE) ps

backup-db: ## Backup database
	$(DOCKER_COMPOSE) exec app mysqldump -h $(shell grep DB_HOST .env | cut -d '=' -f2) -u $(shell grep DB_USERNAME .env | cut -d '=' -f2) -p$(shell grep DB_PASSWORD .env | cut -d '=' -f2) $(shell grep DB_DATABASE .env | cut -d '=' -f2) > backup_$$(date +%Y%m%d_%H%M%S).sql
	@echo "Database backed up successfully"

install: ## Initial setup (copy .env, build, start)
	cp -n .env.docker .env || true
	$(DOCKER_COMPOSE) build
	$(DOCKER_COMPOSE) up -d
	@echo "Installation complete! Access the app at http://localhost"
