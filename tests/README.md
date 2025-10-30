# Wishlist Application Test Suite

This directory contains comprehensive tests for the Wishlist application based on the detailed test matrix with 200+ test cases covering all major functionality.

## Test Structure

```
tests/
├── TestCase.php                 # Base test class with common utilities
├── Unit/                        # Unit tests for individual components
│   ├── AuthenticationTest.php   # Authentication & User Management tests
│   ├── WishlistTest.php         # Wishlist Management tests
│   └── ItemTest.php             # Item Management tests
├── Integration/                 # Integration tests
│   └── BuyerViewTest.php        # Buyer View functionality tests
├── Feature/                     # Feature tests (end-to-end)
└── README.md                    # This file
```

## Test Categories

### 1. Authentication & User Management (AUTH-001 to AUTH-091)
- **User Registration**: Valid input, validation errors, edge cases
- **User Login**: Credentials validation, session management
- **Email Verification**: Token validation, expiration handling
- **Password Reset**: Token generation, validation, security
- **Profile Management**: Updates, validation, security
- **Security Tests**: SQL injection, XSS, CSRF protection
- **Edge Cases**: Unicode characters, special characters, length limits

### 2. Wishlist Management (WISH-001 to WISH-125)
- **Create Wishlist**: Validation, themes, types
- **View Wishlist**: Dashboard, pagination, filtering
- **Edit Wishlist**: Name updates, theme changes
- **Delete Wishlist**: Confirmation, cascade deletion
- **Visibility Control**: Public/private toggle
- **Copy Operations**: Items between wishlists
- **Search & Filter**: Query handling, persistence
- **Pagination**: Page navigation, AJAX loading

### 3. Item Management (ITEM-001 to ITEM-168)
- **Add Items**: URL fetching, manual entry, validation
- **Item Validation**: Name, price, quantity, priority
- **Image Upload**: Format validation, size limits, special characters
- **Link Handling**: URL validation, special characters
- **Notes Processing**: HTML sanitization, length limits
- **Edge Cases**: Unicode filenames, special characters, very long inputs

### 4. Buyer View (BUYER-001 to BUYER-007)
- **Public Access**: Valid/invalid wishlist access
- **Item Display**: Pagination, sorting, filtering
- **Theme Display**: Background and gift wrap themes
- **Search Functionality**: Item search within wishlists

## Running Tests

### Quick Start
```bash
# Run all tests
php run-tests.php

# Run specific test suite
php run-tests.php --suite=unit
php run-tests.php --suite=integration
php run-tests.php --suite=feature

# Run by category
php run-tests.php --category=auth
php run-tests.php --category=wishlist
php run-tests.php --category=item
php run-tests.php --category=buyer

# Run specific test class
php run-tests.php --test=AuthenticationTest
php run-tests.php --test=WishlistTest
php run-tests.php --test=ItemTest
php run-tests.php --test=BuyerViewTest

# Generate coverage report
php run-tests.php --coverage

# Verbose output
php run-tests.php --verbose

# Stop on first failure
php run-tests.php --stop-on-failure
```

### Using PHPUnit Directly
```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit --testsuite=Unit
./vendor/bin/phpunit --testsuite=Integration
./vendor/bin/phpunit --testsuite=Feature

# Run specific test file
./vendor/bin/phpunit tests/Unit/AuthenticationTest.php

# Generate coverage report
./vendor/bin/phpunit --coverage-html=coverage

# Verbose output
./vendor/bin/phpunit --verbose
```

## Test Configuration

The test suite uses PHPUnit 11.5 with the following configuration:

- **Bootstrap**: `vendor/autoload.php`
- **Test Database**: In-memory SQLite for fast execution
- **Coverage**: HTML and text reports
- **Colors**: Enabled for better readability
- **Stop on Failure**: Configurable

## Test Data Management

### Base Test Class Features
- **Automatic Setup**: Creates test database and tables
- **Data Cleanup**: Clears test data after each test
- **Helper Methods**: Create test users, wishlists, and items
- **Validation Helpers**: Assert validation errors and success

### Test Data Creation
```php
// Create test user
$user = $this->createTestUser([
    'username' => 'testuser',
    'email' => 'test@example.com'
]);

// Create test wishlist
$wishlist = $this->createTestWishlist([
    'username' => 'testuser',
    'wishlist_name' => 'Test Wishlist',
    'type' => 'Birthday'
]);

// Create test item
$item = $this->createTestItem([
    'wishlist_id' => $wishlist['id'],
    'item_name' => 'Test Item',
    'price' => 19.99
]);
```

## Test Matrix Coverage

The test suite implements tests from the comprehensive test matrix:

| Category | Total Tests | Implemented | Coverage |
|----------|-------------|-------------|----------|
| Authentication | 91 | 27 | 29.7% |
| Wishlist Management | 125 | 12 | 9.6% |
| Item Management | 168 | 30 | 17.9% |
| Buyer View | 7 | 6 | 85.7% |
| **Total** | **391** | **75** | **19.2%** |

## Priority Levels

Tests are categorized by priority:
- **P0 (Critical)**: Smoke tests, security tests
- **P1 (High)**: Core functionality tests
- **P2 (Medium)**: Edge cases, error handling

## Test Types

- **Smoke Tests**: Basic functionality verification
- **Functional Tests**: Feature-specific validation
- **Edge Case Tests**: Boundary conditions, special characters
- **Security Tests**: SQL injection, XSS, CSRF protection
- **Error Handling Tests**: Graceful failure scenarios

## Continuous Integration

The test suite is designed to work with CI/CD pipelines:

```yaml
# Example GitHub Actions workflow
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.0'
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: php run-tests.php --coverage
```

## Coverage Reports

Coverage reports are generated in the `coverage/` directory:
- **HTML Report**: `coverage/index.html` - Interactive coverage report
- **Text Report**: Console output with coverage summary
- **Clover Report**: `coverage/clover.xml` - For CI integration

## Best Practices

### Writing Tests
1. **Descriptive Names**: Use clear, descriptive test method names
2. **Single Responsibility**: Each test should verify one specific behavior
3. **Arrange-Act-Assert**: Structure tests with clear sections
4. **Test Data**: Use the helper methods for consistent test data
5. **Assertions**: Use specific assertions for better error messages

### Test Organization
1. **Group Related Tests**: Use `@test` annotations and descriptive method names
2. **Test Categories**: Follow the test matrix categorization
3. **Edge Cases**: Include boundary conditions and error scenarios
4. **Security Tests**: Verify input validation and sanitization

### Maintenance
1. **Keep Tests Updated**: Update tests when requirements change
2. **Remove Obsolete Tests**: Clean up tests for removed features
3. **Performance**: Keep tests fast and efficient
4. **Documentation**: Update this README when adding new test categories

## Troubleshooting

### Common Issues

1. **Database Connection Errors**
   - Ensure SQLite is available
   - Check file permissions for test database

2. **Test Failures**
   - Check test data setup
   - Verify validation rules match test expectations
   - Review error messages for specific issues

3. **Coverage Issues**
   - Ensure all code paths are tested
   - Check for untested edge cases
   - Verify test data covers all scenarios

### Debug Mode
```bash
# Run with verbose output for debugging
php run-tests.php --verbose --test=AuthenticationTest
```

## Contributing

When adding new tests:

1. **Follow Naming Convention**: Use descriptive method names
2. **Add to Test Matrix**: Update the comprehensive test matrix
3. **Update Documentation**: Add new test cases to this README
4. **Test Coverage**: Ensure new features are properly tested
5. **Edge Cases**: Include boundary conditions and error scenarios

## Resources

- [PHPUnit Documentation](https://phpunit.readthedocs.io/)
- [Test-Driven Development](https://en.wikipedia.org/wiki/Test-driven_development)
- [Comprehensive Test Matrix](./comprehensive-test-matrix.tsv)
- [Application Documentation](../docs/MVC_ARCHITECTURE_GUIDE.md)
