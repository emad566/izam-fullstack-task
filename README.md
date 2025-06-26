# IZAM E-commerce API

A comprehensive Laravel RESTful API backend for an e-commerce system with Docker support, featuring user authentication, product management, order processing, and administrative controls.

## 🚀 Quick Start with Docker

The fastest way to get started is using Docker:

```bash
# Clone the repository
git clone <repository-url>
cd izam-fullstack-task

# Run the automated setup
chmod +x docker-setup.sh
./docker-setup.sh
```

**Access Points:**
- API: http://localhost:8001
- phpMyAdmin: http://localhost:8080
- Database: localhost:3306

For detailed Docker documentation, see [DOCKER.md](DOCKER.md).

## 📋 Features

- **Authentication System**
  - User registration and login
  - Admin authentication
  - JWT token-based authentication
  - Role-based access control

- **Product Management**
  - CRUD operations for products
  - Category-based organization
  - Image upload support
  - Stock management
  - Advanced filtering (price, category, name)

- **Order Management**
  - Order creation and tracking
  - Order status management
  - Stock validation
  - Email notifications

- **Caching System**
  - Redis-based caching
  - Automatic cache invalidation
  - Performance optimization

- **API Documentation**
  - Postman collection included
  - RESTful API design
  - Comprehensive endpoints

## 🛠️ Manual Installation

If you prefer manual installation without Docker:

### Prerequisites

- PHP 8.2+
- Composer
- MySQL 8.0+
- Redis (optional, for caching)
- Node.js 18+ (for asset compilation)

### Installation Steps

```bash
# 1. Install dependencies
composer install

# 2. Copy environment file
cp .env.example .env

# 3. Generate application key
php artisan key:generate

# 4. Configure database in .env file
# DB_HOST=127.0.0.1
# DB_DATABASE=izam_ecommerce
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# 5. Run migrations and seed database
php artisan migrate --seed

# 6. Create storage link
php artisan storage:link

# 7. Start development server
php artisan serve
```

## 🔧 Configuration

### Environment Variables

Key configuration options in `.env`:

```env
# Application
APP_NAME="IZAM E-commerce"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8001

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=izam_ecommerce
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Cache (Redis recommended)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## 🧪 Testing

Run the test suite:

```bash
# Using Docker
make test

# Manual installation
php artisan test
```

## 📚 API Documentation

The API includes comprehensive endpoints for:

- **Authentication**: `/api/auth/*`
- **Guest Access**: `/api/guest/*` (products, categories)
- **User Access**: `/api/user/*` (orders, profile)
- **Admin Access**: `/api/admin/*` (full management)

Import the Postman collection from `assets/IZAM-ecommerce-task-API.postman_collection.json` for detailed API documentation.

## 🐳 Docker Support

This project includes complete Docker support with:

- Multi-container setup (PHP, MySQL, Redis, Nginx)
- Development and production configurations
- Automated setup scripts
- Comprehensive documentation

See [DOCKER.md](DOCKER.md) for complete Docker documentation.

## 📁 Project Structure

```
├── app/                    # Laravel application code
├── database/              # Migrations, seeders, factories
├── docker/               # Docker configuration files
├── tests/                # Test suites
├── assets/               # Documentation and Postman collection
├── docker-compose.yml    # Docker services configuration
├── Dockerfile           # Application container definition
├── Makefile            # Development commands
└── DOCKER.md           # Docker documentation
```

## 🔒 Security Features

- Input validation and sanitization
- SQL injection prevention
- XSS protection
- CSRF protection
- Rate limiting
- Secure file uploads
- Authentication middleware

## 🚀 Deployment

### Using Docker (Recommended)

```bash
# Production deployment
docker-compose -f docker-compose.prod.yml up -d
```

### Manual Deployment

1. Set `APP_ENV=production` and `APP_DEBUG=false`
2. Configure proper database and Redis connections
3. Set up SSL certificates
4. Configure web server (Nginx/Apache)
5. Set up proper file permissions
6. Configure backup strategies

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests
5. Submit a pull request

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).
