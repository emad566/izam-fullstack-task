# 🐳 Docker Setup for IZAM E-commerce

This document provides comprehensive instructions for running the IZAM E-commerce application using Docker.

## 📋 Prerequisites

- Docker (version 20.10 or higher)
- Docker Compose (version 2.0 or higher)
- At least 4GB of available RAM
- At least 2GB of free disk space

## 🏗️ Architecture Overview

The Docker setup includes the following services:

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Laravel App   │    │     MySQL       │    │     Redis       │
│   (Nginx +      │◄──►│   Database      │    │   Cache/Queue   │
│    PHP-FPM)     │    │                 │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         ▼                       ▼                       ▼
    Port: 8001              Port: 3306              Port: 6379

┌─────────────────┐    ┌─────────────────┐
│   phpMyAdmin    │    │  Queue Worker   │
│  (DB Management)│    │   + Scheduler   │
└─────────────────┘    └─────────────────┘
         │
         ▼
    Port: 8080
```

### Services

1. **app** - Main Laravel application with Nginx and PHP-FPM
2. **database** - MySQL 8.0 database server
3. **redis** - Redis server for caching and sessions
4. **phpmyadmin** - Web-based MySQL administration tool
5. **queue** - Laravel queue worker for background jobs
6. **scheduler** - Laravel task scheduler for cron jobs

## 🚀 Quick Start

### Option 1: Automated Setup (Recommended)

```bash
# Make the setup script executable and run it
chmod +x docker-setup.sh
./docker-setup.sh
```

### Option 2: Manual Setup

```bash
# 1. Build and start containers
docker-compose up -d --build

# 2. Copy environment file
cp docker/environment/app.env .env

# 3. Generate application key
docker-compose exec app php artisan key:generate

# 4. Run migrations and seed database
docker-compose exec app php artisan migrate --seed

# 5. Cache configuration
docker-compose exec app php artisan config:cache
```

### Option 3: Using Makefile

```bash
# Install and setup everything
make install

# Or use individual commands
make help  # See all available commands
```

## 🌐 Access Points

After successful setup, you can access:

- **Application**: http://localhost:8001
- **phpMyAdmin**: http://localhost:8080
- **Database**: localhost:3306
- **Redis**: localhost:6379

### Default Credentials

- **Database**:
  - Host: `localhost:3306`
  - Database: `izam_ecommerce`
  - Username: `izam_user`
  - Password: `izam_password`

- **phpMyAdmin**:
  - Username: `izam_user`
  - Password: `izam_password`

## 📁 Directory Structure

```
docker/
├── nginx/
│   └── default.conf       # Nginx configuration
├── supervisor/
│   └── supervisord.conf   # Process management
├── mysql/
│   └── init.sql          # Database initialization
└── environment/
    └── app.env           # Docker environment variables
```

## 🛠️ Development Workflow

### Common Commands

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f

# Access application container
docker-compose exec app bash

# Run tests
docker-compose exec app php artisan test

# Run migrations
docker-compose exec app php artisan migrate

# Clear caches
docker-compose exec app php artisan cache:clear
```

### Using Makefile

```bash
make help           # Show all available commands
make up             # Start containers
make down           # Stop containers
make logs           # View logs
make shell          # Access app container
make test           # Run tests
make migrate        # Run migrations
make cache-clear    # Clear all caches
```

## 🔧 Configuration

### Environment Variables

The application uses environment variables for configuration. Key Docker-specific settings:

```env
# Database
DB_HOST=database
DB_DATABASE=izam_ecommerce
DB_USERNAME=izam_user
DB_PASSWORD=izam_password

# Redis
REDIS_HOST=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### Customizing Ports

To change default ports, modify `docker-compose.yml`:

```yaml
services:
  app:
    ports:
      - "8080:80"  # Change from 8001:80
  
  database:
    ports:
      - "3307:3306"  # Change from 3306:3306
```

## 📊 Monitoring and Debugging

### Container Status

```bash
# Check container status
docker-compose ps

# View resource usage
docker stats

# Check specific service logs
docker-compose logs app
docker-compose logs database
docker-compose logs redis
```

### Laravel Debugging

```bash
# View Laravel logs
docker-compose exec app tail -f storage/logs/laravel.log

# Check Laravel configuration
docker-compose exec app php artisan config:show

# Run Laravel health checks
docker-compose exec app php artisan tinker
```

## 🚨 Troubleshooting

### Common Issues

#### 1. Port Already in Use

```bash
# Error: Port 8001 is already allocated
# Solution: Stop other services or change port
docker-compose down
# Or edit docker-compose.yml to use different port
```

#### 2. Permission Issues

```bash
# Error: Permission denied
# Solution: Fix file permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

#### 3. Database Connection Failed

```bash
# Check database container
docker-compose logs database

# Restart database service
docker-compose restart database

# Verify database credentials in .env
```

#### 4. Application Key Not Set

```bash
# Generate new application key
docker-compose exec app php artisan key:generate
```

#### 5. Composer Dependencies

```bash
# Install/update dependencies
docker-compose exec app composer install
docker-compose exec app composer update
```

### Performance Optimization

#### 1. Cache Configuration

```bash
# Cache Laravel configuration for better performance
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

#### 2. Optimize Docker Build

```bash
# Use build cache
docker-compose build --parallel

# Build without cache (clean build)
docker-compose build --no-cache
```

## 🔄 Backup and Restore

### Database Backup

```bash
# Create backup
docker-compose exec database mysqldump -u izam_user -p izam_ecommerce > backup.sql

# Or using Makefile
make db-backup
```

### Database Restore

```bash
# Restore from backup
docker-compose exec -T database mysql -u izam_user -p izam_ecommerce < backup.sql
```

### Application Files

```bash
# Backup uploaded files
docker cp izam-app:/var/www/html/storage/app ./backup/storage

# Restore uploaded files
docker cp ./backup/storage izam-app:/var/www/html/storage/app
```

## 🚀 Production Deployment

### Production Configuration

1. Create `docker-compose.prod.yml` with production settings
2. Use proper SSL certificates
3. Set `APP_ENV=production` and `APP_DEBUG=false`
4. Use external database and Redis services
5. Configure proper backup strategies

### Security Considerations

1. Change default passwords
2. Use secrets management for sensitive data
3. Configure firewall rules
4. Regular security updates
5. Monitor application logs

## 📚 Additional Resources

- [Docker Documentation](https://docs.docker.com/)
- [Laravel Docker Best Practices](https://laravel.com/docs/deployment)
- [Docker Compose Reference](https://docs.docker.com/compose/)

## 🆘 Getting Help

If you encounter issues:

1. Check this documentation
2. Review container logs: `docker-compose logs`
3. Verify your Docker installation
4. Check system resources (RAM, disk space)
5. Restart Docker service if needed

For development questions, refer to the main project README. 
