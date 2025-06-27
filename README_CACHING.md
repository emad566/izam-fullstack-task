# Caching Implementation

This project implements a comprehensive caching system for the Products API endpoint using Laravel's Cache facade and a custom CacheNames enum.

## CacheNames Enum

The `CacheNames` enum provides centralized cache key management with the following cache types:

```php
use App\CacheNames;

// Available cache types
CacheNames::PRODUCTS_LIST     // Product listing with filters
CacheNames::PRODUCT_DETAIL    // Individual product details
```

### Usage Examples

#### Basic Cache Key Generation
```php
// Simple cache key
$key = CacheNames::PRODUCTS_LIST->key();
// Result: "products_list"

// Cache key with parameters
$key = CacheNames::PRODUCTS_LIST->key(['category' => 'electronics', 'price' => 100]);
// Result: "products_list_a1b2c3d4..." (with MD5 hash of parameters)
```



#### Paginated Cache Keys
```php
$paginatedKey = CacheNames::PRODUCTS_LIST->paginatedKey([
    'category' => 'electronics',
    'min_price' => 50,
    'page' => 2
]);
// Includes pagination parameters (page, per_page, sort_column, sort_direction)
```

## Products API Caching

### Cached Endpoints

#### GET /admin/products
- **Cache Duration**: Configurable via `config('constants.products_cache_duration')` minutes
- **Cache Key**: Based on all query parameters including:
  - Filters: `category_name`, `category_names`, `min_price`, `max_price`, `name`, `q`
  - Pagination: `page`, `per_page`, `sortColumn`, `sortDirection`
  - Other: `date_from`, `date_to`, `deleted_at`

#### GET /admin/products/{id}
- **Cache Duration**: Configurable via `config('constants.products_cache_duration')` minutes
- **Cache Key**: Based on product ID and `deleted_at` parameter

### Cache Invalidation

Cache is automatically invalidated (cleared) when:
- ✅ **Creating** a new product (`POST /admin/products`)
- ✅ **Updating** an existing product (`PUT /admin/products/{id}`)
- ✅ **Deleting** a product (`DELETE /admin/products/{id}`)
- ✅ **Toggling** product active status (`PUT /admin/products/{id}/toggleActive/{state}`)

### Performance Benefits
 

### Cache Configuration

Cache settings can be configured in:
- **Cache Driver**: `config/cache.php` (default: database)
- **Cache Duration**: `config('constants.products_cache_duration')` (configurable)

### Testing

Comprehensive test coverage includes:
- ✅ Cache functionality verification
- ✅ Cache invalidation testing
- ✅ Performance comparison tests
- ✅ Cache key uniqueness validation
- ✅ Filter-based cache differentiation

## Example API Usage

```bash
# First request - hits database, caches result
curl "http://127.0.0.1:8000/api/admin/products?category_name=electronics&min_price=100"

# Second identical request - served from cache (faster)
curl "http://127.0.0.1:8000/api/admin/products?category_name=electronics&min_price=100"

# Different filters - different cache key, hits database
curl "http://127.0.0.1:8000/api/admin/products?category_name=clothing&max_price=50"

# Create new product - invalidates all product caches
curl -X POST "http://127.0.0.1:8000/api/admin/products" -d '{"name":"New Product",...}'

# Next request - cache rebuilt
curl "http://127.0.0.1:8000/api/admin/products?category_name=electronics&min_price=100"
```

## Technical Implementation

The caching system uses:
- **Laravel Cache Facade**: For cache storage and retrieval
- **MD5 Hashing**: For parameter-based cache key generation
- **Database Cache Driver**: Default storage backend
- **Selective Cache Clearing**: Pattern-based cache invalidation for file driver, fallback to Cache::flush() for others
 