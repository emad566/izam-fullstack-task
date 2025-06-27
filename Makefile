# IZAM E-commerce Docker Makefile

.PHONY: help build up down restart logs shell test migrate seed fresh install clean dev prod

# Default target
help: ## Show this help message
	@echo "🚀 IZAM E-commerce Development Commands"
	@echo ""
	@echo "Setup Commands:"
	@echo "  setup          - Initial Docker setup (run once)"
	@echo "  build          - Build Docker containers"
	@echo "  up             - Start production containers"
	@echo "  down           - Stop all containers"
	@echo ""
	@echo "Development Commands:"
	@echo "  dev            - Start development mode with hot reloading"
	@echo "  dev-down       - Stop development containers"
	@echo "  assets-build   - Build React assets for production"
	@echo "  assets-dev     - Start Vite dev server only"
	@echo ""
	@echo "Laravel Commands:"
	@echo "  shell          - Access app container bash"
	@echo "  test           - Run PHP tests"
	@echo "  migrate        - Run database migrations"
	@echo "  seed           - Seed the database"
	@echo "  fresh          - Fresh migration with seed"
	@echo ""
	@echo "Utility Commands:"
	@echo "  logs           - View container logs"
	@echo "  install        - Install dependencies"
	@echo "  cache-clear    - Clear all caches"

# Docker commands
build: ## Build Docker containers
	@echo "🔨 Building Docker containers..."
	@docker-compose build

up: ## Start Docker containers
	@echo "⬆️ Starting production containers..."
	@docker-compose up -d

down: ## Stop Docker containers
	@echo "⬇️ Stopping all containers..."
	@docker-compose down
	@docker-compose -f docker-compose.yml -f docker-compose.dev.yml down 2>/dev/null || true

restart: ## Restart Docker containers
	docker-compose restart

logs: ## Show container logs
	@docker-compose logs -f

shell: ## Access application container shell
	@docker-compose exec app bash

# Laravel commands
migrate: ## Run database migrations
	@echo "📊 Running database migrations..."
	@docker-compose exec app php artisan migrate

migrate-fresh: ## Fresh database migration with seeding
	docker-compose exec app php artisan migrate:fresh --seed

seed: ## Seed the database
	@echo "🌱 Seeding database..."
	@docker-compose exec app php artisan db:seed

test: ## Run tests
	@echo "🧪 Running PHP tests..."
	@docker-compose exec app php artisan test

test-feature: ## Run feature tests only
	docker-compose exec app php artisan test --testsuite=Feature

test-unit: ## Run unit tests only
	docker-compose exec app php artisan test --testsuite=Unit

# Application commands
install: ## Complete installation and setup
	@echo "📦 Installing dependencies..."
	@docker-compose exec app composer install
	@docker-compose exec app npm install

fresh: ## Fresh installation (rebuild everything)
	make down
	docker-compose build --no-cache
	make up
	make migrate-fresh
	make cache

cache: ## Cache Laravel configuration
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache

cache-clear: ## Clear all caches
	@echo "🧹 Clearing all caches..."
	@docker-compose exec app php artisan cache:clear
	@docker-compose exec app php artisan config:clear
	@docker-compose exec app php artisan route:clear
	@docker-compose exec app php artisan view:clear

queue-work: ## Start queue worker
	docker-compose exec app php artisan queue:work

# Maintenance commands
composer-install: ## Install Composer dependencies
	docker-compose exec app composer install

composer-update: ## Update Composer dependencies
	docker-compose exec app composer update

npm-install: ## Install NPM dependencies
	docker-compose exec app npm install

npm-build: ## Build assets
	docker-compose exec app npm run build

# Cleanup commands
clean: ## Clean up Docker resources
	docker system prune -f
	docker volume prune -f

clean-all: ## Clean up all Docker resources (including images)
	docker system prune -af
	docker volume prune -f

# Development commands
dev:
	@echo "🚀 Starting development mode with hot reloading..."
	@docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d
	@echo "✅ Development servers started!"
	@echo "🌐 Laravel app: http://localhost:8000"
	@echo "⚡ Vite dev server: http://localhost:5173"

dev-down:
	@echo "⬇️ Stopping development containers..."
	@docker-compose -f docker-compose.yml -f docker-compose.dev.yml down

assets-build:
	@echo "🔨 Building React assets for production..."
	@docker-compose exec app npm run build

assets-dev:
	@echo "⚡ Starting Vite dev server..."
	@docker-compose -f docker-compose.yml -f docker-compose.dev.yml up vite

# Database commands
db-shell: ## Access database shell
	docker-compose exec database mysql -u izam_user -p izam_ecommerce

db-backup: ## Backup database
	docker-compose exec database mysqldump -u izam_user -p izam_ecommerce > backup_$(shell date +%Y%m%d_%H%M%S).sql

# Monitoring
status: ## Show container status
	docker-compose ps

stats: ## Show container resource usage
	docker stats

# Setup commands
setup:
	@echo "🚀 Running initial Docker setup..."
	@chmod +x docker-setup.sh
	@./docker-setup.sh

prod: ## Start production environment
	docker-compose -f docker-compose.prod.yml up -d
