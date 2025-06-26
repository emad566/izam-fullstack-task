# IZAM E-commerce Docker Makefile

.PHONY: help build up down restart logs shell test migrate seed fresh install clean

# Default target
help: ## Show this help message
	@echo "IZAM E-commerce Docker Commands"
	@echo "================================"
	@awk 'BEGIN {FS = ":.*##"} /^[a-zA-Z_-]+:.*##/ { printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2 }' $(MAKEFILE_LIST)

# Docker commands
build: ## Build Docker containers
	docker-compose build

up: ## Start Docker containers
	docker-compose up -d

down: ## Stop Docker containers
	docker-compose down

restart: ## Restart Docker containers
	docker-compose restart

logs: ## Show container logs
	docker-compose logs -f

shell: ## Access application container shell
	docker-compose exec app bash

# Laravel commands
migrate: ## Run database migrations
	docker-compose exec app php artisan migrate

migrate-fresh: ## Fresh database migration with seeding
	docker-compose exec app php artisan migrate:fresh --seed

seed: ## Seed the database
	docker-compose exec app php artisan db:seed

test: ## Run tests
	docker-compose exec app php artisan test

test-feature: ## Run feature tests only
	docker-compose exec app php artisan test --testsuite=Feature

test-unit: ## Run unit tests only
	docker-compose exec app php artisan test --testsuite=Unit

# Application commands
install: ## Complete installation and setup
	./docker-setup.sh

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
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

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
dev: ## Start development environment
	make up
	make logs

prod: ## Start production environment
	docker-compose -f docker-compose.prod.yml up -d

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
