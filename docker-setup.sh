#!/bin/bash

# IZAM E-commerce Docker Setup Script
echo "🚀 Setting up IZAM E-commerce with Docker..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    print_error "Docker is not installed. Please install Docker first."
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    print_error "Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

print_status "Docker and Docker Compose are installed ✅"

# Create necessary directories
print_status "Creating necessary directories..."
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

# Set proper permissions
print_status "Setting permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Copy environment file if it doesn't exist
if [ ! -f .env ]; then
    print_status "Creating .env file from Docker template..."
    if [ -f docker/environment/app.env ]; then
        cp docker/environment/app.env .env
        print_status ".env file created successfully"
    else
        print_warning ".env file not found. Please create one manually."
    fi
else
    print_warning ".env file already exists. Skipping creation."
fi

# Generate application key if not set
print_status "Checking application key..."
if grep -q "APP_KEY=$" .env 2>/dev/null || ! grep -q "APP_KEY=" .env 2>/dev/null; then
    print_status "Generating application key..."
    # We'll do this after containers are up
    GENERATE_KEY=true
else
    print_status "Application key already set ✅"
    GENERATE_KEY=false
fi

# Build and start containers
print_status "Building and starting Docker containers..."
docker-compose up -d --build

# Wait for services to be ready
print_status "Waiting for services to be ready..."
sleep 10

# Check if containers are running
if ! docker-compose ps | grep -q "Up"; then
    print_error "Some containers failed to start. Please check the logs with 'docker-compose logs'"
    exit 1
fi

print_status "Containers are running ✅"

# Generate application key if needed
if [ "$GENERATE_KEY" = true ]; then
    print_status "Generating application key..."
    docker-compose exec izam php artisan key:generate
fi

# Run composer install
print_status "Running composer install..."
docker-compose exec izam composer i


# Run database migrations
print_status "Running database migrations..."
docker-compose exec izam php artisan migrate --force

# Seed the database
print_status "Seeding the database..."
docker-compose exec izam php artisan db:seed --force

# Cache configuration
print_status "Caching configuration..."
docker-compose exec izam php artisan config:cache
docker-compose exec izam php artisan route:cache

# Create storage link
print_status "Creating storage link..."
docker-compose exec izam php artisan storage:link

# Run npm install
print_status "Running npm install..."
docker-compose exec izam npm install

# Run npm run build
print_status "Running npm run build..."
docker-compose exec izam npm run build

# Run tests
print_status "Running tests..."
docker-compose exec izam php artisan test --testsuite=Api

# Cache configuration
print_status "Caching configuration..."
docker-compose exec izam php artisan config:cache
docker-compose exec izam php artisan route:cache


# Build React assets
print_status "Building React assets..."
if docker-compose exec izam npm run build; then
    print_status "React assets built successfully ✅"
else
    print_warning "Failed to build React assets. You may need to build them manually."
fi

print_status "Setup completed successfully! 🎉"
echo ""
print_status "🌐 Application: http://localhost:3010"
print_status "🗄️  Database: localhost:3012"
print_status "📊 phpMyAdmin: http://localhost:3013"
print_status "🔴 Redis: localhost:3014"
echo ""
print_status "Development mode:"
print_status "🚀 Start with hot reloading: docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d"
print_status "🌐 Vite dev server: http://localhost:3011"
print_status "📱 Hot reloading enabled for React development"
echo ""
print_status "Useful commands:"
echo "  - Stop containers: docker-compose down"
echo "  - View logs: docker-compose logs -f"
echo "  - Access izam container: docker-compose exec izam bash"
echo "  - Run tests: docker-compose exec izam php artisan test"
echo "  - Build assets: docker-compose exec izam npm run build"
echo "  - Development mode: docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d"
echo "  - Stop dev mode: docker-compose -f docker-compose.yml -f docker-compose.dev.yml down"
echo ""
print_status "Happy coding! 🚀"
