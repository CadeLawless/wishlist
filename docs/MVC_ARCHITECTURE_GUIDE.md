# Wishlist Application - MVC Architecture Guide

## Table of Contents
1. [Overview](#overview)
2. [Directory Structure](#directory-structure)
3. [Core Components](#core-components)
4. [Controllers](#controllers)
5. [Models](#models)
6. [Views](#views)
7. [Services](#services)
8. [Middleware](#middleware)
9. [Validation](#validation)
10. [Routing](#routing)
11. [Database Layer](#database-layer)
12. [File Organization](#file-organization)
13. [Best Practices](#best-practices)
14. [Common Patterns](#common-patterns)

## Overview

This Wishlist application follows the Model-View-Controller (MVC) architectural pattern with additional layers for services, middleware, and validation. The architecture promotes separation of concerns, maintainability, and scalability.

### Key Principles
- **Separation of Concerns**: Each component has a single responsibility
- **Dependency Injection**: Services are injected into controllers
- **Service Layer**: Business logic is encapsulated in services
- **Validation Layer**: Input validation is centralized
- **Middleware**: Cross-cutting concerns are handled by middleware

## Directory Structure

```
wishlist/
├── app/                          # Application core
│   ├── Controllers/              # Request handlers
│   ├── Core/                     # Framework components
│   ├── Helpers/                  # Utility classes
│   ├── Middleware/               # Request middleware
│   ├── Models/                   # Data models
│   ├── Services/                 # Business logic
│   └── Validation/               # Input validation
├── config/                       # Configuration files
├── docs/                         # Documentation
├── public/                       # Web-accessible files
├── routes/                       # Route definitions
├── views/                        # Templates and layouts
└── vendor/                       # Composer dependencies
```

## Core Components

### Core Framework (`app/Core/`)

#### `Controller.php`
Base controller class providing common functionality:
- View rendering (`view()`, `render()`)
- Redirects (`redirect()`)
- Authentication checks (`auth()`, `requireAuth()`, `requireGuest()`, `requireAdmin()`)
- Flash messages (`withSuccess()`, `withError()`)

#### `Model.php`
Abstract base model providing CRUD operations:
- `find($id)` - Find record by ID
- `create($data)` - Create new record
- `update($id, $data)` - Update existing record
- `delete($id)` - Delete record
- `where($column, $operator, $value)` - Query with conditions

#### `Database.php`
Database connection and query management:
- `query($sql, $params)` - Execute prepared statements
- `lastInsertId()` - Get last inserted ID
- Connection management and error handling

#### `Request.php`
HTTP request encapsulation:
- `get($key)` - GET parameters
- `post($key)` - POST parameters
- `input($key)` - GET or POST parameters
- `file($key)` - File uploads
- `param($key)` - Route parameters
- `server($key)` - Server variables
- `header($key)` - HTTP headers

#### `Response.php`
HTTP response handling:
- `redirect($url)` - Redirect responses
- `json($data)` - JSON responses
- Flash message management
- Response status codes

#### `View.php`
Template rendering system:
- `render($template, $data)` - Render template with data
- `renderWithLayout($template, $data, $layout)` - Render with layout
- Template path resolution
- Data passing to templates

#### `Router.php`
URL routing and dispatch:
- Route registration
- URL pattern matching
- Parameter extraction
- Middleware application
- Controller method dispatch

#### `Route.php`
Individual route representation:
- Path matching
- Parameter extraction
- Middleware assignment
- Handler storage

#### `Config.php`
Configuration management:
- Environment variable loading
- Configuration file loading
- Application settings

## Controllers

Controllers handle HTTP requests and coordinate between models, services, and views.

### `AuthController.php`
User authentication and account management:
- **Login/Logout**: `login()`, `logout()`
- **Registration**: `register()`, `verifyEmail()`
- **Password Management**: `forgotPassword()`, `resetPassword()`
- **Profile Management**: `profile()`, `updateProfile()`
- **Admin Functions**: `admin()`, `toggleUserStatus()`

### `WishlistController.php`
Wishlist management and display:
- **CRUD Operations**: `create()`, `show()`, `update()`, `delete()`
- **Visibility**: `hide()`, `showPublic()`
- **Completion**: `complete()`, `reactivate()`
- **Item Management**: `addItem()`, `copyFrom()`, `copyTo()`
- **Filtering/Sorting**: `filterItems()`, `paginateItems()`

### `ItemController.php`
Individual item management:
- **CRUD Operations**: `create()`, `edit()`, `update()`, `delete()`
- **Image Handling**: File upload and management
- **Item Relationships**: Copy tracking and synchronization

### `BuyerController.php`
Public wishlist viewing:
- **Public Access**: `show()` - View wishlist by secret key
- **Item Interaction**: `purchaseItem()` - Mark items as purchased
- **Filtering/Sorting**: `filterItems()`, `paginateItems()`

### `HomeController.php`
Dashboard and navigation:
- **Dashboard**: `index()` - User's wishlist overview
- **Navigation**: Route to appropriate sections

## Models

Models represent data entities and handle database interactions.

### `User.php`
User account management:
- **Authentication**: `findByUsername()`, `findByEmail()`
- **Registration**: `createUser()`, `verifyEmail()`
- **Password**: `updatePassword()`, `resetPassword()`
- **Profile**: `updateProfile()`

### `Wishlist.php`
Wishlist entity management:
- **CRUD**: `createWishlist()`, `findByUserAndId()`, `update()`, `delete()`
- **Access**: `findBySecretKey()`, `findOtherWishlists()`
- **Search**: `searchByName()`
- **Themes**: `updateTheme()`
- **Status**: `toggleVisibility()`, `toggleComplete()`
- **Duplicates**: `findDuplicates()`, `updateDuplicateFlags()`

### `Item.php`
Wishlist item management:
- **CRUD**: `create()`, `find()`, `update()`, `delete()`
- **Relationships**: `findByCopyIdAndWishlist()`, `findByCopyIdExcludingWishlist()`
- **Bulk Operations**: `deleteByWishlistId()`
- **Pagination**: `getPaginatedItems()`, `countItems()`

### `Theme.php`
Theme and styling management:
- **Backgrounds**: `getBackgroundThemes()`
- **Gift Wraps**: `getGiftWrapThemes()`
- **Filtering**: `getThemesByType()`

## Views

Views handle presentation and user interface.

### Layouts (`views/layouts/`)

#### `main.php`
Primary application layout:
- HTML structure
- Navigation header
- Footer with scripts
- Dark mode toggle
- Responsive design

#### `buyer.php`
Public wishlist layout:
- Simplified header
- Client-side dark mode
- Public-specific styling
- No authentication required

#### `auth.php`
Authentication pages layout:
- Login/register forms
- Minimal navigation
- Authentication-specific styling

### Controllers (`views/`)

#### Authentication (`views/auth/`)
- `login.php` - User login form
- `register.php` - User registration form
- `profile.php` - User profile management
- `admin.php` - Admin dashboard

#### Wishlist (`views/wishlist/`)
- `index.php` - User's wishlist overview
- `show.php` - Individual wishlist display
- `create.php` - New wishlist creation

#### Items (`views/items/`)
- `create.php` - Add new item form
- `edit.php` - Edit existing item
- `_form.php` - Reusable item form component

#### Buyer (`views/buyer/`)
- `show.php` - Public wishlist view

### Components (`views/components/`)
- `header.php` - Navigation header
- `footer.php` - Page footer with scripts
- `pagination-controls.php` - Reusable pagination
- `sidebar.php` - Navigation sidebar
- `alerts.php` - Flash message display

## Services

Services encapsulate business logic and provide reusable functionality.

### `WishlistService.php`
Core wishlist business logic:
- **CRUD Operations**: `createWishlist()`, `getWishlistById()`, `updateWishlistName()`
- **Item Management**: `addItem()`, `getWishlistItems()`, `updateItem()`, `deleteItem()`
- **Copying**: `copyItems()`, `updateCopiedItems()`
- **Statistics**: `getWishlistStats()`
- **Search**: `searchWishlists()`
- **Status**: `toggleWishlistVisibility()`, `toggleWishlistComplete()`

### `AuthService.php`
Authentication business logic:
- **Login/Logout**: `login()`, `logout()`
- **Registration**: `register()`, `verifyEmail()`
- **Password**: `forgotPassword()`, `resetPassword()`
- **Session**: `checkAuth()`, `getCurrentUser()`

### `FilterService.php`
Sorting and filtering logic:
- **Buyer Filters**: `processBuyerFilters()`
- **Wisher Filters**: `processWisherFilters()`
- **Sorting**: `buildOrderClause()`, `getSortDirection()`
- **Validation**: `validateFilters()`

### `SessionManager.php`
Session management:
- **Session Control**: `startSession()`, `clearSession()`
- **User State**: `isLoggedIn()`, `getUsername()`, `setLoginSession()`
- **Preferences**: `storeBuyerSortPreferences()`, `getWisherSortPreferences()`
- **Flash Messages**: `setWishlistFlash()`, `getFlashMessages()`

### `ThemeService.php`
Theme and styling management:
- **Backgrounds**: `getBackgroundImage()`, `getBackgroundThemes()`
- **Gift Wraps**: `getGiftWrapImage()`, `getGiftWrapThemes()`
- **Filtering**: Theme filtering by type (Birthday/Christmas)

### `ItemRenderService.php`
Item display logic:
- **Rendering**: `renderItem()` - Generate item HTML
- **Conditional Display**: Different rendering for buyer vs wisher views
- **Image Handling**: Thumbnail generation and display

### `FileUploadService.php`
File upload management:
- **Image Processing**: Upload, resize, optimize images
- **Path Management**: `getBaseUploadPath()` - Centralized path handling
- **Cleanup**: Delete files and directories

### `ItemCopyService.php`
Item copying between wishlists:
- **Copying**: `copyItems()` - Copy items between wishlists
- **Image Handling**: `copyItemImage()` - Copy item images
- **Cleanup**: `deleteImagesFromAllWishlists()`

### `PaginationService.php`
Pagination logic:
- **Page Calculation**: Calculate total pages and offsets
- **Navigation**: Generate pagination controls
- **AJAX Support**: Handle AJAX pagination requests

### `EmailService.php`
Email functionality:
- **Verification**: `sendVerificationEmail()`
- **Password Reset**: `sendPasswordResetEmail()`
- **Welcome**: `sendWelcomeEmail()`

### `UserPreferencesService.php`
User preference management:
- **Dark Mode**: `toggleDarkMode()`
- **Settings**: User-specific preferences

### `PopupManager.php`
Modal and popup management:
- **Theme Selection**: Theme popup functionality
- **Dynamic Content**: Load popup content via AJAX

## Middleware

Middleware handles cross-cutting concerns and request preprocessing.

### `AuthMiddleware.php`
Authentication enforcement:
- **Protected Routes**: Ensure user is logged in
- **Redirect**: Redirect to login if not authenticated
- **Session Check**: Verify session validity

### `GuestMiddleware.php`
Guest-only routes:
- **Public Access**: Allow access without authentication
- **Redirect**: Redirect authenticated users away from guest pages
- **Examples**: Login, register pages

### `AdminMiddleware.php`
Administrative access:
- **Admin Check**: Verify user has admin privileges
- **Access Control**: Restrict admin-only functionality
- **Security**: Prevent unauthorized admin access

## Validation

Input validation is centralized in dedicated validator classes.

### `BaseValidator.php`
Abstract base validator:
- **Common Methods**: Shared validation logic
- **Error Handling**: Standardized error management
- **Validation Rules**: Reusable validation patterns

### `UserRequestValidator.php`
User-related validation:
- **Registration**: `validateRegistration()`
- **Login**: `validateLogin()`
- **Profile**: `validateNameUpdate()`, `validateEmailUpdate()`
- **Password**: `validatePasswordChange()`, `validatePasswordReset()`

### `WishlistRequestValidator.php`
Wishlist validation:
- **Creation**: `validateWishlist()`
- **Updates**: `validateWishlistName()`
- **Form Data**: Validate wishlist form submissions

### `ItemRequestValidator.php`
Item validation:
- **Creation**: `validateItem()`
- **Updates**: Validate item modifications
- **File Uploads**: Validate image uploads

## Routing

### Route Definition (`routes/web.php`)
All application routes are defined in a single file:

```php
// Authentication routes
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'register']);
$router->post('/register', [AuthController::class, 'register']);

// Wishlist routes
$router->get('/wishlists', [WishlistController::class, 'index']);
$router->get('/wishlist/{id}', [WishlistController::class, 'show']);
$router->post('/wishlist/{id}/filter', [WishlistController::class, 'filterItems']);

// Buyer routes (public access)
$router->get('/buyer/{key}', [BuyerController::class, 'show']);
$router->post('/buyer/{key}/filter', [BuyerController::class, 'filterItems']);
```

### Route Parameters
- `{id}` - Wishlist ID
- `{key}` - Secret key for public access
- `{itemId}` - Item ID

### Middleware Application
Routes can have middleware applied:
```php
$router->get('/admin', [AuthController::class, 'admin'], [AuthMiddleware::class, AdminMiddleware::class]);
```

## Database Layer

### Connection Management
- **Singleton Pattern**: Single database connection
- **Prepared Statements**: All queries use prepared statements
- **Error Handling**: Comprehensive error management
- **Transaction Support**: Database transaction handling

### Query Patterns
```php
// Find single record
$stmt = Database::query("SELECT * FROM users WHERE id = ?", [$id]);
$user = $stmt->get_result()->fetch_assoc();

// Find multiple records
$stmt = Database::query("SELECT * FROM wishlists WHERE username = ?", [$username]);
$wishlists = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Insert record
$stmt = Database::query("INSERT INTO items (name, price, wishlist_id) VALUES (?, ?, ?)", [$name, $price, $wishlistId]);
$itemId = Database::lastInsertId();
```

## File Organization

### Controllers
- **Single Responsibility**: Each controller handles one entity
- **Dependency Injection**: Services injected via constructor
- **Thin Controllers**: Minimal logic, delegate to services
- **Consistent Naming**: `{Entity}Controller.php`

### Models
- **Active Record Pattern**: Models contain data and behavior
- **Static Methods**: For queries and operations
- **Relationship Management**: Handle entity relationships
- **Data Validation**: Model-level validation

### Services
- **Business Logic**: Encapsulate complex operations
- **Reusability**: Shared across multiple controllers
- **Single Responsibility**: Each service has a focused purpose
- **Dependency Management**: Services can depend on other services

### Views
- **Template Hierarchy**: Layouts, controllers, components
- **Separation**: Logic separated from presentation
- **Reusability**: Components for common elements
- **Conditional Rendering**: Different views for different contexts

## Best Practices

### Controller Best Practices
1. **Keep Controllers Thin**: Delegate business logic to services
2. **Single Responsibility**: One controller per entity
3. **Consistent Naming**: Follow naming conventions
4. **Error Handling**: Proper error responses
5. **Input Validation**: Validate all inputs

### Model Best Practices
1. **Active Record**: Models represent database entities
2. **Static Methods**: For queries and operations
3. **Data Integrity**: Validate data at model level
4. **Relationship Management**: Handle entity relationships
5. **Query Optimization**: Efficient database queries

### Service Best Practices
1. **Business Logic**: Encapsulate complex operations
2. **Reusability**: Design for reuse across controllers
3. **Single Responsibility**: Each service has one purpose
4. **Dependency Injection**: Inject dependencies via constructor
5. **Error Handling**: Comprehensive error management

### View Best Practices
1. **Separation of Concerns**: Logic separate from presentation
2. **Template Reuse**: Use layouts and components
3. **Conditional Rendering**: Different views for different contexts
4. **Security**: Escape all output
5. **Performance**: Minimize database queries in views

## Common Patterns

### Controller Pattern
```php
class EntityController extends Controller
{
    private EntityService $entityService;
    private EntityValidator $validator;

    public function __construct()
    {
        parent::__construct();
        $this->entityService = new EntityService();
        $this->validator = new EntityValidator();
    }

    public function show(int $id): Response
    {
        $entity = $this->entityService->getById($id);
        return $this->view('entity/show', ['entity' => $entity]);
    }
}
```

### Service Pattern
```php
class EntityService
{
    public function getById(int $id): ?array
    {
        return Entity::find($id);
    }

    public function create(array $data): ?int
    {
        return Entity::create($data);
    }
}
```

### Model Pattern
```php
class Entity extends Model
{
    protected static string $table = 'entities';

    public static function findByUser(int $userId): array
    {
        $stmt = Database::query("SELECT * FROM " . static::$table . " WHERE user_id = ?", [$userId]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
```

### Middleware Pattern
```php
class AuthMiddleware
{
    public static function handle(Request $request, callable $next): Response
    {
        if (!SessionManager::isLoggedIn()) {
            return Response::redirect('/login');
        }
        return $next($request);
    }
}
```

This architecture provides a solid foundation for the Wishlist application, promoting maintainability, scalability, and separation of concerns while following modern PHP development practices.
