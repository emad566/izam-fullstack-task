# IZAM E-commerce Fullstack Application

A comprehensive React + Laravel fullstack e-commerce application featuring a modern React frontend with TypeScript and a robust Laravel RESTful API backend. Built with Docker support, advanced authentication, real-time cart management, and production-ready deployment.

## 📋 Table of Contents

- [🚀 Introduction](#-introduction)
- [🏗️ Architecture Overview](#️-architecture-overview)
- [✨ Features](#-features)
- [⚡ Quick Start](#-quick-start)
- [🛠️ Setup Instructions](#️-setup-instructions)
- [📱 Frontend Features](#-frontend-features)
- [🔐 Backend API](#-backend-api)
- [🐳 Docker Development](#-docker-development)
- [📚 API Documentation](#-api-documentation)
- [🧪 Testing](#-testing)
- [🚀 Deployment](#-deployment)
- [📞 Support & Contact](#-support--contact)

## 🚀 Introduction

The IZAM E-commerce application is a modern fullstack solution combining a **React TypeScript frontend** with a **Laravel PHP backend**. It demonstrates professional-grade development practices, responsive design, and scalable architecture patterns.

🌟 **Live Demo**: Experience the application at [http://localhost:3010](http://localhost:3010) after setup

### 🏗️ Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                    IZAM E-commerce Stack                    │
├─────────────────────────────────────────────────────────────┤
│ 🎨 Frontend (React + TypeScript)                           │
│ ├─ React 19 + TypeScript                                   │
│ ├─ Vite Build System                                       │
│ ├─ TailwindCSS + Shadcn/ui                                 │
│ ├─ React Query (Data Fetching)                             │
│ ├─ React Router (Navigation)                               │
│ └─ Local Storage (Cart Management)                         │
├─────────────────────────────────────────────────────────────┤
│ 🔗 Communication Layer                                     │
│ ├─ RESTful API (JSON)                                      │
│ ├─ Axios HTTP Client                                       │
│ └─ Real-time State Sync                                    │
├─────────────────────────────────────────────────────────────┤
│ ⚙️ Backend (Laravel + PHP)                                 │
│ ├─ Laravel 10 + PHP 8.2                                   │
│ ├─ Multi-Guard Authentication                              │
│ ├─ Custom Design Patterns                                  │
│ ├─ Event-Driven Architecture                               │
│ └─ Advanced Caching (Redis)                                │
├─────────────────────────────────────────────────────────────┤
│ 🗄️ Data Layer                                              │
│ ├─ MySQL Database                                          │
│ ├─ Redis Cache                                             │
│ └─ File Storage (Images)                                   │
├─────────────────────────────────────────────────────────────┤
│ 🐳 Infrastructure                                          │
│ ├─ Docker Containers                                       │
│ ├─ Hot Reloading (Development)                             │
│ └─ Production Optimization                                  │
└─────────────────────────────────────────────────────────────┘
```

### Key Technology Stack

#### Frontend Technologies
- **React 19** - Latest React with concurrent features
- **TypeScript** - Type-safe development
- **Vite** - Fast build tool with HMR
- **TailwindCSS** - Utility-first CSS framework
- **Shadcn/ui** - Modern component library
- **React Query** - Server state management
- **React Router v7** - Client-side routing
- **Nuqs** - URL state management
- **Axios** - HTTP client for API calls

#### Backend Technologies  
- **Laravel 10** - Modern PHP framework
- **PHP 8.2** - Latest PHP with performance improvements
- **MySQL 8.0** - Relational database
- **Redis** - Caching and session storage
- **Sanctum** - API authentication
- **Spatie Media Library** - Image management

#### Development Tools
- **Docker** - Containerized development
- **Docker Compose** - Multi-service orchestration
- **Makefile** - Development workflow automation
- **PHPUnit** - Backend testing
- **ESLint** - Frontend code quality

## ✨ Features

### 🎨 Frontend Features
- **🛍️ Product Catalog** - Grid view with filtering and search
- **🔍 Advanced Search** - Real-time search with debouncing
- **🎛️ Smart Filters** - Category and price range filtering
- **📱 Responsive Design** - Mobile-first approach
- **🛒 Real-time Cart** - LocalStorage-based cart with sync
- **📄 Pagination** - Efficient data loading
- **🎭 Modal System** - Product details and filters
- **💳 Checkout Process** - Complete order flow
- **🔐 Authentication** - Login/logout functionality
- **🎨 Modern UI** - Clean, professional design
- **⚡ Hot Reloading** - Development-time updates

### ⚙️ Backend Features
- **🔐 Multi-Guard Auth** - Separate user/admin authentication
- **📦 Product Management** - CRUD operations with media
- **🛒 Order Processing** - Complete order lifecycle
- **🏷️ Category System** - Hierarchical organization
- **📊 Advanced Filtering** - Dynamic query building
- **⚡ Redis Caching** - Performance optimization
- **📧 Event System** - Order notifications
- **🛡️ Security** - Input validation, XSS protection
- **📝 Comprehensive Logging** - Request/error tracking
- **🧪 Extensive Testing** - 109+ test cases

### 🔄 Integration Features
- **🔗 RESTful API** - Clean API design
- **🔄 Real-time Sync** - Cart state synchronization
- **📱 Mobile Optimized** - Touch-friendly interactions
- **🚀 Performance** - Optimized loading and caching
- **🔒 Security** - CORS, CSRF protection
- **🌐 Localization Ready** - Multi-language support

## ⚡ Quick Start

Get the full application running in under 5 minutes:

```bash
# 1. Clone the repository
git clone https://github.com/emad566/izam-fullstack-task.git
cd izam-fullstack-task

# 2. Run automated setup
chmod +x docker-setup.sh
./docker-setup.sh

# 3. Access the application
open http://localhost:3010
```

**🎯 Access Points:**
- **Frontend Application**: `http://localhost:3010`
- **API Endpoint**: `http://localhost:3010/api`
- **Vite Dev Server**: `http://localhost:3011` (development mode)
- **phpMyAdmin**: `http://localhost:3013`
- **Database**: `localhost:3012`

## 🛠️ Setup Instructions

### Docker Setup (Recommended)

#### Prerequisites
- Docker 20.0+
- Docker Compose 2.0+
- 4GB+ RAM
- 10GB+ free disk space

#### Production Mode
```bash
# Clone and setup
git clone <repository-url>
cd izam-fullstack-task

# Automated setup
chmod +x docker-setup.sh
./docker-setup.sh

# Access application
open http://localhost:3010
```

#### Development Mode (with Hot Reloading)
```bash
# Start development servers with React hot reloading
make dev

# Or manually:
docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d

# Access points:
# Laravel app: http://localhost:3010
# Vite dev server: http://localhost:3011
```

#### Available Make Commands
```bash
# Setup and build
make setup          # Initial Docker setup
make build          # Build containers
make up             # Start production mode
make dev            # Start development mode with hot reloading

# Development
make dev-down       # Stop development containers
make assets-build   # Build React assets for production
make assets-dev     # Start Vite dev server only

# Laravel utilities
make shell          # Access app container
make test           # Run PHP tests
make migrate        # Run migrations
make seed           # Seed database
make cache-clear    # Clear all caches

# Monitoring
make logs           # View container logs
make down           # Stop all containers
```

### Local Development Setup

#### Prerequisites
- PHP 8.2+
- Node.js 20+
- Composer 2.0+
- MySQL 8.0+
- Redis (optional)

#### Installation Steps
```bash
# 1. Install backend dependencies
composer install

# 2. Install frontend dependencies
npm install

# 3. Environment setup
cp .env.example .env
php artisan key:generate

# 4. Database setup
php artisan migrate --seed

# 5. Build frontend assets
npm run build

# 6. Start servers
# Terminal 1: Laravel
php artisan serve --port=3010

# Terminal 2: Vite (development)
npm run dev
```

## 📱 Frontend Features

### User Interface Components

#### Product Catalog
- **Grid Layout** - Responsive product grid (2 cols mobile, 3 cols desktop)
- **Product Cards** - Image, title, category, price, stock, add to cart
- **Hover Effects** - Smooth transitions and visual feedback
- **Clickable Elements** - Product images and titles open details modal

#### Search & Filtering
- **Search Bar** - Real-time search with 500ms debouncing
- **Category Filters** - Dynamic category loading from API
- **Price Range** - Dual-handle slider for min/max price
- **URL Persistence** - Filters and search persist in URL

#### Shopping Cart
- **Local Storage** - Client-side cart persistence
- **Real-time Updates** - Instant quantity changes
- **Order Summary** - Subtotal, shipping, tax calculation
- **Stock Validation** - Prevents over-ordering
- **Remove Items** - One-click item removal

#### Responsive Design
- **Mobile First** - Optimized for mobile devices
- **Breakpoint System** - Tailored layouts for different screen sizes
- **Touch Friendly** - Appropriate touch targets and gestures
- **Modal System** - Full-screen modals on mobile

#### State Management
```typescript
// URL State (nuqs)
const [searchQuery, setSearchQuery] = useQueryState('q')
const [filters, setFilters] = useQueryStates({
  min_price: parseAsInteger,
  max_price: parseAsInteger,
  category_ids: parseAsArrayOf(parseAsInteger)
})

// Server State (React Query)
const { data: products } = useQuery({
  queryKey: ['products', searchParams],
  queryFn: () => fetchProducts(searchParams)
})

// Local State (LocalStorage + Custom Events)
const cart = useLocalStorage('cart', [])
```

### Modern Development Patterns

#### Component Architecture
```
resources/js/
├── components/           # Reusable UI components
│   ├── ui/              # Base UI components (shadcn/ui)
│   └── common/          # App-specific components
├── pages/               # Page components and features
│   ├── auth/           # Authentication pages
│   ├── checkout/       # Checkout process
│   └── components/     # Page-specific components
├── hooks/              # Custom React hooks
├── lib/                # Utility libraries
├── services/           # API service layer
└── utils/              # Helper functions
```

#### TypeScript Integration
- **Strict Type Safety** - Complete type coverage
- **API Type Definitions** - Shared types for API responses
- **Component Props** - Fully typed component interfaces
- **Custom Hooks** - Type-safe custom hooks

## 🔐 Backend API

### Authentication System

#### Multi-Guard Architecture
```php
// Separate authentication for users and admins
'guards' => [
    'web' => ['driver' => 'session', 'provider' => 'users'],
    'admin' => ['driver' => 'sanctum', 'provider' => 'admins'],
    'api' => ['driver' => 'sanctum', 'provider' => 'users'],
]
```

#### JWT Token Management
- **Secure Token Generation** - Laravel Sanctum
- **Automatic Expiration** - Configurable token lifetimes
- **Role-based Access** - Different permissions for users/admins
- **Token Refresh** - Seamless token renewal

### API Endpoints

#### Guest Routes (Public)
```http
GET    /api/guest/products           # List products with filtering
GET    /api/guest/products/{id}      # Single product details
GET    /api/guest/categories         # List categories
```

#### User Routes (Authenticated)
```http
POST   /api/auth/register           # User registration
POST   /api/auth/login              # User login
POST   /api/auth/logout             # User logout
GET    /api/user/profile            # User profile
POST   /api/user/orders             # Create order
GET    /api/user/orders             # List user orders
```

#### Admin Routes (Admin Only)
```http
POST   /api/admin/auth/login        # Admin login
GET    /api/admin/products          # Manage products
POST   /api/admin/products          # Create product
PUT    /api/admin/products/{id}     # Update product
DELETE /api/admin/products/{id}     # Delete product
GET    /api/admin/orders            # All orders
```

### Custom Design Patterns

#### BaseController Pattern
```php
class BaseController extends Controller
{
    protected $model;
    protected $resource;
    
    public function __construct($model = null)
    {
        $this->model = $model ?? $this->model;
        $this->resource = $this->resource ?? $this->getResourceClass();
    }
    
    protected function sendResponse($success, $data, $message, $helpers = null, $status = 200)
    {
        return response()->json([
            'status' => $success,
            'data' => $data,
            'message' => $message,
            'helpers' => $helpers
        ], $status);
    }
}
```

#### Controller Traits System
- **IndexTrait** - Standardized listing with filtering
- **ShowTrait** - Single resource retrieval
- **EditTrait** - Resource updates
- **DestroyTrait** - Safe deletion
- **ToggleActiveTrait** - Status management

#### Event-Driven Architecture
```php
// Order placed event
event(new OrderPlaced($order));

// Listener for email notifications
class SendOrderNotificationToAdmin
{
    public function handle(OrderPlaced $event)
    {
        Mail::to(config('mail.admin_email'))
            ->send(new OrderPlacedNotification($event->order));
    }
}
```

## 🐳 Docker Development

### Container Architecture
```yaml
services:
  app:          # Laravel application with PHP-FPM + Nginx
  database:     # MySQL 8.0 database
  redis:        # Redis for caching and sessions
  phpmyadmin:   # Database management interface
  queue:        # Laravel queue worker
  scheduler:    # Laravel task scheduler
  vite:         # Vite development server (dev profile)
```

### Development Workflow

#### Hot Reloading Setup
```bash
# Start development environment
make dev

# This starts:
# - Laravel app with PHP-FPM + Nginx (port 8000)
# - Vite dev server with HMR (port 5173)
# - MySQL database (port 3307)
# - Redis cache (port 6379)
# - phpMyAdmin (port 8081)
```

#### Asset Building Process
```dockerfile
# Production asset building in Dockerfile
RUN npm ci --omit=dev
RUN npm run build

# Development with hot reloading
# Vite serves assets directly with WebSocket HMR
```

#### Environment Variables
```bash
# Laravel configuration
APP_URL=http://localhost:3010
DB_HOST=database
REDIS_HOST=redis

# Vite configuration
VITE_APP_URL=http://localhost:3010
VITE_DEV_SERVER_URL=http://localhost:3011
```

## 📚 API Documentation

### Request/Response Format

#### Standard API Response
```json
{
  "status": true,
  "data": {
    "items": {
      "data": [...],
      "links": {...},
      "meta": {...}
    }
  },
  "message": "Success",
  "helpers": null,
  "response_code": 200
}
```

#### Error Response
```json
{
  "status": false,
  "data": null,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."]
  },
  "response_code": 422
}
```

### Advanced Filtering

#### Product Filtering
```http
GET /api/guest/products?q=shirt&min_price=10&max_price=100&category_ids[]=1&category_ids[]=2&page=1&per_page=9
```

#### Filter Parameters
- **q** - Search query (product name)
- **min_price** - Minimum price filter
- **max_price** - Maximum price filter
- **category_ids[]** - Array of category IDs
- **page** - Page number for pagination
- **per_page** - Items per page (default: 15)

### Authentication Examples

#### User Registration
```bash
curl -X POST http://localhost:3010/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

#### User Login
```bash
curl -X POST http://localhost:3010/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

#### Create Order
```bash
curl -X POST http://localhost:3010/api/user/orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "products": [
      {"product_id": 1, "quantity": 2},
      {"product_id": 5, "quantity": 1}
    ]
  }'
```

## 🧪 Testing

### Test Coverage Overview
- **Total Tests**: 109+ test cases
- **Test Assertions**: 752+ assertions
- **Coverage Areas**: Authentication, CRUD operations, Security, Filtering
- **Test Types**: Feature tests, Unit tests, API tests

### Frontend Testing Strategy
```typescript
// Component testing patterns
describe('ProductCard', () => {
  it('should display product information correctly', () => {
    // Test component rendering
  })
  
  it('should handle add to cart functionality', () => {
    // Test cart interactions
  })
})

// API integration testing
describe('Product API', () => {
  it('should fetch products with filters', async () => {
    // Test API calls and responses
  })
})
```

### Backend Testing Examples
```php
// Feature test example
public function test_user_can_create_order()
{
    $user = User::factory()->create();
    $products = Product::factory()->count(2)->create();
    
    $response = $this->actingAs($user, 'api')
        ->postJson('/api/user/orders', [
            'products' => [
                ['product_id' => $products[0]->id, 'quantity' => 2],
                ['product_id' => $products[1]->id, 'quantity' => 1],
            ]
        ]);
    
    $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['order']]);
}
```

### Running Tests
```bash
# Run all backend tests
make test
# or
docker-compose exec izam php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage

# Frontend tests (if configured)
npm run test
npm run test:coverage
```

## 🚀 Deployment

### Docker Production Deployment

#### Build Production Images
```bash
# Build optimized production containers
docker-compose -f docker-compose.yml build --no-cache

# Start production environment
docker-compose up -d

# Verify deployment
curl http://localhost:3010/api/guest/products
```

#### Production Optimizations
- **Asset Optimization** - Minified CSS/JS bundles
- **PHP Opcache** - Enabled for performance
- **Redis Caching** - Production cache configuration
- **Image Optimization** - Compressed Docker layers
- **Security Headers** - Production security settings

### Manual Deployment

#### Server Requirements
- **PHP**: 8.2+ with extensions (PDO, mbstring, XML, etc.)
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Web Server**: Nginx or Apache with mod_rewrite
- **Cache**: Redis 6.0+ (recommended)
- **Node.js**: 20+ for asset building

#### Deployment Steps
```bash
# 1. Clone and install dependencies
git clone <repository-url>
cd izam-fullstack-task
composer install --no-dev --optimize-autoloader
npm ci --omit=dev

# 2. Environment configuration
cp .env.example .env
# Configure .env with production values

# 3. Build assets
npm run build

# 4. Laravel setup
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Set permissions
chown -R www-data:www-data storage bootstrap/cache
```

### Environment Configuration

#### Production Environment Variables
```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=izam_ecommerce
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=localhost
REDIS_PASSWORD=your_redis_password

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-email-password
```

## 📞 Support & Contact

### Development Team
- **Core Development**: [Emadeldeen Soliman](https://github.com/emad566)
- **Frontend Architecture**: React + TypeScript implementation
- **Backend API**: Laravel custom design patterns
- **Documentation**: Comprehensive development guide

### Repository
- **GitHub**: [https://github.com/emad566/izam-fullstack-task](https://github.com/emad566/izam-fullstack-task)
- **Issues**: Report bugs and feature requests
- **Wiki**: Additional documentation and guides

### Getting Help
1. **Check Documentation** - Review this README and code comments
2. **Search Issues** - Look for existing solutions
3. **Create Issue** - Provide detailed reproduction steps
4. **Community** - Join discussions and share improvements

---

Built with ❤️ using React, TypeScript, Laravel, and Docker

© 2025 IZAM E-commerce. All rights reserved.
