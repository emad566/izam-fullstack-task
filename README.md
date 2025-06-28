# IZAM E-commerce Fullstack Application

A comprehensive React + Laravel fullstack e-commerce application featuring a modern React frontend with TypeScript and a robust Laravel RESTful API backend.

📚 **[Complete Documentation - Click Here for More Details](README-detailed.md)**

## ⏱️ Development Time Tracking

**Estimated Time:** 24 hours  
**Actual Time:** 28 hours total
- 18 hours for core development
- 10 additional hours for unit testing, code refactoring, documentation, and deployment

## 🚀 Live Demo

🌟 **Project Live**: [https://izam-task.emadw3.com](https://izam-task.emadw3.com) - no setup required!  
🌟 **API Live**: [https://izam-task.emadw3.com/api](https://izam-task.emadw3.com/api) - no setup required!

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

### Authentication
```
POST /api/login           # User login
POST /api/register        # User registration
POST /api/logout          # User logout
```

### Products
```
GET  /api/products        # List products (with filters)
GET  /api/products/{id}   # Get single product
```

### Categories
```
GET  /api/categories      # List all categories
```

### Orders
```
POST /api/orders          # Create new order
GET  /api/orders          # User's orders (authenticated)
```

### Admin (Protected)
```
GET  /api/admin/products  # Admin product management
GET  /api/admin/orders    # Admin order management
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
# Run all tests
php artisan test

# Test coverage: 109 tests, 752 assertions
# Includes: API endpoints, Authentication, Security, Controllers
```

## 🎯 Custom Trait Controller Design Pattern

### BaseController Pattern
- **Dynamic Model Binding**: Automatic model resolution
- **Standardized Responses**: Consistent API responses
- **Error Handling**: Centralized exception management

### Controller Traits System
```php
// Modular controller functionality
IndexTrait::class    # List resources
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
