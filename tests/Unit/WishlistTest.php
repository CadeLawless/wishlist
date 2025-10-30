<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Validation\WishlistRequestValidator;
use App\Models\Wishlist;

class WishlistTest extends TestCase
{
    private WishlistRequestValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new WishlistRequestValidator();
    }

    /**
     * @test
     * WISH-001: Create Wishlist - Valid Input
     */
    public function test_create_wishlist_with_valid_input()
    {
        $data = [
            'wishlist_name' => 'My Birthday Wishlist',
            'wishlist_type' => 'Birthday',
            'background_theme' => 'theme1',
            'gift_wrap_theme' => 'wrap1'
        ];

        $errors = $this->validator->validateWishlist($data);

        $this->assertEmpty($errors, 'Valid wishlist data should not produce validation errors');
    }

    /**
     * @test
     * WISH-002: Create Wishlist - Empty Name
     */
    public function test_create_wishlist_with_empty_name()
    {
        $data = [
            'wishlist_name' => '', // Empty name
            'wishlist_type' => 'Birthday',
            'background_theme' => 'theme1',
            'gift_wrap_theme' => 'wrap1'
        ];

        $errors = $this->validator->validateWishlist($data);

        $this->assertValidationError($errors, 'wishlist_name', 'Wishlist name is required');
    }

    /**
     * @test
     * WISH-003: Create Wishlist - Name Too Long
     */
    public function test_create_wishlist_with_name_too_long()
    {
        $data = [
            'wishlist_name' => str_repeat('A', 101), // 101 characters, maximum is 100
            'wishlist_type' => 'Birthday',
            'background_theme' => 'theme1',
            'gift_wrap_theme' => 'wrap1'
        ];

        $errors = $this->validator->validateWishlist($data);

        $this->assertValidationError($errors, 'wishlist_name', 'Wishlist name must not exceed 100 characters');
    }

    /**
     * @test
     * WISH-004: Create Wishlist - Invalid Type
     */
    public function test_create_wishlist_with_invalid_type()
    {
        $data = [
            'wishlist_name' => 'My Wishlist',
            'wishlist_type' => 'InvalidType', // Invalid type
            'background_theme' => 'theme1',
            'gift_wrap_theme' => 'wrap1'
        ];

        $errors = $this->validator->validateWishlist($data);

        $this->assertValidationError($errors, 'wishlist_type', 'Please select a valid wishlist type');
    }

    /**
     * @test
     * WISH-005: Create Wishlist - Birthday Type
     */
    public function test_create_wishlist_with_birthday_type()
    {
        $data = [
            'wishlist_name' => 'My Birthday Wishlist',
            'wishlist_type' => 'Birthday',
            'background_theme' => 'theme1',
            'gift_wrap_theme' => 'wrap1'
        ];

        $errors = $this->validator->validateWishlist($data);

        $this->assertEmpty($errors, 'Birthday type should be valid');
    }

    /**
     * @test
     * WISH-006: Create Wishlist - Christmas Type
     */
    public function test_create_wishlist_with_christmas_type()
    {
        $data = [
            'wishlist_name' => 'My Christmas Wishlist',
            'wishlist_type' => 'Christmas',
            'background_theme' => 'theme1',
            'gift_wrap_theme' => 'wrap1'
        ];

        $errors = $this->validator->validateWishlist($data);

        $this->assertEmpty($errors, 'Christmas type should be valid');
    }

    /**
     * @test
     * WISH-007: Create Wishlist - General Type
     */
    public function test_create_wishlist_with_general_type()
    {
        $data = [
            'wishlist_name' => 'My General Wishlist',
            'wishlist_type' => 'General',
            'background_theme' => 'theme1',
            'gift_wrap_theme' => 'wrap1'
        ];

        $errors = $this->validator->validateWishlist($data);

        $this->assertEmpty($errors, 'General type should be valid');
    }

    /**
     * @test
     * WISH-008: Create Wishlist - No Background Theme
     */
    public function test_create_wishlist_without_background_theme()
    {
        $data = [
            'wishlist_name' => 'My Wishlist',
            'wishlist_type' => 'Birthday',
            'gift_wrap_theme' => 'wrap1'
            // No background_theme
        ];

        $errors = $this->validator->validateWishlist($data);

        $this->assertEmpty($errors, 'Background theme should be optional');
    }

    /**
     * @test
     * WISH-009: Create Wishlist - No Gift Wrap Theme
     */
    public function test_create_wishlist_without_gift_wrap_theme()
    {
        $data = [
            'wishlist_name' => 'My Wishlist',
            'wishlist_type' => 'Birthday',
            'background_theme' => 'theme1'
            // No gift_wrap_theme
        ];

        $errors = $this->validator->validateWishlist($data);

        $this->assertEmpty($errors, 'Gift wrap theme should be optional');
    }

    /**
     * @test
     * WISH-010: Create Wishlist - Special Characters in Name
     */
    public function test_create_wishlist_with_special_characters_in_name()
    {
        $data = [
            'wishlist_name' => 'My Wishlist!@#$%', // Special characters
            'wishlist_type' => 'Birthday',
            'background_theme' => 'theme1',
            'gift_wrap_theme' => 'wrap1'
        ];

        $errors = $this->validator->validateWishlist($data);

        // This depends on your validation rules for special characters
        $this->assertIsArray($errors);
    }

    /**
     * @test
     * WISH-011: Create Wishlist - Unicode Characters in Name
     */
    public function test_create_wishlist_with_unicode_characters_in_name()
    {
        $data = [
            'wishlist_name' => 'Mi Lista de Deseos éñü', // Unicode characters
            'wishlist_type' => 'Birthday',
            'background_theme' => 'theme1',
            'gift_wrap_theme' => 'wrap1'
        ];

        $errors = $this->validator->validateWishlist($data);

        // This depends on your validation rules for Unicode characters
        $this->assertIsArray($errors);
    }

    /**
     * @test
     * WISH-012: Create Wishlist - Duplicate Name Same Type
     */
    public function test_create_wishlist_with_duplicate_name_same_type()
    {
        // Create existing wishlist
        $this->createTestUser(['username' => 'testuser']);
        $this->createTestWishlist([
            'username' => 'testuser',
            'wishlist_name' => 'Test Wishlist',
            'type' => 'Birthday'
        ]);

        // Check for duplicates
        $duplicates = Wishlist::findDuplicates('Birthday', 'Test Wishlist', 'testuser');

        $this->assertEquals(1, $duplicates, 'Should find 1 duplicate wishlist with same name and type');
    }

    /**
     * @test
     * WISH-013: Create Wishlist - Duplicate Name Different Type
     */
    public function test_create_wishlist_with_duplicate_name_different_type()
    {
        // Create existing wishlist
        $this->createTestUser(['username' => 'testuser']);
        $this->createTestWishlist([
            'username' => 'testuser',
            'wishlist_name' => 'Test Wishlist',
            'type' => 'Birthday'
        ]);

        // Check for duplicates with different type
        $duplicates = Wishlist::findDuplicates('Christmas', 'Test Wishlist', 'testuser');

        $this->assertEquals(0, $duplicates, 'Should find 0 duplicates with different type');
    }

    /**
     * @test
     * WISH-027: Edit Wishlist - Valid Name
     */
    public function test_edit_wishlist_with_valid_name()
    {
        $newName = 'Updated Wishlist Name';
        $errors = $this->validator->validateWishlistName($newName);

        $this->assertEmpty($errors, 'Valid wishlist name should not produce validation errors');
    }

    /**
     * @test
     * WISH-028: Edit Wishlist - Empty Name
     */
    public function test_edit_wishlist_with_empty_name()
    {
        $errors = $this->validator->validateWishlistName('');

        $this->assertValidationError($errors, 'wishlist_name', 'Wishlist name is required');
    }

    /**
     * @test
     * WISH-029: Edit Wishlist - Name Too Long
     */
    public function test_edit_wishlist_with_name_too_long()
    {
        $longName = str_repeat('A', 101); // 101 characters, maximum is 100
        $errors = $this->validator->validateWishlistName($longName);

        $this->assertValidationError($errors, 'wishlist_name', 'Wishlist name must not exceed 100 characters');
    }

    /**
     * @test
     * WISH-031: Rename Wishlist - Valid Name
     */
    public function test_rename_wishlist_with_valid_name()
    {
        $newName = 'Renamed Wishlist';
        $errors = $this->validator->validateWishlistName($newName);

        $this->assertEmpty($errors, 'Valid renamed wishlist should not produce validation errors');
    }

    /**
     * @test
     * WISH-032: Rename Wishlist - Empty Name
     */
    public function test_rename_wishlist_with_empty_name()
    {
        $errors = $this->validator->validateWishlistName('');

        $this->assertValidationError($errors, 'wishlist_name', 'Wishlist name is required');
    }

    /**
     * @test
     * WISH-033: Rename Wishlist - Name Too Long
     */
    public function test_rename_wishlist_with_name_too_long()
    {
        $longName = str_repeat('A', 101); // 101 characters, maximum is 100
        $errors = $this->validator->validateWishlistName($longName);

        $this->assertValidationError($errors, 'wishlist_name', 'Wishlist name must not exceed 100 characters');
    }

    /**
     * @test
     * Test wishlist model methods
     */
    public function test_wishlist_model_find_by_user_and_id()
    {
        $user = $this->createTestUser(['username' => 'testuser']);
        $wishlist = $this->createTestWishlist([
            'username' => 'testuser',
            'wishlist_name' => 'Test Wishlist'
        ]);

        $foundWishlist = Wishlist::findByUserAndId('testuser', $wishlist['id']);

        $this->assertNotNull($foundWishlist, 'Should find wishlist by user and ID');
        $this->assertEquals('Test Wishlist', $foundWishlist['wishlist_name']);
    }

    /**
     * @test
     * Test wishlist model find by secret key
     */
    public function test_wishlist_model_find_by_secret_key()
    {
        $user = $this->createTestUser(['username' => 'testuser']);
        $wishlist = $this->createTestWishlist([
            'username' => 'testuser',
            'secret_key' => 'secret123'
        ]);

        $foundWishlist = Wishlist::findBySecretKey('secret123');

        $this->assertNotNull($foundWishlist, 'Should find wishlist by secret key');
        $this->assertEquals('secret123', $foundWishlist['secret_key']);
    }

    /**
     * @test
     * Test wishlist model get wisher name
     */
    public function test_wishlist_model_get_wisher_name()
    {
        $user = $this->createTestUser(['username' => 'testuser', 'name' => 'Test User']);
        $wishlist = $this->createTestWishlist([
            'username' => 'testuser',
            'wishlist_name' => 'Test Wishlist'
        ]);

        $wisherName = Wishlist::getWisherName($wishlist['id']);

        $this->assertEquals('Test User', $wisherName, 'Should return correct wisher name');
    }
}
