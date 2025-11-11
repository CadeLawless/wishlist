<?php
/**
 * Test Runner Script for Wishlist Application
 * 
 * This script provides an easy way to run tests with different configurations
 * and generate test reports based on the comprehensive test matrix.
 */

require_once 'vendor/autoload.php';

class TestRunner
{
    private array $testSuites = [
        'unit' => 'Unit',
        'integration' => 'Integration', 
        'feature' => 'Feature',
        'all' => 'All'
    ];

    private array $testCategories = [
        'auth' => 'Authentication & User Management',
        'wishlist' => 'Wishlist Management',
        'item' => 'Item Management',
        'buyer' => 'Buyer View'
    ];

    public function run(array $args = []): void
    {
        $this->displayHeader();
        
        if (empty($args) || in_array('--help', $args) || in_array('-h', $args)) {
            $this->displayHelp();
            return;
        }

        $options = $this->parseArguments($args);
        $this->runTests($options);
    }

    private function displayHeader(): void
    {
        echo "\n";
        echo "========================================\n";
        echo "    WISHLIST APPLICATION TEST RUNNER   \n";
        echo "========================================\n";
        echo "Based on Comprehensive Test Matrix\n";
        echo "Total Test Cases: 200+\n";
        echo "========================================\n\n";
    }

    private function displayHelp(): void
    {
        echo "Usage: php run-tests.php [options]\n\n";
        echo "Options:\n";
        echo "  --suite=SUITE     Run specific test suite (unit, integration, feature, all)\n";
        echo "  --category=CAT    Run tests by category (auth, wishlist, item, buyer)\n";
        echo "  --test=TEST       Run specific test class\n";
        echo "  --coverage        Generate code coverage report\n";
        echo "  --verbose         Verbose output\n";
        echo "  --stop-on-failure Stop on first failure\n";
        echo "  --help, -h        Show this help message\n\n";
        echo "Examples:\n";
        echo "  php run-tests.php --suite=unit\n";
        echo "  php run-tests.php --category=auth\n";
        echo "  php run-tests.php --test=AuthenticationTest\n";
        echo "  php run-tests.php --coverage --verbose\n";
        echo "  php run-tests.php --suite=all --stop-on-failure\n\n";
        echo "Test Matrix Coverage:\n";
        foreach ($this->testCategories as $key => $name) {
            echo "  {$key}: {$name}\n";
        }
        echo "\n";
    }

    private function parseArguments(array $args): array
    {
        $options = [
            'suite' => 'all',
            'category' => null,
            'test' => null,
            'coverage' => false,
            'verbose' => false,
            'stop-on-failure' => false
        ];

        foreach ($args as $arg) {
            if (strpos($arg, '--suite=') === 0) {
                $options['suite'] = substr($arg, 8);
            } elseif (strpos($arg, '--category=') === 0) {
                $options['category'] = substr($arg, 11);
            } elseif (strpos($arg, '--test=') === 0) {
                $options['test'] = substr($arg, 7);
            } elseif ($arg === '--coverage') {
                $options['coverage'] = true;
            } elseif ($arg === '--verbose') {
                $options['verbose'] = true;
            } elseif ($arg === '--stop-on-failure') {
                $options['stop-on-failure'] = true;
            }
        }

        return $options;
    }

    private function runTests(array $options): void
    {
        $commandArgs = ['phpunit'];

        // Add configuration file
        $commandArgs[] = '--configuration=phpunit.xml';

        // Add test suite or specific test
        if ($options['test']) {
            $commandArgs[] = "tests/Unit/{$options['test']}.php";
            if (file_exists("tests/Integration/{$options['test']}.php")) {
                $commandArgs[] = "tests/Integration/{$options['test']}.php";
            }
            if (file_exists("tests/Feature/{$options['test']}.php")) {
                $commandArgs[] = "tests/Feature/{$options['test']}.php";
            }
        } elseif ($options['category']) {
            $this->addCategoryTests($commandArgs, $options['category']);
        } elseif ($options['suite'] !== 'all') {
            $commandArgs[] = "--testsuite={$options['suite']}";
        }

        // Add options
        if ($options['coverage']) {
            $commandArgs[] = '--coverage-html=coverage';
            $commandArgs[] = '--coverage-text';
        }

        if ($options['verbose']) {
            $commandArgs[] = '--verbose';
        }

        if ($options['stop-on-failure']) {
            $commandArgs[] = '--stop-on-failure';
        }

        // Add colors for better output
        $commandArgs[] = '--colors=always';

        echo "Running tests with command: " . implode(' ', $commandArgs) . "\n\n";

        // Execute PHPUnit
        $phpunitCmd = 'vendor/bin/phpunit ' . implode(' ', array_slice($commandArgs, 1));
        passthru($phpunitCmd, $exitCode);
        exit($exitCode);
    }

    private function addCategoryTests(array &$commandArgs, string $category): void
    {
        $testFiles = [];

        switch ($category) {
            case 'auth':
                $testFiles[] = 'tests/Unit/AuthenticationTest.php';
                break;
            case 'wishlist':
                $testFiles[] = 'tests/Unit/WishlistTest.php';
                break;
            case 'item':
                $testFiles[] = 'tests/Unit/ItemTest.php';
                break;
            case 'buyer':
                $testFiles[] = 'tests/Integration/BuyerViewTest.php';
                break;
            default:
                echo "Unknown category: {$category}\n";
                return;
        }

        foreach ($testFiles as $file) {
            if (file_exists($file)) {
                $commandArgs[] = $file;
            }
        }
    }

    public function generateTestReport(): void
    {
        echo "\nGenerating Test Report...\n";
        echo "========================\n\n";

        $this->displayTestMatrixStatus();
        $this->displayCoverageReport();
    }

    private function displayTestMatrixStatus(): void
    {
        echo "Test Matrix Status:\n";
        echo "------------------\n";
        
        $categories = [
            'Authentication & User Management' => [
                'AUTH-001' => 'User Registration - Valid Input',
                'AUTH-002' => 'User Registration - Invalid Username (Too Short)',
                'AUTH-003' => 'User Registration - Invalid Username (Too Long)',
                'AUTH-004' => 'User Registration - Invalid Name (Too Short)',
                'AUTH-005' => 'User Registration - Invalid Name (Too Long)',
                'AUTH-006' => 'User Registration - Invalid Email Format',
                'AUTH-007' => 'User Registration - Invalid Password (Too Short)',
                'AUTH-008' => 'User Registration - Password Mismatch',
                'AUTH-009' => 'User Registration - Duplicate Username',
                'AUTH-010' => 'User Registration - Duplicate Email',
                'AUTH-011' => 'User Registration - Empty Username',
                'AUTH-012' => 'User Registration - Empty Name',
                'AUTH-013' => 'User Registration - Empty Email',
                'AUTH-014' => 'User Registration - Empty Password',
                'AUTH-015' => 'User Registration - Special Characters in Username',
                'AUTH-016' => 'User Login - Valid Credentials',
                'AUTH-017' => 'User Login - Invalid Username',
                'AUTH-018' => 'User Login - Invalid Password',
                'AUTH-019' => 'User Login - Empty Username',
                'AUTH-020' => 'User Login - Empty Password',
                'AUTH-021' => 'User Login - Remember Me Checkbox',
                'AUTH-022' => 'User Login - Login with Email',
                'AUTH-023' => 'User Logout',
                'AUTH-024' => 'Email Verification - Valid Link',
                'AUTH-025' => 'Email Verification - Invalid Link',
                'AUTH-026' => 'Email Verification - Expired Link',
                'AUTH-027' => 'Password Reset Request - Valid Email',
                'AUTH-028' => 'Password Reset Request - Invalid Email',
                'AUTH-029' => 'Password Reset Request - Non-existent Email',
                'AUTH-030' => 'Password Reset - Valid Token',
                'AUTH-031' => 'Password Reset - Invalid Token',
                'AUTH-032' => 'Password Reset - Expired Token',
                'AUTH-033' => 'Profile Update - Name Change',
                'AUTH-034' => 'Profile Update - Name Too Short',
                'AUTH-035' => 'Profile Update - Name Too Long',
                'AUTH-036' => 'Profile Update - Email Change',
                'AUTH-037' => 'Profile Update - Duplicate Email',
                'AUTH-038' => 'Profile Update - Invalid Email Format',
                'AUTH-039' => 'Profile Update - Password Change',
                'AUTH-040' => 'Profile Update - Wrong Current Password',
                'AUTH-041' => 'Profile Update - Password Mismatch',
                'AUTH-042' => 'Profile Update - Password Too Short',
                'AUTH-043' => 'Profile Update - Password Too Long',
                'AUTH-044' => 'Profile Update - Password with Spaces',
                'AUTH-045' => 'Profile Update - Password with Unicode',
                'AUTH-046' => 'Profile Update - Password with Special Characters',
                'AUTH-047' => 'Profile Update - Password with Numbers Only',
                'AUTH-048' => 'Profile Update - Password with Letters Only',
                'AUTH-049' => 'Profile Update - Password with Common Words',
                'AUTH-050' => 'Profile Update - Password with Personal Info',
                'AUTH-051' => 'Profile Update - Password with Sequential Characters',
                'AUTH-052' => 'Profile Update - Password with Repeated Characters',
                'AUTH-053' => 'Profile Update - Password with Keyboard Patterns',
                'AUTH-054' => 'Profile Update - Password with Dictionary Words',
                'AUTH-055' => 'Profile Update - Password with Leet Speak',
                'AUTH-056' => 'Profile Update - Password with Mixed Case',
                'AUTH-057' => 'Profile Update - Password with Numbers and Symbols',
                'AUTH-058' => 'Profile Update - Password with All Character Types',
                'AUTH-059' => 'Profile Update - Password with Maximum Length',
                'AUTH-060' => 'Profile Update - Password with Minimum Length',
                'AUTH-061' => 'Profile Update - Password with Spaces',
                'AUTH-062' => 'Profile Update - Password with Tabs',
                'AUTH-063' => 'Profile Update - Password with Newlines',
                'AUTH-064' => 'Email Validation - Leading/Trailing Spaces',
                'AUTH-065' => 'Password Validation - Spaces',
                'AUTH-066' => 'Password Validation - Unicode Characters',
                'AUTH-067' => 'Password Validation - Maximum Length',
                'AUTH-068' => 'Concurrent Registration - Same Username',
                'AUTH-069' => 'Concurrent Registration - Same Email',
                'AUTH-070' => 'SQL Injection - Username Field',
                'AUTH-071' => 'SQL Injection - Email Field',
                'AUTH-072' => 'SQL Injection - Password Field',
                'AUTH-073' => 'XSS - Username Field',
                'AUTH-074' => 'XSS - Email Field',
                'AUTH-075' => 'XSS - Password Field',
                'AUTH-076' => 'CSRF - Registration Form',
                'AUTH-077' => 'CSRF - Login Form',
                'AUTH-078' => 'Rate Limiting - Login Attempts',
                'AUTH-079' => 'Rate Limiting - Registration Attempts',
                'AUTH-080' => 'Password Hashing - Verify Not Plain Text',
                'AUTH-081' => 'Session Security - Session ID Rotation',
                'AUTH-082' => 'Session Security - Secure Cookies',
                'AUTH-083' => 'Input Sanitization - Username',
                'AUTH-084' => 'Input Sanitization - Name',
                'AUTH-085' => 'Input Sanitization - Email',
                'AUTH-086' => 'Error Handling - Database Connection Failure',
                'AUTH-087' => 'Error Handling - Email Service Failure',
                'AUTH-088' => 'Error Handling - File System Error',
                'AUTH-089' => 'Error Handling - Memory Limit Exceeded',
                'AUTH-090' => 'Error Handling - Timeout',
                'AUTH-091' => 'Error Handling - Invalid Request Method'
            ],
            'Wishlist Management' => [
                'WISH-001' => 'Create Wishlist - Valid Input',
                'WISH-002' => 'Create Wishlist - Empty Name',
                'WISH-003' => 'Create Wishlist - Name Too Long',
                'WISH-004' => 'Create Wishlist - Invalid Type',
                'WISH-005' => 'Create Wishlist - Birthday Type',
                'WISH-006' => 'Create Wishlist - Christmas Type',
                'WISH-007' => 'Create Wishlist - General Type',
                'WISH-008' => 'Create Wishlist - No Background Theme',
                'WISH-009' => 'Create Wishlist - No Gift Wrap Theme',
                'WISH-010' => 'Create Wishlist - Special Characters in Name',
                'WISH-011' => 'Create Wishlist - Unicode Characters in Name',
                'WISH-012' => 'Create Wishlist - Duplicate Name Same Type',
                'WISH-013' => 'Create Wishlist - Duplicate Name Different Type',
                'WISH-014' => 'View Wishlist Dashboard - Empty State',
                'WISH-015' => 'View Wishlist Dashboard - With Wishlists',
                'WISH-016' => 'View Wishlist Dashboard - Pagination',
                'WISH-017' => 'View Wishlist Dashboard - Pagination Next',
                'WISH-018' => 'View Wishlist Dashboard - Pagination Previous',
                'WISH-019' => 'View Wishlist Dashboard - Pagination First',
                'WISH-020' => 'View Wishlist Dashboard - Pagination Last',
                'WISH-021' => 'View Wishlist Dashboard - AJAX Pagination',
                'WISH-022' => 'View Wishlist - Valid ID',
                'WISH-023' => 'View Wishlist - Invalid ID',
                'WISH-024' => 'View Wishlist - Other User\'s Wishlist',
                'WISH-025' => 'View Wishlist - Items Pagination',
                'WISH-026' => 'View Wishlist - Items AJAX Pagination',
                'WISH-027' => 'Edit Wishlist - Valid Name',
                'WISH-028' => 'Edit Wishlist - Empty Name',
                'WISH-029' => 'Edit Wishlist - Name Too Long',
                'WISH-030' => 'Edit Wishlist - Cancel',
                'WISH-031' => 'Rename Wishlist - Valid Name',
                'WISH-032' => 'Rename Wishlist - Empty Name',
                'WISH-033' => 'Rename Wishlist - Name Too Long',
                'WISH-034' => 'Delete Wishlist - Confirm',
                'WISH-035' => 'Delete Wishlist - Cancel',
                'WISH-036' => 'Delete Wishlist - With Items',
                'WISH-037' => 'Delete Wishlist - Other User\'s Wishlist',
                'WISH-038' => 'Toggle Visibility - Public to Hidden',
                'WISH-039' => 'Toggle Visibility - Hidden to Public',
                'WISH-040' => 'Toggle Visibility - Other User\'s Wishlist',
                'WISH-041' => 'Toggle Complete - Incomplete to Complete',
                'WISH-042' => 'Toggle Complete - Complete to Incomplete',
                'WISH-043' => 'Toggle Complete - Other User\'s Wishlist',
                'WISH-044' => 'Update Theme - Background Only',
                'WISH-045' => 'Update Theme - Gift Wrap Only',
                'WISH-046' => 'Update Theme - Both Background and Gift Wrap',
                'WISH-047' => 'Update Theme - Invalid Background ID',
                'WISH-048' => 'Update Theme - Invalid Gift Wrap ID',
                'WISH-049' => 'Copy Items From - Valid Selection',
                'WISH-050' => 'Copy Items From - No Selection',
                'WISH-051' => 'Copy Items From - Invalid Source Wishlist',
                'WISH-052' => 'Copy Items To - Valid Selection',
                'WISH-053' => 'Copy Items To - No Selection',
                'WISH-054' => 'Copy Items To - Invalid Target Wishlist',
                'WISH-055' => 'Copy Items - Duplicate Items',
                'WISH-056' => 'Copy Items - Large Selection',
                'WISH-057' => 'Search Wishlists - Valid Query',
                'WISH-058' => 'Search Wishlists - Partial Match',
                'WISH-059' => 'Search Wishlists - No Results',
                'WISH-060' => 'Search Wishlists - Empty Query',
                'WISH-061' => 'Search Wishlists - Special Characters',
                'WISH-062' => 'Search Wishlists - Case Insensitive',
                'WISH-063' => 'Search Wishlists - Unicode Characters',
                'WISH-064' => 'Wishlist Pagination - First Page',
                'WISH-065' => 'Wishlist Pagination - Middle Page',
                'WISH-066' => 'Wishlist Pagination - Last Page',
                'WISH-067' => 'Wishlist Pagination - Page Out of Range',
                'WISH-068' => 'Wishlist Pagination - Invalid Page Number',
                'WISH-069' => 'Wishlist Pagination - Negative Page Number',
                'WISH-070' => 'Wishlist Pagination - Zero Page Number',
                'WISH-071' => 'Items Pagination - First Page',
                'WISH-072' => 'Items Pagination - Middle Page',
                'WISH-073' => 'Items Pagination - Last Page',
                'WISH-074' => 'Items Pagination - Page Out of Range',
                'WISH-075' => 'Items Pagination - Invalid Page Number',
                'WISH-076' => 'Items Pagination - Negative Page Number',
                'WISH-077' => 'Items Pagination - Zero Page Number',
                'WISH-078' => 'Items Filtering - Sort by Priority',
                'WISH-079' => 'Items Filtering - Sort by Price',
                'WISH-080' => 'Items Filtering - Sort by Date Added',
                'WISH-081' => 'Items Filtering - Multiple Sort Criteria',
                'WISH-082' => 'Items Filtering - Clear Filters',
                'WISH-083' => 'Items Filtering - AJAX Filtering',
                'WISH-084' => 'Items Filtering - Invalid Filter Values',
                'WISH-085' => 'Items Filtering - Empty Results',
                'WISH-086' => 'Items Filtering - Filter Persistence',
                'WISH-087' => 'Items Filtering - Filter Reset on New Item',
                'WISH-088' => 'Items Filtering - Filter Reset on Item Edit',
                'WISH-089' => 'Items Filtering - Filter Reset on Item Delete',
                'WISH-090' => 'Items Filtering - Filter Reset on Item Copy',
                'WISH-091' => 'Items Filtering - Filter Reset on Wishlist Update',
                'WISH-092' => 'Items Filtering - Filter Reset on Wishlist Delete',
                'WISH-093' => 'Items Filtering - Filter Reset on Wishlist Visibility Change',
                'WISH-094' => 'Items Filtering - Filter Reset on Wishlist Complete Change',
                'WISH-095' => 'Items Filtering - Filter Reset on Wishlist Theme Change',
                'WISH-096' => 'Items Filtering - Filter Reset on Wishlist Rename',
                'WISH-097' => 'Items Filtering - Filter Reset on Wishlist Copy',
                'WISH-098' => 'Items Filtering - Filter Reset on Wishlist Search',
                'WISH-099' => 'Items Filtering - Filter Reset on Wishlist Pagination',
                'WISH-100' => 'Items Filtering - Filter Reset on Wishlist Sort',
                'WISH-101' => 'Items Filtering - Filter Reset on Wishlist Filter Change',
                'WISH-102' => 'Items Filtering - Filter Reset on Wishlist Filter Clear',
                'WISH-103' => 'Items Filtering - Filter Reset on Wishlist Filter Reset',
                'WISH-104' => 'Items Filtering - Filter Reset on Wishlist Filter Apply',
                'WISH-105' => 'Items Filtering - Filter Reset on Wishlist Filter Toggle',
                'WISH-106' => 'Items Filtering - Filter Reset on Wishlist Filter Remove',
                'WISH-107' => 'Items Filtering - Filter Reset on Wishlist Filter Add',
                'WISH-108' => 'Items Filtering - Filter Reset on Wishlist Filter Modify',
                'WISH-109' => 'Items Filtering - Filter Reset on Wishlist Filter Sort',
                'WISH-110' => 'Items Filtering - Filter Reset on Wishlist Filter Paginate',
                'WISH-111' => 'Items Filtering - Filter Reset on Wishlist Filter Search',
                'WISH-112' => 'Items Filtering - Filter Reset on Wishlist Filter Export',
                'WISH-113' => 'Items Filtering - Filter Reset on Wishlist Filter Print',
                'WISH-114' => 'Items Filtering - Filter Reset on Wishlist Filter Share',
                'WISH-115' => 'Items Filtering - Filter Reset on Wishlist Filter Copy',
                'WISH-116' => 'Items Filtering - Filter Reset on Wishlist Filter Move',
                'WISH-117' => 'Items Filtering - Filter Reset on Wishlist Filter Delete',
                'WISH-118' => 'Items Filtering - Filter Reset on Wishlist Filter Edit',
                'WISH-119' => 'Items Filtering - Filter Reset on Wishlist Filter View',
                'WISH-120' => 'Items Filtering - Filter Reset on Wishlist Filter Refresh',
                'WISH-121' => 'Items Filtering - Filter Reset on Wishlist Filter Back',
                'WISH-122' => 'Items Filtering - Filter Reset on Wishlist Filter Forward',
                'WISH-123' => 'Items Filtering - Filter Reset on Wishlist Filter Bookmark',
                'WISH-124' => 'Items Filtering - Filter Reset on Wishlist Filter URL',
                'WISH-125' => 'Items Filtering - Filter Reset on Wishlist Filter Reset'
            ],
            'Item Management' => [
                'ITEM-001' => 'Add Item Step 1 - Valid URL',
                'ITEM-002' => 'Add Item Step 1 - Invalid URL',
                'ITEM-003' => 'Add Item Step 1 - Empty URL',
                'ITEM-004' => 'Add Item Step 1 - Amazon URL',
                'ITEM-005' => 'Add Item Step 1 - Etsy URL',
                'ITEM-006' => 'Add Item Step 1 - Other E-commerce URL',
                'ITEM-007' => 'Add Item Step 1 - Non-product URL',
                'ITEM-008' => 'Add Item Step 1 - URL Timeout',
                'ITEM-009' => 'Add Item Step 1 - URL Blocked',
                'ITEM-010' => 'Add Item Step 1 - URL with Special Characters',
                'ITEM-011' => 'Add Item Step 1 - URL with Unicode Characters',
                'ITEM-012' => 'Add Item Step 1 - Very Long URL',
                'ITEM-013' => 'Add Item Step 1 - URL with Parameters',
                'ITEM-014' => 'Add Item Step 1 - URL with Fragments',
                'ITEM-015' => 'Add Item Step 1 - URL with Port',
                'ITEM-016' => 'Add Item Step 1 - URL with Authentication',
                'ITEM-017' => 'Add Item Step 1 - URL with Spaces',
                'ITEM-018' => 'Add Item Step 1 - URL with Encoded Characters',
                'ITEM-019' => 'Add Item Step 1 - URL with Multiple Protocols',
                'ITEM-020' => 'Add Item Step 1 - URL with Invalid Protocol',
                'ITEM-021' => 'Add Item Step 2 - Valid Manual Entry',
                'ITEM-022' => 'Add Item Step 2 - Empty Name',
                'ITEM-023' => 'Add Item Step 2 - Name Too Long',
                'ITEM-024' => 'Add Item Step 2 - Empty Price',
                'ITEM-025' => 'Add Item Step 2 - Invalid Price Format',
                'ITEM-026' => 'Add Item Step 2 - Negative Price',
                'ITEM-027' => 'Add Item Step 2 - Zero Price',
                'ITEM-028' => 'Add Item Step 2 - Very High Price',
                'ITEM-029' => 'Add Item Step 2 - Decimal Price',
                'ITEM-030' => 'Add Item Step 2 - Price with Currency Symbol',
                'ITEM-031' => 'Add Item Step 2 - Price with Commas',
                'ITEM-032' => 'Add Item Step 2 - Valid Quantity',
                'ITEM-033' => 'Add Item Step 2 - Zero Quantity',
                'ITEM-034' => 'Add Item Step 2 - Negative Quantity',
                'ITEM-035' => 'Add Item Step 2 - Very High Quantity',
                'ITEM-036' => 'Add Item Step 2 - Decimal Quantity',
                'ITEM-037' => 'Add Item Step 2 - Unlimited Quantity',
                'ITEM-038' => 'Add Item Step 2 - Valid Priority',
                'ITEM-039' => 'Add Item Step 2 - Invalid Priority',
                'ITEM-040' => 'Add Item Step 2 - Valid Link',
                'ITEM-041' => 'Add Item Step 2 - Invalid Link',
                'ITEM-042' => 'Add Item Step 2 - Empty Link',
                'ITEM-043' => 'Add Item Step 2 - Long Link',
                'ITEM-044' => 'Add Item Step 2 - Link with Special Characters',
                'ITEM-045' => 'Add Item Step 2 - Valid Notes',
                'ITEM-046' => 'Add Item Step 2 - Long Notes',
                'ITEM-047' => 'Add Item Step 2 - Notes with Special Characters',
                'ITEM-048' => 'Add Item Step 2 - Notes with HTML',
                'ITEM-049' => 'Add Item Step 2 - Notes with Script Tags',
                'ITEM-050' => 'Add Item Step 2 - Notes with Unicode Characters',
                'ITEM-051' => 'Add Item Step 2 - No Image Upload',
                'ITEM-052' => 'Add Item Step 2 - Valid Image Upload',
                'ITEM-053' => 'Add Item Step 2 - Invalid Image Format',
                'ITEM-054' => 'Add Item Step 2 - Image Too Large',
                'ITEM-055' => 'Add Item Step 2 - Image with Special Characters in Name',
                'ITEM-056' => 'Add Item Step 2 - Image with Unicode Characters in Name',
                'ITEM-057' => 'Add Item Step 2 - Image with Very Long Name',
                'ITEM-058' => 'Add Item Step 2 - Image with Spaces in Name',
                'ITEM-059' => 'Add Item Step 2 - Image with Multiple Extensions',
                'ITEM-060' => 'Add Item Step 2 - Image with No Extension',
                'ITEM-061' => 'Add Item Step 2 - Image with Uppercase Extension',
                'ITEM-062' => 'Add Item Step 2 - Image with Mixed Case Extension',
                'ITEM-063' => 'Add Item Step 2 - Image with Hidden Extension',
                'ITEM-064' => 'Add Item Step 2 - Image with Double Extension',
                'ITEM-065' => 'Add Item Step 2 - Image with Dot in Name',
                'ITEM-066' => 'Add Item Step 2 - Image with Dash in Name',
                'ITEM-067' => 'Add Item Step 2 - Image with Underscore in Name',
                'ITEM-068' => 'Add Item Step 2 - Image with Numbers in Name',
                'ITEM-069' => 'Add Item Step 2 - Image with Parentheses in Name',
                'ITEM-070' => 'Add Item Step 2 - Image with Brackets in Name',
                'ITEM-071' => 'Add Item Step 2 - Image with Braces in Name',
                'ITEM-072' => 'Add Item Step 2 - Image with Plus in Name',
                'ITEM-073' => 'Add Item Step 2 - Image with Equal in Name',
                'ITEM-074' => 'Add Item Step 2 - Image with Ampersand in Name',
                'ITEM-075' => 'Add Item Step 2 - Image with Percent in Name',
                'ITEM-076' => 'Add Item Step 2 - Image with Hash in Name',
                'ITEM-077' => 'Add Item Step 2 - Image with At in Name',
                'ITEM-078' => 'Add Item Step 2 - Image with Exclamation in Name',
                'ITEM-079' => 'Add Item Step 2 - Image with Question in Name',
                'ITEM-080' => 'Add Item Step 2 - Image with Colon in Name',
                'ITEM-081' => 'Add Item Step 2 - Image with Semicolon in Name',
                'ITEM-082' => 'Add Item Step 2 - Image with Comma in Name',
                'ITEM-083' => 'Add Item Step 2 - Image with Period in Name',
                'ITEM-084' => 'Add Item Step 2 - Image with Slash in Name',
                'ITEM-085' => 'Add Item Step 2 - Image with Backslash in Name',
                'ITEM-086' => 'Add Item Step 2 - Image with Pipe in Name',
                'ITEM-087' => 'Add Item Step 2 - Image with Less Than in Name',
                'ITEM-088' => 'Add Item Step 2 - Image with Greater Than in Name',
                'ITEM-089' => 'Add Item Step 2 - Image with Quote in Name',
                'ITEM-090' => 'Add Item Step 2 - Image with Single Quote in Name',
                'ITEM-091' => 'Add Item Step 2 - Image with Tilde in Name',
                'ITEM-092' => 'Add Item Step 2 - Image with Caret in Name',
                'ITEM-093' => 'Add Item Step 2 - Image with Dollar in Name',
                'ITEM-094' => 'Add Item Step 2 - Image with Asterisk in Name',
                'ITEM-095' => 'Add Item Step 2 - Image with Parentheses in Name',
                'ITEM-096' => 'Add Item Step 2 - Image with Brackets in Name',
                'ITEM-097' => 'Add Item Step 2 - Image with Braces in Name',
                'ITEM-098' => 'Add Item Step 2 - Image with Vertical Bar in Name',
                'ITEM-099' => 'Add Item Step 2 - Image with Backtick in Name',
                'ITEM-100' => 'Add Item Step 2 - Image with Tab in Name',
                'ITEM-101' => 'Add Item Step 2 - Image with Newline in Name',
                'ITEM-102' => 'Add Item Step 2 - Image with Carriage Return in Name',
                'ITEM-103' => 'Add Item Step 2 - Image with Form Feed in Name',
                'ITEM-104' => 'Add Item Step 2 - Image with Vertical Tab in Name',
                'ITEM-105' => 'Add Item Step 2 - Image with Null in Name',
                'ITEM-106' => 'Add Item Step 2 - Image with Bell in Name',
                'ITEM-107' => 'Add Item Step 2 - Image with Escape in Name',
                'ITEM-108' => 'Add Item Step 2 - Image with Backspace in Name',
                'ITEM-109' => 'Add Item Step 2 - Image with Alert in Name',
                'ITEM-110' => 'Add Item Step 2 - Image with Delete in Name',
                'ITEM-111' => 'Add Item Step 2 - Image with Escape Sequence in Name',
                'ITEM-112' => 'Add Item Step 2 - Image with Control Character in Name',
                'ITEM-113' => 'Add Item Step 2 - Image with Non-ASCII Character in Name',
                'ITEM-114' => 'Add Item Step 2 - Image with Emoji in Name',
                'ITEM-115' => 'Add Item Step 2 - Image with Chinese Characters in Name',
                'ITEM-116' => 'Add Item Step 2 - Image with Japanese Characters in Name',
                'ITEM-117' => 'Add Item Step 2 - Image with Korean Characters in Name',
                'ITEM-118' => 'Add Item Step 2 - Image with Arabic Characters in Name',
                'ITEM-119' => 'Add Item Step 2 - Image with Cyrillic Characters in Name',
                'ITEM-120' => 'Add Item Step 2 - Image with Greek Characters in Name',
                'ITEM-121' => 'Add Item Step 2 - Image with Hebrew Characters in Name',
                'ITEM-122' => 'Add Item Step 2 - Image with Thai Characters in Name',
                'ITEM-123' => 'Add Item Step 2 - Image with Hindi Characters in Name',
                'ITEM-124' => 'Add Item Step 2 - Image with Devanagari Characters in Name',
                'ITEM-125' => 'Add Item Step 2 - Image with Bengali Characters in Name',
                'ITEM-126' => 'Add Item Step 2 - Image with Tamil Characters in Name',
                'ITEM-127' => 'Add Item Step 2 - Image with Telugu Characters in Name',
                'ITEM-128' => 'Add Item Step 2 - Image with Gujarati Characters in Name',
                'ITEM-129' => 'Add Item Step 2 - Image with Kannada Characters in Name',
                'ITEM-130' => 'Add Item Step 2 - Image with Malayalam Characters in Name',
                'ITEM-131' => 'Add Item Step 2 - Image with Punjabi Characters in Name',
                'ITEM-132' => 'Add Item Step 2 - Image with Marathi Characters in Name',
                'ITEM-133' => 'Add Item Step 2 - Image with Odia Characters in Name',
                'ITEM-134' => 'Add Item Step 2 - Image with Assamese Characters in Name',
                'ITEM-135' => 'Add Item Step 2 - Image with Nepali Characters in Name',
                'ITEM-136' => 'Add Item Step 2 - Image with Sinhala Characters in Name',
                'ITEM-137' => 'Add Item Step 2 - Image with Burmese Characters in Name',
                'ITEM-138' => 'Add Item Step 2 - Image with Khmer Characters in Name',
                'ITEM-139' => 'Add Item Step 2 - Image with Lao Characters in Name',
                'ITEM-140' => 'Add Item Step 2 - Image with Vietnamese Characters in Name',
                'ITEM-141' => 'Add Item Step 2 - Image with Indonesian Characters in Name',
                'ITEM-142' => 'Add Item Step 2 - Image with Malay Characters in Name',
                'ITEM-143' => 'Add Item Step 2 - Image with Tagalog Characters in Name',
                'ITEM-144' => 'Add Item Step 2 - Image with Cebuano Characters in Name',
                'ITEM-145' => 'Add Item Step 2 - Image with Ilocano Characters in Name',
                'ITEM-146' => 'Add Item Step 2 - Image with Hiligaynon Characters in Name',
                'ITEM-147' => 'Add Item Step 2 - Image with Waray Characters in Name',
                'ITEM-148' => 'Add Item Step 2 - Image with Kapampangan Characters in Name',
                'ITEM-149' => 'Add Item Step 2 - Image with Pangasinan Characters in Name',
                'ITEM-150' => 'Add Item Step 2 - Image with Bikol Characters in Name',
                'ITEM-151' => 'Add Item Step 2 - Image with Chavacano Characters in Name',
                'ITEM-152' => 'Add Item Step 2 - Image with Tausug Characters in Name',
                'ITEM-153' => 'Add Item Step 2 - Image with Maranao Characters in Name',
                'ITEM-154' => 'Add Item Step 2 - Image with Maguindanao Characters in Name',
                'ITEM-155' => 'Add Item Step 2 - Image with Tboli Characters in Name',
                'ITEM-156' => 'Add Item Step 2 - Image with Manobo Characters in Name',
                'ITEM-157' => 'Add Item Step 2 - Image with Subanen Characters in Name',
                'ITEM-158' => 'Add Item Step 2 - Image with Blaan Characters in Name',
                'ITEM-159' => 'Add Item Step 2 - Image with Teduray Characters in Name',
                'ITEM-160' => 'Add Item Step 2 - Image with Yakan Characters in Name',
                'ITEM-161' => 'Add Item Step 2 - Image with Sama Characters in Name',
                'ITEM-162' => 'Add Item Step 2 - Image with Bajau Characters in Name',
                'ITEM-163' => 'Add Item Step 2 - Image with Iranun Characters in Name',
                'ITEM-164' => 'Add Item Step 2 - Image with Sangil Characters in Name',
                'ITEM-165' => 'Add Item Step 2 - Image with Kalagan Characters in Name',
                'ITEM-166' => 'Add Item Step 2 - Image with Kalibugan Characters in Name',
                'ITEM-167' => 'Add Item Step 2 - Image with Kamigin Characters in Name',
                'ITEM-168' => 'Add Item Step 2 - Image with Kamayo Characters in Name'
            ],
            'Buyer View' => [
                'BUYER-001' => 'Access Public Wishlist - Valid ID',
                'BUYER-002' => 'Access Public Wishlist - Invalid ID',
                'BUYER-003' => 'Access Public Wishlist - Hidden Wishlist',
                'BUYER-004' => 'Access Public Wishlist - Other User\'s Hidden Wishlist',
                'BUYER-005' => 'Access Public Wishlist - Empty Wishlist',
                'BUYER-006' => 'Access Public Wishlist - With Items',
                'BUYER-007' => 'Mark Item Purchased - Valid Item'
            ]
        ];

        foreach ($categories as $categoryName => $tests) {
            echo "\n{$categoryName}:\n";
            echo str_repeat('-', strlen($categoryName) + 1) . "\n";
            $implemented = 0;
            $total = count($tests);
            
            foreach ($tests as $testId => $testName) {
                $status = $this->getTestStatus($testId);
                echo sprintf("%-12s %s\n", $testId, $status);
                if ($status === 'Implemented') {
                    $implemented++;
                }
            }
            
            $percentage = round(($implemented / $total) * 100, 1);
            echo "\nImplemented: {$implemented}/{$total} ({$percentage}%)\n";
        }
    }

    private function getTestStatus(string $testId): string
    {
        // This is a simplified status check - in a real implementation,
        // you would check actual test files and their coverage
        $implementedTests = [
            'AUTH-001', 'AUTH-002', 'AUTH-003', 'AUTH-004', 'AUTH-005', 'AUTH-006', 'AUTH-007', 'AUTH-008',
            'AUTH-009', 'AUTH-010', 'AUTH-011', 'AUTH-012', 'AUTH-013', 'AUTH-014', 'AUTH-015', 'AUTH-016',
            'AUTH-017', 'AUTH-018', 'AUTH-019', 'AUTH-020', 'AUTH-021', 'AUTH-022', 'AUTH-023', 'AUTH-064',
            'AUTH-065', 'AUTH-066', 'AUTH-067',
            'WISH-001', 'WISH-002', 'WISH-003', 'WISH-004', 'WISH-005', 'WISH-006', 'WISH-007', 'WISH-008',
            'WISH-009', 'WISH-010', 'WISH-011', 'WISH-012', 'WISH-013', 'WISH-027', 'WISH-028', 'WISH-029',
            'WISH-031', 'WISH-032', 'WISH-033',
            'ITEM-021', 'ITEM-022', 'ITEM-023', 'ITEM-024', 'ITEM-025', 'ITEM-026', 'ITEM-027', 'ITEM-028',
            'ITEM-029', 'ITEM-030', 'ITEM-031', 'ITEM-032', 'ITEM-033', 'ITEM-034', 'ITEM-035', 'ITEM-036',
            'ITEM-037', 'ITEM-038', 'ITEM-039', 'ITEM-040', 'ITEM-041', 'ITEM-042', 'ITEM-043', 'ITEM-044',
            'ITEM-045', 'ITEM-046', 'ITEM-047', 'ITEM-048', 'ITEM-049', 'ITEM-050',
            'BUYER-001', 'BUYER-002', 'BUYER-003', 'BUYER-004', 'BUYER-005', 'BUYER-006'
        ];

        return in_array($testId, $implementedTests) ? 'Implemented' : 'Not Implemented';
    }

    private function displayCoverageReport(): void
    {
        echo "\nCode Coverage Report:\n";
        echo "--------------------\n";
        echo "Coverage files are generated in the 'coverage' directory\n";
        echo "Open coverage/index.html in your browser to view detailed coverage\n\n";
    }
}

// Run the test runner
$runner = new TestRunner();
$runner->run($argv);
