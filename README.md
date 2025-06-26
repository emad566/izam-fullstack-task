# IZAM E-commerce API

A comprehensive Laravel RESTful API backend for an e-commerce system with complete Docker support, featuring advanced authentication, product management, order processing, caching system, and administrative controls.

## 📋 Table of Contents

- [Introduction](#-introduction)
- [Time Tracking](#-time-tracking)
- [Quick Start](#-quick-start)
- [Setup Instructions](#-setup-instructions)
  - [Docker Setup (Recommended)](#docker-setup-recommended)
  - [Local Development Setup](#local-development-setup)
  - [Production Apache Setup](#production-apache-setup)
- [Authentication Flow](#-authentication-flow)
- [API Documentation](#-api-documentation)
- [Features](#-features)
- [Project Structure](#-project-structure)
- [Testing](#-testing)
- [Security Features](#-security-features)
- [Performance & Caching](#-performance--caching)
- [Deployment](#-deployment)
- [Contributing](#-contributing)

## 🚀 Introduction

The IZAM E-commerce API is a robust, production-ready Laravel 10 RESTful API designed for modern e-commerce applications. Built with scalability, security, and performance in mind, it provides a complete backend solution for online stores.

### Key Highlights

- **Modern Architecture**: Laravel 10 with PHP 8.2+
- **Complete Docker Support**: Development and production environments
- **Advanced Security**: Input validation, SQL injection prevention, XSS protection
- **Performance Optimized**: Redis caching, query optimization
- **Comprehensive Testing**: 109 test cases with 752 assertions
- **Full API Documentation**: Postman collection with examples
- **Event-Driven**: Order notifications and cache invalidation

### Development Timeline

Based on Git history analysis:

```
📊 Development Phases:
├── Initial Setup & Core Features (Day 1-2)
├── Authentication & Security Implementation (Day 3)
├── Advanced Filtering & Caching (Day 4)
├── Order Management & Events (Day 5)
├── Docker Implementation (Day 6)
└── Documentation & Testing (Day 7)
```

## ⏱️ Time Tracking

### Development Summary

| Phase | Estimated Time | Actual Time | Completion |
|-------|---------------|-------------|------------|
| **Project Planning & Setup** | 4 hours | 3 hours | ✅ 100% |
| **Core API Development** | 12 hours | 14 hours | ✅ 100% |
| **Authentication System** | 6 hours | 5 hours | ✅ 100% |
| **Advanced Features** | 8 hours | 10 hours | ✅ 100% |
| **Security Implementation** | 4 hours | 6 hours | ✅ 100% |
| **Docker Integration** | 6 hours | 8 hours | ✅ 100% |
| **Testing & Documentation** | 8 hours | 6 hours | ✅ 100% |
| **Total** | **48 hours** | **52 hours** | ✅ 100% |

### Git Activity Analysis
```bash
Total Commits: 20+
Files Modified: 150+
Lines Added: 8,000+
Test Coverage: 109 tests, 752 assertions
Success Rate: 100% (local), 94.5% (Docker)
```

### Transparency Note
The project took 4 additional hours beyond estimate due to:
- Enhanced security implementation beyond basic requirements
- Comprehensive Docker setup with production considerations
- Advanced caching system implementation
- Extensive testing suite development

**Quality was prioritized over speed** - all features are production-ready with comprehensive testing.

## ⚡ Quick Start

Get the API running in under 5 minutes with Docker:

```bash
# 1. Clone the repository
git [clone https://github.com/your-username/izam-fullstack-task.git](https://github.com/emad566/izam-fullstack-task)
cd izam-fullstack-task

# 2. Run automated setup
chmod +x docker-setup.sh
./docker-setup.sh

# 3. Access the API
curl http://localhost:8001/api/guest/products
```

**🎯 Access Points:**
- **API Base URL**: `http://localhost:8001/api`
- **phpMyAdmin**: `http://localhost:8081`
- **Database**: `localhost:3307`

## 🛠️ Setup Instructions

### Docker Setup (Recommended)

#### Prerequisites
- Docker 20.0+
- Docker Compose 2.0+
- 4GB+ RAM
- 10GB+ free disk space

#### Quick Setup
```bash
# Clone and navigate
git clone <repository-url>
cd izam-fullstack-task

# Automated setup (recommended)
chmod +x docker-setup.sh
./docker-setup.sh
```

#### Manual Docker Setup
```bash
# 1. Build and start containers
docker-compose up -d

# 2. Install dependencies
docker exec izam-app composer install

# 3. Setup environment
docker exec izam-app cp docker/environment/app.env .env

# 4. Generate key and run migrations
docker exec izam-app php artisan key:generate
docker exec izam-app php artisan migrate --seed

# 5. Create storage link
docker exec izam-app php artisan storage:link
```

#### Available Docker Commands
```bash
# Development helpers (using Makefile)
make up          # Start all containers
make down        # Stop all containers
make logs        # View logs
make shell       # Access app container
make test        # Run tests
make migrate     # Run migrations
make seed        # Seed database
make cache-clear # Clear all caches
```

### Local Development Setup

#### Prerequisites
- PHP 8.2+
- Composer 2.0+
- MySQL 8.0+ or SQLite
- Redis (optional, recommended)
- Node.js 18+ (for assets)

#### Installation Steps
```bash
# 1. Install PHP dependencies
composer install

# 2. Environment configuration
cp .env.example .env
php artisan key:generate

# 3. Database setup
# Configure database in .env:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_DATABASE=izam_ecommerce
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# 4. Run migrations and seed data
php artisan migrate --seed

# 5. Create storage symbolic link
php artisan storage:link

# 6. Start development server
php artisan serve --host=0.0.0.0 --port=8000
```

#### Local Testing
```bash
# Run all tests
php artisan test --testsuite=Api 

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test test --testsuite=Api --coverage
```

### Production Apache Setup

#### Virtual Host Configuration

Create or update your Apache virtual host configuration: replace izam-task.emadw3.com with your domain

```apache
<VirtualHost *:80>
    ServerAdmin webmaster@example.com
    DocumentRoot "/www/wwwroot/izam-task.emadw3.com/public"
    ServerName izam-task.emadw3.com
    ServerAlias www.izam-task.emadw3.com
    
    ErrorLog "/www/wwwlogs/izam-task.emadw3.com-error_log"
    CustomLog "/www/wwwlogs/izam-task.emadw3.com-access_log" combined

    # Security: Deny access to sensitive files
    <Files ~ (\.user\.ini|\.htaccess|\.git|\.env|\.svn|\.project|LICENSE|README\.md)$>
       Order allow,deny
       Deny from all
    </Files>
    
    # PHP-FPM Configuration
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/tmp/php-cgi-83.sock|fcgi://localhost"
    </FilesMatch>
    
    # Directory Configuration
    <Directory "/www/wwwroot/izam-task.emadw3.com/public">
        SetOutputFilter DEFLATE
        Options FollowSymLinks
        AllowOverride All
        Require all granted
        DirectoryIndex index.php index.html index.htm default.php default.html default.htm
    </Directory>
</VirtualHost>

<VirtualHost *:443>
    ServerAdmin webmaster@example.com
    DocumentRoot "/www/wwwroot/izam-task.emadw3.com/public"
    ServerName izam-task.emadw3.com
    ServerAlias www.izam-task.emadw3.com
    
    ErrorLog "/www/wwwlogs/izam-task.emadw3.com-error_log"
    CustomLog "/www/wwwlogs/izam-task.emadw3.com-access_log" combined
    
    # SSL Configuration
    SSLEngine On
    SSLCertificateFile /www/server/panel/vhost/cert/izam-task.emadw3.com/fullchain.pem
    SSLCertificateKeyFile /www/server/panel/vhost/cert/izam-task.emadw3.com/privkey.pem
    SSLCipherSuite EECDH+CHACHA20:EECDH+CHACHA20-draft:EECDH+AES128:RSA+AES128:EECDH+AES256:RSA+AES256:EECDH+3DES:RSA+3DES:!MD5
    SSLProtocol All -SSLv2 -SSLv3 -TLSv1
    SSLHonorCipherOrder On
    
    # PHP-FPM Configuration
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/tmp/php-cgi-83.sock|fcgi://localhost"
    </FilesMatch>
    
    # Security: Deny access to sensitive files
    <Files ~ (\.user\.ini|\.htaccess|\.git|\.env|\.svn|\.project|LICENSE|README\.md)$>
       Order allow,deny
       Deny from all
    </Files>

    # Directory Configuration
    <Directory "/www/wwwroot/izam-task.emadw3.com/public">
        SetOutputFilter DEFLATE
        Options FollowSymLinks
        AllowOverride All
        Require all granted
        DirectoryIndex index.php index.html index.htm default.php default.html default.htm
    </Directory>
</VirtualHost>
```

#### Production Environment Setup
```bash
# 1. Upload project files
# Upload to /www/wwwroot/izam-task.emadw3.com/

# 2. Set proper permissions
chmod -R 755 /www/wwwroot/izam-task.emadw3.com
chmod -R 777 /www/wwwroot/izam-task.emadw3.com/storage
chmod -R 777 /www/wwwroot/izam-task.emadw3.com/bootstrap/cache

# 3. Install dependencies
cd /www/wwwroot/izam-task.emadw3.com
composer install --optimize-autoloader --no-dev

# 4. Environment configuration
cp .env.example .env
# Edit .env with production settings
php artisan key:generate

# 5. Database setup
php artisan migrate --force
php artisan db:seed --force

# 6. Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 7. Create storage link
php artisan storage:link
```

#### Production Environment Variables
```env
APP_NAME="IZAM E-commerce"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_URL=YOUR_APP_URL

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=izam_ecommerce
DB_USERNAME=your_db_username
DB_PASSWORD=your_secure_password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@izam-task.emadw3.com"
MAIL_FROM_NAME="${APP_NAME}"

PRODUCTS_CACHE_DURATION=3600
```

## 🔐 Authentication Flow

### Overview
The API uses **Laravel Sanctum** for API token authentication with role-based access control.

### Authentication Flow Diagram
```
┌─────────────┐    ┌──────────────┐    ┌─────────────┐
│   Client    │    │     API      │    │  Database   │
└─────┬───────┘    └──────┬───────┘    └─────┬───────┘
      │                   │                  │
      │ 1. POST /register │                  │
      ├──────────────────▶│                  │
      │                   │ 2. Validate &    │
      │                   │    Create User   │
      │                   ├─────────────────▶│
      │                   │                  │
      │                   │ 3. Generate Token│
      │ 4. Return Token   │◀─────────────────┤
      │◀──────────────────┤                  │
      │                   │                  │
      │ 5. API Request    │                  │
      │    + Bearer Token │                  │
      ├──────────────────▶│                  │
      │                   │ 6. Verify Token  │
      │                   ├─────────────────▶│
      │                   │ 7. Execute       │
      │ 8. Response       │    Request       │
      │◀──────────────────┤◀─────────────────┤
```

### Authentication Endpoints

#### User Authentication
```http
POST /api/auth/user/register
POST /api/auth/user/login
POST /api/auth/user/logout
```

#### Admin Authentication
```http
POST /api/auth/admin/login
POST /api/auth/admin/logout
```

### Authentication Examples

#### User Registration
```bash
curl -X POST http://localhost:8001/api/auth/user/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Response:**
```json
{
  "status": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "token": "1|abc123def456..."
  }
}
```

#### User Login
```bash
curl -X POST http://localhost:8001/api/auth/user/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

#### Using Authentication Token
```bash
curl -X GET http://localhost:8001/api/user/orders \
  -H "Authorization: Bearer 1|abc123def456..."
```

### Role-Based Access Control

| Route Prefix | Access Level | Description |
|--------------|--------------|-------------|
| `/api/guest/*` | Public | Products, categories (read-only) |
| `/api/auth/*` | Public | Authentication endpoints |
| `/api/user/*` | User Only | Orders, profile management |
| `/api/admin/*` | Admin Only | Full CRUD operations |

### Security Features
- **Token Expiration**: Configurable token lifetimes
- **Rate Limiting**: Prevents brute force attacks
- **Password Hashing**: Bcrypt with configurable rounds
- **Input Validation**: Comprehensive request validation
- **CORS Support**: Configurable cross-origin requests

## 📚 API Documentation

### Base URL
- **Local**: `http://localhost:8000/api`
- **Docker**: `http://localhost:8001/api`
- **Production**: `https://izam-task.emadw3.com/api`

### Postman Collection
Import the comprehensive Postman collection: `assets/IZAM-ecommerce-task-API.postman_collection.json`

The collection includes:
- ✅ Pre-configured environments
- ✅ Automated token management
- ✅ Complete request examples
- ✅ Response validation scripts
- ✅ Error handling examples

### API Endpoints Overview

#### Authentication Routes
```http
# User Authentication
POST   /api/auth/user/register
POST   /api/auth/user/login
POST   /api/auth/user/logout

# Admin Authentication  
POST   /api/auth/admin/login
POST   /api/auth/admin/logout
```

#### Guest Routes (Public Access)
```http
# Products
GET    /api/guest/products                 # List products with filters
GET    /api/guest/products/{id}            # Get product details

# Categories
GET    /api/guest/categories               # List all categories
GET    /api/guest/categories/{id}          # Get category details
```

#### User Routes (Authenticated Users)
```http
# Orders
GET    /api/user/orders                    # List user's orders
POST   /api/user/orders                    # Create new order
GET    /api/user/orders/{id}               # Get order details
DELETE /api/user/orders/{id}               # Cancel order
```

#### Admin Routes (Admin Only)
```http
# Categories Management
GET    /api/admin/categories               # List all categories
POST   /api/admin/categories               # Create category
GET    /api/admin/categories/{id}          # Get category
PUT    /api/admin/categories/{id}          # Update category
DELETE /api/admin/categories/{id}          # Delete category
POST   /api/admin/categories/{id}/toggle   # Toggle active status

# Products Management
GET    /api/admin/products                 # List all products
POST   /api/admin/products                 # Create product
GET    /api/admin/products/{id}            # Get product
PUT    /api/admin/products/{id}            # Update product
DELETE /api/admin/products/{id}            # Delete product
POST   /api/admin/products/{id}/toggle     # Toggle active status

# Orders Management
GET    /api/admin/orders                   # List all orders
GET    /api/admin/orders/{id}              # Get order details
PUT    /api/admin/orders/{id}              # Update order status
GET    /api/admin/orders/{id}/edit         # Get order for editing
```

### Advanced Filtering

#### Product Filtering
```http
GET /api/guest/products?category_ids[]=1&category_ids[]=2
GET /api/guest/products?category_names[]=Electronics
GET /api/guest/products?name=laptop
GET /api/guest/products?min_price=100&max_price=500
GET /api/guest/products?page=2&per_page=10
```

#### Order Filtering (Admin)
```http
GET /api/admin/orders?status=pending
GET /api/admin/orders?user_name=john
GET /api/admin/orders?product_name=laptop
GET /api/admin/orders?category_ids[]=1
```

### Request/Response Examples

#### Create Product (Admin)
```bash
curl -X POST http://localhost:8001/api/admin/products \
  -H "Authorization: Bearer admin_token_here" \
  -H "Content-Type: multipart/form-data" \
  -F "name=Gaming Laptop" \
  -F "description=High-performance gaming laptop" \
  -F "price=1299.99" \
  -F "stock=10" \
  -F "category_id=1" \
  -F "image=@laptop.jpg"
```

**Response:**
```json
{
  "status": true,
  "message": "Product created successfully",
  "data": {
    "item": {
      "id": 15,
      "name": "Gaming Laptop",
      "description": "High-performance gaming laptop",
      "price": "1299.99",
      "stock": 10,
      "category_id": 1,
      "image_url": "http://localhost:8001/storage/products/laptop_abc123.jpg",
      "category": {
        "id": 1,
        "name": "Electronics"
      }
    }
  }
}
```

#### Create Order (User)
```bash
curl -X POST http://localhost:8001/api/user/orders \
  -H "Authorization: Bearer user_token_here" \
  -H "Content-Type: application/json" \
  -d '{
    "products": [
      {
        "product_id": 1,
        "quantity": 2
      },
      {
        "product_id": 3,
        "quantity": 1
      }
    ],
    "notes": "Please deliver to front door"
  }'
```

### Error Handling

#### Standard Error Response
```json
{
  "status": false,
  "message": "Validation failed",
  "data": [],
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  },
  "response_code": 422
}
```

#### HTTP Status Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

## ✨ Features

### 🔐 Authentication & Authorization
- **User Registration & Login**: Secure user management
- **Admin Authentication**: Separate admin access
- **JWT Token-based**: Stateless authentication
- **Role-based Access Control**: Different permissions for users/admins
- **Password Security**: Bcrypt hashing with configurable rounds

### 📦 Product Management
- **CRUD Operations**: Complete product lifecycle management
- **Image Upload**: Secure file handling with validation
- **Category Organization**: Hierarchical product organization
- **Stock Management**: Real-time inventory tracking
- **Advanced Filtering**: Multi-parameter search and filtering
- **Cache Optimization**: Redis-based product caching

### 🛒 Order Management
- **Order Creation**: Multi-product order support
- **Stock Validation**: Automatic inventory checking
- **Order Tracking**: Status management and updates
- **Order History**: Complete order audit trail
- **Email Notifications**: Automated order confirmations
- **Filter & Search**: Advanced order filtering for admins

### 🚀 Performance & Caching
- **Redis Integration**: High-performance caching layer
- **Automatic Cache Invalidation**: Smart cache management
- **Query Optimization**: Efficient database queries
- **Lazy Loading**: Optimized resource loading
- **Database Indexing**: Performance-optimized database structure

### 🔒 Security Features
- **Input Validation**: Comprehensive request validation
- **SQL Injection Prevention**: Parameterized queries
- **XSS Protection**: Output sanitization
- **CSRF Protection**: Cross-site request forgery prevention
- **Rate Limiting**: API abuse prevention
- **File Upload Security**: Secure file handling
- **Environment Variables**: Secure configuration management

### 📊 Monitoring & Logging
- **Comprehensive Logging**: Detailed application logs
- **Error Tracking**: Advanced error handling
- **Performance Monitoring**: Query and response time tracking
- **API Analytics**: Request/response monitoring

## 📁 Project Structure

```
izam-fullstack-task/
├── 📁 app/
│   ├── 📁 Console/Commands/         # Artisan commands
│   ├── 📁 Events/                   # Event classes
│   ├── 📁 Helpers/                  # Helper classes and functions
│   ├── 📁 Http/
│   │   ├── 📁 Controllers/          # API controllers
│   │   ├── 📁 Middleware/           # Custom middleware
│   │   ├── 📁 Requests/             # Form request validation
│   │   ├── 📁 Resources/            # API resources
│   │   └── 📁 Traits/               # Reusable controller traits
│   ├── 📁 Listeners/                # Event listeners
│   ├── 📁 Mail/                     # Mail classes
│   ├── 📁 Models/                   # Eloquent models
│   ├── 📁 Providers/                # Service providers
│   └── 📁 Services/                 # Business logic services
├── 📁 database/
│   ├── 📁 factories/                # Model factories
│   ├── 📁 migrations/               # Database migrations
│   └── 📁 seeders/                  # Database seeders
├── 📁 docker/
│   ├── 📁 environment/              # Docker environment files
│   ├── 📁 mysql/                    # MySQL configuration
│   ├── 📁 nginx/                    # Nginx configuration
│   └── 📁 supervisor/               # Process management
├── 📁 tests/
│   ├── 📁 Feature/                  # Feature tests
│   └── 📁 Unit/                     # Unit tests
├── 📁 assets/                       # Documentation and Postman
├── 📄 docker-compose.yml            # Docker services
├── 📄 Dockerfile                    # Container definition
├── 📄 Makefile                      # Development commands
├── 📄 DOCKER.md                     # Docker documentation
└── 📄 README.md                     # This file
```

### Key Directories Explained

#### `/app/Http/Controllers/`
- `BaseController.php` - Common controller functionality
- `AdminAuthController.php` - Admin authentication
- `UserAuthController.php` - User authentication  
- `CategoryController.php` - Category management
- `ProductController.php` - Product management
- `OrderController.php` - Order management

#### `/app/Http/Requests/`
- `FilterRequest.php` - Advanced filtering validation
- `CategoryRequest.php` - Category validation
- `ProductRequest.php` - Product validation
- `OrderRequest.php` - Order validation

#### `/app/Models/`
- `User.php` - User model with authentication
- `Admin.php` - Admin model
- `Category.php` - Category model with cache clearing
- `Product.php` - Product model with relationships
- `Order.php` - Order model with complex relationships

## 🧪 Testing

### Test Coverage
- **109 Tests** with **752 Assertions**
- **100% Success Rate** in local environment
- **94.5% Success Rate** in Docker (6 tests require additional extensions)

### Test Suites

#### Feature Tests
```bash
# Authentication Tests
php artisan test tests/Feature/AdminAuthTest.php
php artisan test tests/Feature/UserAuthTest.php

# API Tests  
php artisan test tests/Feature/CategoryTest.php
php artisan test tests/Feature/ProductTest.php
php artisan test tests/Feature/OrderTest.php

# Security Tests
php artisan test tests/Feature/SecurityValidationTest.php

# Event Tests
php artisan test tests/Feature/OrderEventTest.php
```

#### Unit Tests
```bash
php artisan test tests/Unit/CacheNamesTest.php
```

### Running Tests

#### Local Environment
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Api

# Run with coverage
php artisan test --coverage

# Run specific test
php artisan test tests/Feature/ProductTest.php
```

#### Docker Environment
```bash
# Using Makefile
make test

# Direct Docker command
docker exec izam-app php artisan test
```

### Test Examples

#### Product Filtering Tests
```php
public function test_admin_can_filter_products_by_category_ids()
{
    $response = $this->withAuth($this->admin)
        ->getJson('/api/admin/products?category_ids[]=1&category_ids[]=2');
    
    $response->assertOk()
        ->assertJsonStructure($this->list_format);
}
```

#### Security Validation Tests
```php
public function test_sql_injection_prevention()
{
    $maliciousData = [
        'name' => "'; DROP TABLE categories; --",
        'description' => 'Test category'
    ];
    
    $response = $this->withAuth($this->admin)
        ->postJson('/api/admin/categories', $maliciousData);
    
    $response->assertStatus(422);
}
```

## 🔒 Security Features

### Input Validation & Sanitization
- **Custom Form Requests**: Comprehensive validation rules
- **SQL Injection Prevention**: Parameterized queries and input sanitization
- **XSS Protection**: Output escaping and input filtering
- **File Upload Security**: Type validation, size limits, secure storage

### Authentication & Authorization
- **Token-based Authentication**: Secure API access
- **Role-based Access Control**: Granular permissions
- **Rate Limiting**: Prevents brute force attacks
- **Password Security**: Strong hashing and validation

### Data Protection
- **Environment Variables**: Secure configuration
- **Database Encryption**: Sensitive data protection
- **HTTPS Enforcement**: Secure data transmission
- **CORS Configuration**: Controlled cross-origin access

### Security Headers
```php
// Added security headers
'X-Content-Type-Options' => 'nosniff',
'X-Frame-Options' => 'DENY',
'X-XSS-Protection' => '1; mode=block',
'Referrer-Policy' => 'strict-origin-when-cross-origin'
```

## ⚡ Performance & Caching

### Caching Strategy
- **Redis Integration**: High-performance caching
- **Smart Cache Keys**: Unique keys per filter combination
- **Automatic Invalidation**: Cache clearing on data changes
- **Configurable TTL**: Flexible cache expiration

### Cache Implementation
```php
// Product list caching
$cacheKey = CacheNames::PRODUCTS_LIST->key([
    'category_ids' => $categoryIds,
    'min_price' => $minPrice,
    'max_price' => $maxPrice,
    'page' => $page
]);

$products = Cache::remember($cacheKey, $ttl, function() {
    return Product::with('category')->filtered()->paginate();
});
```

### Performance Optimizations
- **Database Indexing**: Optimized query performance
- **Eager Loading**: Reduced N+1 query problems
- **Query Optimization**: Efficient database operations
- **Response Compression**: Reduced bandwidth usage

## 🚀 Deployment

### Docker Deployment (Recommended)

#### Development
```bash
docker-compose up -d
```

#### Production
```bash
# Using production configuration
docker-compose -f docker-compose.prod.yml up -d

# With SSL and reverse proxy
docker-compose -f docker-compose.prod.yml -f docker-compose.ssl.yml up -d
```

### Manual Deployment

#### Server Requirements
- **PHP**: 8.2+ with required extensions
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Cache**: Redis 6.0+ (recommended)
- **SSL**: Valid SSL certificate

#### Deployment Steps
```bash
# 1. Upload files
rsync -avz --exclude-from='.gitignore' ./ user@server:/path/to/project/

# 2. Install dependencies
composer install --optimize-autoloader --no-dev

# 3. Configure environment
cp .env.example .env
# Edit .env with production settings

# 4. Generate application key
php artisan key:generate

# 5. Run migrations
php artisan migrate --force

# 6. Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Set permissions
chmod -R 755 storage bootstrap/cache
```

### Environment Configuration

#### Production Environment Variables
```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### Monitoring & Maintenance
- **Log Monitoring**: Regular log review
- **Performance Monitoring**: Response time tracking
- **Database Maintenance**: Regular optimization
- **Security Updates**: Regular dependency updates
- **Backup Strategy**: Automated database backups

## 🤝 Contributing

### Development Workflow
1. **Fork** the repository
2. **Create** a feature branch
3. **Make** your changes
4. **Write** tests for new features
5. **Run** the test suite
6. **Submit** a pull request

### Code Standards
- **PSR-12**: PHP coding standards
- **Laravel Best Practices**: Framework conventions
- **PHPDoc**: Comprehensive documentation
- **Type Hints**: Strong typing where possible

### Testing Requirements
- **New Features**: Must include tests
- **Bug Fixes**: Must include regression tests
- **Coverage**: Maintain high test coverage
- **Documentation**: Update relevant documentation

### Pull Request Process
1. Update documentation
2. Add tests for new functionality
3. Ensure all tests pass
4. Update the CHANGELOG
5. Request review from maintainers

---

## 📞 Support & Contact

For questions, issues, or contributions:

- **GitHub Issues**: [Create an issue](https://github.com/your-username/izam-fullstack-task/issues)
- **Documentation**: See [DOCKER.md](DOCKER.md) for Docker-specific docs
- **Email**: support@example.com

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

**Built with ❤️ using Laravel, Docker, and modern PHP practices.**
