<!-- 8032ab8c-4db7-4cdc-8f60-5bac5ab4dca1 7bdb2be4-87e7-424c-b259-79eeaf579fdf -->
# MVC Architecture Review & Improvement Plan

## Issues Found

### Critical Issues

1. **Code Duplication - Background Image Retrieval**: Background theme image retrieval is duplicated across `ItemController::create()`, `ItemController::edit()`, `ItemController::store()`, and `ItemController::update()` methods (lines 40-47, 116-124, 169-177, 309-317, 380-388).

2. **Inconsistent Service Usage**: `WishlistService` uses both direct database queries and Model methods inconsistently (e.g., `getWishlistById()` uses raw SQL, while `addItem()` uses Model).

3. **BuyerController Type Error**: `BuyerController::show()` returns Response but has no use statement for it (line 18).

4. **Error Messages as HTML in Controllers**: Hardcoded HTML error messages in controllers violates separation of concerns (`AuthController`, `ItemController`, `WishlistController`).

5. **Direct Database Access in Controllers**: `ItemController` and `WishlistController` make direct `Database::query()` calls instead of using services/models.

### Code Quality Issues

6. **Duplicate User Retrieval Logic**: `auth()` method in `Controller` duplicates database user lookup that's already in `AuthService`.

7. **Session Management Scattered**: Session start checks are duplicated throughout controllers and services.

8. **generateRandomString() Duplicated**: Exists in both `AuthController` and `Wishlist` model.

9. **Over-commenting**: Some debug `error_log()` calls should be removed from production code (`AuthController`, `Model::update()`).

10. **Missing Return Type Hints**: Several service methods lack return type hints (e.g., `WishlistService::copyItems()`).

11. **Unused Constructor Dependencies**: `HomeController` injects `WishlistService` but never uses it.

12. **File Path Duplication**: `getBaseUploadPath()` is duplicated in both `FileUploadService` and `ItemCopyService`.

### Architecture Issues

13. **Middleware Not Being Used**: Middleware classes exist (`AuthMiddleware`, `GuestMiddleware`, `AdminMiddleware`) but are never registered or applied to routes. Instead, controllers manually call `requireAuth()`, `requireGuest()`, etc. in every method, duplicating authentication logic.

14. **Service Layer Inconsistency**: Some services instantiate dependencies in constructor (good), others use static methods on models (inconsistent).

15. **Fat Controllers**: Controllers contain business logic that should be in services (e.g., item copy logic in `WishlistController`).

16. **Missing Validation Layer**: Form data validation done in controllers instead of dedicated request classes.

17. **No Repository Pattern**: Models mix query building with data representation.

## Improvements Needed

### 1. Extract Background Theme Helper Method

Create a `ThemeService::getBackgroundImage(int $themeId)` method to eliminate duplication.

### 2. Refactor WishlistService for Consistency

All database operations should go through models, not raw SQL queries.

### 3. Create Utility/Helper Class

Move shared utility methods like `generateRandomString()` to a `StringHelper` or `Helpers` class.

### 4. Create Request Validation Classes

Extract validation to dedicated Request classes (e.g., `StoreItemRequest`, `UpdateWishlistRequest`).

### 5. Remove Debug Logging

Clean up production code by removing temporary `error_log()` statements.

### 6. Centralize Session Management

Create a `SessionManager` service to handle all session operations.

### 7. Fix Service Injection

Remove unused dependencies and ensure all services are properly injected.

### 8. Consolidate File Upload Paths

Create a single source of truth for upload paths in `FileUploadService`.

### 9. Move Business Logic to Services

Extract all business logic from controllers to appropriate services.

### 10. Add Missing Type Hints

Add strict typing throughout the codebase for better IDE support and error prevention.

### 11. Implement Middleware Properly

Register middleware in routes and apply to protected/guest routes. Remove manual `requireAuth()`, `requireGuest()`, `requireAdmin()` calls from controllers. Create `GuestMiddleware` and `AdminMiddleware` if missing.

## Documentation to Create

### MVC Architecture Guide

A comprehensive guide explaining:

- Directory structure and file organization
- How routing works (routes → controllers → services → models)
- Where to add new features
- Service vs Controller vs Model responsibilities
- Common patterns used in the codebase
- How to create new CRUD operations
- How authentication and middleware work
- Where old procedural files map to new MVC structure

## Testing Requirements

After each phase, the following functionality must be manually tested:

**Phase 1 Testing (Critical Fixes):**

- Login/Registration flows work correctly
- Wishlist creation with theme backgrounds displays properly
- Item creation/editing shows correct background images
- All pages load without errors

**Phase 2 Testing (Code Quality):**

- All existing functionality still works
- No PHP errors or warnings appear
- File uploads work correctly across different wishlists

**Phase 3 Testing (Architecture - Most Critical):**

- **Authentication:** Login, logout, auto-login (remember me) work
- **Guest Access:** Redirects to login when not authenticated
- **Protected Routes:** Profile, admin, wishlists require authentication
- **Admin Access:** Admin-only pages block non-admin users
- **Item CRUD:** Create, view, edit, delete items on wishlists
- **Wishlist CRUD:** Create, view, edit, delete, hide/show, complete wishlists
- **Copy Items:** Copy items between wishlists
- **Pagination:** Navigate between pages of items
- **Filtering/Sorting:** Sort by priority, price, date
- **Buyer View:** Public wishlist viewing works

**Phase 4 Testing:**

- Documentation is accurate and complete
- Code examples in docs work when tested

## Todos

### Phase 1: Critical Fixes

- Fix BuyerController missing Response import
- Create ThemeService method for background images
- Remove all direct Database::query() calls from controllers
- Create StringHelper utility class

### Phase 2: Code Quality

- Remove debug error_log statements
- Add missing return type hints
- Remove unused constructor dependencies
- Consolidate file upload path logic

### Phase 3: Architecture Improvements

- Implement middleware properly (register middleware, apply to routes, remove manual auth checks)
- Create Request validation classes
- Move business logic from controllers to services
- Refactor WishlistService for consistency
- Create SessionManager service

### Phase 4: Documentation

- Create MVC Architecture Guide document
- Add inline documentation where needed (but not over-document)
- Create migration guide from old to new structure

### To-dos

- [ ] Fix BuyerController missing Response use statement
- [ ] Create ThemeService::getBackgroundImage() method to eliminate duplication
- [ ] Remove direct Database::query() calls from ItemController and WishlistController
- [ ] Create StringHelper utility class and consolidate generateRandomString() methods
- [ ] Remove debug error_log statements from production code
- [ ] Add missing return type hints throughout services and models
- [ ] Remove unused constructor dependencies (e.g., HomeController::wishlistService)
- [ ] Consolidate getBaseUploadPath() into single location in FileUploadService
- [ ] Create dedicated Request validation classes to extract validation from controllers
- [ ] Move business logic from controllers to appropriate services
- [ ] Refactor WishlistService to use models consistently instead of raw SQL
- [ ] Create SessionManager service to centralize session handling
- [ ] Create comprehensive MVC Architecture Guide document
- [ ] Review and add appropriate inline documentation (avoid over-documenting)