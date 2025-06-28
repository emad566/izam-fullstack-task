# IZAM E-commerce Fullstack Application

A comprehensive React + Laravel fullstack e-commerce application featuring a modern React frontend with TypeScript and a robust Laravel RESTful API backend.

📚 **[Complete Documentation - Click Here for More Details](README-detailed.md)**

## 📋 Table of Contents

- [⏱️ Development Time Tracking](#️-development-time-tracking)
- [🚀 Live Demo](#-live-demo)
  - [🔑 Test Credentials](#-test-credentials)
  - [🗄️ Database Access](#️-database-access)
- [📚 API Endpoints](#-api-endpoints)
  - [🚀 Postman Collection Ready-to-Use](#-postman-collection-ready-to-use)
- [⚡ Quick Setup](#-quick-setup)
- [🐳 Docker Setup](#-docker-setup)
- [🔐 Authentication Flow](#-authentication-flow)
- [🚀 Running Backend & Frontend](#-running-backend--frontend)
- [🧪 Testing](#-testing)
- [📧 Order Email Event System](#-order-email-event-system)
- [🎯 Custom Trait Controller Design Pattern](#-custom-trait-controller-design-pattern)
- [🔐 Multi-Auth System](#-multi-auth-system)
- [🛡️ Security Features](#️-security-features)
- [👨‍💻 Development Attribution](#-development-attribution)
- [📞 Support & Contact](#-support--contact)

## ⏱️ Development Time Tracking

**Estimated Time:** 24 hours  
**Actual Time:** 28 hours total
- 18 hours for core development
- 10 additional hours for unit testing, code refactoring, documentation, and deployment

## 🚀 Live Demo

🌟 **Project Live**: [https://izam-task.emadw3.com](https://izam-task.emadw3.com) - no setup required!  
🌟 **API Live**: [https://izam-task.emadw3.com/api](https://izam-task.emadw3.com/api) - no setup required!

### 🔑 Test Credentials

**Admin Login:**
- Email: `admin@admin.com`
- Password: `12345678`

**User Login:**
- Email: `user@user.com`  
- Password: `12345678`

### 🗄️ Database Access

**phpMyAdmin:** [http://izam-task.emadw3.com:3013/](http://izam-task.emadw3.com:3013/) - no authentication required  
*Direct access to view database structure and data*

## 📚 API Endpoints

### 🚀 **Postman Collection Ready-to-Use**
Import the comprehensive Postman collection: **[`assets/IZAM-ecommerce-task-API.postman_collection.json`](assets/IZAM-ecommerce-task-API.postman_collection.json)**

**🎯 Collection Advantages:**
- ✅ **Auto Token Management** - Automatic user/admin token capture and usage
- ✅ **Multi-Environment Support** - Switch between Local, Docker, and Live environments
- ✅ **Smart Variable Management** - Auto-capture of productId, categoryId, orderId 
- ✅ **Complete CRUD Coverage** - All endpoints with real examples
- ✅ **Advanced Filtering Examples** - Pre-configured filter parameters
- ✅ **File Upload Support** - Ready-to-use product image uploads
- ✅ **Organized Structure** - Logical folder organization (Auth/Guest/User/Admin)
- ✅ **Pre-configured Headers** - Automatic Accept/Content-Type headers

### Authentication (`/api/auth/`)
```
# User Authentication
POST /api/auth/user/register      # User registration
POST /api/auth/user/login         # User login
POST /api/auth/user/logout        # User logout (authenticated)

# Admin Authentication  
POST /api/auth/admin/login        # Admin login
POST /api/auth/admin/logout       # Admin logout (authenticated)
```

**Request Body Examples:**
```json
# User Registration
POST /api/auth/user/register
{
    "name": "John Doe",
    "email": "user@example.com",
    "password": "12345678",
    "password_confirmation": "12345678"
}

# User Login
POST /api/auth/user/login
{
    "email": "user@user.com",
    "password": "12345678"
}

# Admin Login
POST /api/auth/admin/login
{
    "email": "admin@admin.com",
    "password": "12345678"
}
```

### Public Routes (`/api/guest/`) - No Authentication Required
```
# Categories (Accessible by guests and authenticated users)
GET  /api/guest/categories        # List categories
GET  /api/guest/categories/{id}   # Show single category

# Products (Accessible by guests and authenticated users)
GET  /api/guest/products          # List products (with advanced filtering)
GET  /api/guest/products/{id}     # Show single product
```

### User Routes (`/api/user/`) - Authenticated Users Only
```
# Orders Management (Users can only view their own orders)
GET  /api/user/orders             # List user's orders (with filtering)
GET  /api/user/orders/{id}        # Show user's order details
```

### Admin Routes (`/api/admin/`) - Admin Only - Full CRUD
```
# Categories Management (Complete CRUD)
GET    /api/admin/categories           # List all categories
GET    /api/admin/categories/create    # Get category creation form data
POST   /api/admin/categories           # Create new category
GET    /api/admin/categories/{id}      # Show category details
GET    /api/admin/categories/{id}/edit # Get category edit form data
PUT    /api/admin/categories/{id}      # Update category
DELETE /api/admin/categories/{id}      # Delete category
PUT    /api/admin/categories/{id}/toggleActive/{state}  # Toggle category status

# Products Management (Complete CRUD + Image Upload)
GET    /api/admin/products             # List all products (with advanced filtering)
GET    /api/admin/products/create      # Get product creation form data
POST   /api/admin/products             # Create new product (with image upload)
GET    /api/admin/products/{id}        # Show product details
GET    /api/admin/products/{id}/edit   # Get product edit form data
PUT    /api/admin/products/{id}        # Update product (with image upload)
DELETE /api/admin/products/{id}        # Delete product
PUT    /api/admin/products/{id}/toggleActive/{state}  # Toggle product status

# Orders Management (Complete CRUD)
GET    /api/admin/orders               # List all orders (with advanced filtering)
GET    /api/admin/orders/create        # Get order creation form data
POST   /api/admin/orders               # Create new order
GET    /api/admin/orders/{id}          # Show order details
GET    /api/admin/orders/{id}/edit     # Get order edit form data
PUT    /api/admin/orders/{id}          # Update order (status, notes)
DELETE /api/admin/orders/{id}          # Delete order
PUT    /api/admin/orders/{id}/toggleActive/{state}  # Toggle order status
```

**Admin Request Body Examples:**
```json
# Create Category
POST /api/admin/categories
{
    "name": "Electronics",
    "description": "Electronic devices and accessories"
}

# Create Product (form-data for image upload)
POST /api/admin/products
Content-Type: multipart/form-data
{
    "name": "MacBook Pro",
    "description": "Latest MacBook Pro with M2 chip",
    "price": 1299.99,
    "stock": 50,
    "category_id": 1,
    "image": [file upload]
}

# Create Order
POST /api/admin/orders
{
    "products": [
        {
            "product_id": 1,
            "quantity": 2
        },
        {
            "product_id": 5,
            "quantity": 1
        }
    ],
    "notes": "Express delivery requested"
}

# Update Order
PUT /api/admin/orders/{id}
{
    "status": "completed",
    "notes": "Order shipped successfully"
}
```

### Advanced Filtering Parameters
```
# Products Filtering (Guest & Admin)
?category_ids[]=1&category_ids[]=2     # Filter by category IDs
?category_name=electronics             # Filter by category name (like search)
?category_names[]=phones&category_names[]=laptops  # Filter by category names
?min_price=100&max_price=500          # Price range filtering
?name=macbook                         # Product name search
?q=laptop                             # General search query
?page=1&per_page=15                   # Pagination
?sortColumn=price&sortDirection=ASC   # Sorting
?date_from=2024-01-01&date_to=2024-12-31  # Date range

# Orders Filtering (User & Admin)
?status=pending                       # Filter by order status
?order_number=ORD-001                 # Filter by order number
?user_name=john                       # Filter by user name (admin only)
?user_names[]=john&user_names[]=jane  # Filter by user names (admin only)
?user_ids[]=1&user_ids[]=2            # Filter by user IDs (admin only)
?product_name=laptop                  # Filter by product name
?product_names[]=laptop&product_names[]=phone  # Filter by product names
?category_names[]=electronics         # Filter by category names
?category_ids[]=1&category_ids[]=2    # Filter by category IDs
```

## ⚡ Quick Setup

```bash
# Clone repository
git clone https://github.com/emad566/izam-fullstack-task.git
cd izam-fullstack-task

# Automated setup with Docker
chmod +x docker-setup.sh
./docker-setup.sh

# Access application
open http://localhost:8000
```

## 🐳 Docker Setup

### Production Mode (Recommended)
```bash
# Full production setup with Docker
docker-compose up -d

# Access points:
# Frontend + Backend: http://localhost:8000
# phpMyAdmin: http://localhost:8081
# Database: localhost:3307
```

### Development Mode
```bash
# Development with hot reloading
docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d

# Access points:
# Laravel: http://localhost:8000
# Vite Dev Server: http://localhost:5173
```

### Docker Features
- ✅ **Automated Setup** - One-command deployment
- ✅ **Multi-Service** - Laravel + MySQL + Redis + phpMyAdmin
- ✅ **Hot Reloading** - React development with Vite
- ✅ **Production Ready** - Optimized containers
- ✅ **Database Management** - phpMyAdmin interface

## 🔐 Authentication Flow

### Multi-Guard System
- **User Guard**: Customer authentication for shopping
- **Admin Guard**: Administrative access for management

### Login Process
1. User submits credentials via `/api/login`
2. Server validates and returns Sanctum token
3. Token stored in frontend for subsequent requests
4. Protected routes require `Authorization: Bearer {token}`

## 🚀 Running Backend & Frontend

### Development Mode
```bash
# Backend (Laravel) - Port 8000
docker-compose up -d

# Frontend (React) - Port 5173
npm run dev
```

### Production Mode
```bash
# Full stack - Port 8000
docker-compose -f docker-compose.yml up -d
```

## 🧪 Testing

```bash
# Run all API tests
php artisan test --testsuite=Api

# Test coverage: 109 tests, 752 assertions
# Includes: API endpoints, Authentication, Security, Controllers
```

## 📧 Order Email Event System

### Event-Driven Order Notifications

The application implements a sophisticated event-driven email notification system that automatically notifies administrators when new orders are placed.

### How It Works

```php
// 1. Order Creation Triggers Event (Order Model)
static::created(function ($order) {
    event(new OrderPlaced($order, $order->user));
});

// 2. Event Listener Handles Notification (EventServiceProvider)
OrderPlaced::class => [
    SendOrderNotificationToAdmin::class,
],

// 3. Admin Notification Email Sent
Mail::to($admin->email)->send(new OrderPlacedNotification($event->order, $event->user));
```

### Features

✅ **Automatic Triggering** - Event fires immediately when order is created  
✅ **Multi-Admin Support** - Sends notifications to all active administrators  
✅ **Queue Support** - Implements `ShouldQueue` for background processing  
✅ **Comprehensive Email** - Includes order details, customer info, and product list  
✅ **Error Handling** - Graceful failure handling with detailed logging  
✅ **Testing Coverage** - Complete test suite for event system (7 test cases)

### Email Content

The notification email includes:
- **Order Number** - Unique order identifier  
- **Customer Details** - User name and contact information
- **Product List** - All ordered items with quantities and prices
- **Total Amount** - Complete order value
- **Order Notes** - Any special instructions from customer

### Event Flow

1. **Order Created** → `OrderPlaced` event dispatched
2. **Event Listener** → `SendOrderNotificationToAdmin` processes event
3. **Email Generated** → `OrderPlacedNotification` mailable created
4. **Admin Notified** → Email sent to all administrators
5. **Error Logging** → Any failures logged for debugging

## 🎯 Custom Trait Controller Design Pattern

### BaseController Pattern
- **Dynamic Model Binding**: Automatic model resolution
- **Standardized Responses**: Consistent API responses
- **Error Handling**: Centralized exception management

### Controller Traits System
```php
// Modular controller functionality
IndexTrait::class    # List and filter resources
ShowTrait::class     # Show single resource
EditTrait::class     # Update resources
DestroyTrait::class  # Delete resources
ToggleActiveTrait::class # Toggle status
```

### Benefits
- **DRY Principle**: Reusable code across controllers
- **Consistency**: Standardized behavior
- **Maintainability**: Single source of truth

## 🔐 Multi-Auth System

### Guard Configuration
```php
// User authentication
'users' => [
    'driver' => 'sanctum',
    'provider' => 'users',
]

// Admin authentication  
'admins' => [
    'driver' => 'sanctum',
    'provider' => 'admins',
]
```

### Route Protection
- **User Routes**: `/api/user/*` - Customer access
- **Admin Routes**: `/api/admin/*` - Administrative access
- **Guest Routes**: `/api/auth/*` - Public access

## 🛡️ Security Features

### Input Validation & Sanitization
- ✅ **Custom Form Requests** - Security-first validation with automatic input sanitization
- ✅ **XSS Prevention** - Input filtering and output escaping
- ✅ **SQL Injection Protection** - Parameterized queries and input validation
- ✅ **File Upload Security** - Type validation and secure storage

### Authentication & Authorization
- ✅ **Multi-Guard System** - Separate user and admin authentication with token isolation
- ✅ **Laravel Sanctum** - Secure API token management
- ✅ **Role-Based Access** - Granular permission system
- ✅ **Token Scoping** - Prevents privilege escalation between guards

### Advanced Security
- ✅ **CSRF Protection** - Laravel's built-in CSRF protection
- ✅ **Rate Limiting** - API throttling to prevent abuse
- ✅ **Security Headers** - CORS and HTTP security headers
- ✅ **Password Security** - Bcrypt hashing with strong validation rules
- ✅ **Error Handling** - Secure error responses without information leakage

## 👨‍💻 Development Attribution

**Core Architecture & Development**: [Emadeldeen Soliman](https://github.com/emad566)
- Custom-built Trait Controller Design Patterns
- Multi-guard authentication system
- Core testing framework and controller architecture
- Custom Artisan commands and backend API

**Documentation & Optimization**: Claude 4 Sonnet AI
- Comprehensive documentation and README structure
- Code optimization suggestions and best practices
- Testing functions and frontend integration

## 📞 Support & Contact

**Developer**: Emadeldeen Soliman  
**GitHub**: [github.com/emad566](https://github.com/emad566)  
**LinkedIn**: [Emad El-Deen Soliman](https://www.linkedin.com/in/emadeldeen-soliman/)

**Project Repository**: [IZAM Fullstack Task](https://github.com/emad566/izam-fullstack-task)  
**Live Demo**: [https://izam-task.emadw3.com](https://izam-task.emadw3.com)

---

📚 **Need more details?** Check the [Complete Documentation](README-detailed.md) for comprehensive setup guides, API documentation, testing details, and deployment instructions.
