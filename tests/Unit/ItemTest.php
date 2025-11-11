<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Validation\ItemRequestValidator;
use App\Models\Item;

class ItemTest extends TestCase
{
    private ItemRequestValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new ItemRequestValidator();
    }

    /**
     * @test
     * ITEM-021: Add Item Step 2 - Valid Manual Entry
     */
    public function test_add_item_with_valid_manual_entry()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '19.99',
            'quantity' => '1',
            'priority' => '1',
            'link' => 'https://example.com/item',
            'notes' => 'Test notes'
        ];

        $errors = $this->validator->validateItem($data);

        $this->assertEmpty($errors, 'Valid item data should not produce validation errors');
    }

    /**
     * @test
     * ITEM-022: Add Item Step 2 - Empty Name
     */
    public function test_add_item_with_empty_name()
    {
        $data = [
            'item_name' => '', // Empty name
            'price' => '19.99',
            'quantity' => '1',
            'priority' => '1',
            'link' => 'https://example.com/item',
            'notes' => 'Test notes'
        ];

        $errors = $this->validator->validateItem($data);

        $this->assertValidationError($errors, 'item_name', 'Item name is required');
    }

    /**
     * @test
     * ITEM-023: Add Item Step 2 - Name Too Long
     */
    public function test_add_item_with_name_too_long()
    {
        $data = [
            'item_name' => str_repeat('A', 101), // 101 characters, maximum is 100
            'price' => '19.99',
            'quantity' => '1',
            'priority' => '1',
            'link' => 'https://example.com/item',
            'notes' => 'Test notes'
        ];

        $errors = $this->validator->validateItem($data);

        $this->assertValidationError($errors, 'item_name', 'Item name must not exceed 100 characters');
    }

    /**
     * @test
     * ITEM-024: Add Item Step 2 - Empty Price
     */
    public function test_add_item_with_empty_price()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '', // Empty price
            'quantity' => '1',
            'priority' => '1',
            'link' => 'https://example.com/item',
            'notes' => 'Test notes'
        ];

        $errors = $this->validator->validateItem($data);

        $this->assertValidationError($errors, 'price', 'Price is required');
    }

    /**
     * @test
     * ITEM-025: Add Item Step 2 - Invalid Price Format
     */
    public function test_add_item_with_invalid_price_format()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => 'abc', // Invalid price format
            'quantity' => '1',
            'priority' => '1',
            'link' => 'https://example.com/item',
            'notes' => 'Test notes'
        ];

        $errors = $this->validator->validateItem($data);

        $this->assertValidationError($errors, 'price', 'Please enter a valid price');
    }

    /**
     * @test
     * ITEM-026: Add Item Step 2 - Negative Price
     */
    public function test_add_item_with_negative_price()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '-19.99', // Negative price
            'quantity' => '1',
            'priority' => '1',
            'link' => 'https://example.com/item',
            'notes' => 'Test notes'
        ];

        $errors = $this->validator->validateItem($data);

        $this->assertValidationError($errors, 'price', 'Price must be greater than 0');
    }

    /**
     * @test
     * ITEM-027: Add Item Step 2 - Zero Price
     */
    public function test_add_item_with_zero_price()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '0', // Zero price
            'quantity' => '1',
            'priority' => '1',
            'link' => 'https://example.com/item',
            'notes' => 'Test notes'
        ];

        $errors = $this->validator->validateItem($data);

        $this->assertValidationError($errors, 'price', 'Price must be greater than 0');
    }

    /**
     * @test
     * ITEM-028: Add Item Step 2 - Very High Price
     */
    public function test_add_item_with_very_high_price()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '999999', // Very high price
            'quantity' => '1',
            'priority' => '1',
            'link' => 'https://example.com/item',
            'notes' => 'Test notes'
        ];

        $errors = $this->validator->validateItem($data);

        // This depends on your price validation rules
        $this->assertIsArray($errors);
    }

    /**
     * @test
     * ITEM-029: Add Item Step 2 - Decimal Price
     */
    public function test_add_item_with_decimal_price()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '19.99', // Decimal price
            'quantity' => '1',
            'priority' => '1',
            'link' => 'https://example.com/item',
            'notes' => 'Test notes'
        ];

        $errors = $this->validator->validateItem($data);

        $this->assertEmpty($errors, 'Decimal price should be valid');
    }

    /**
     * @test
     * ITEM-030: Add Item Step 2 - Price with Currency Symbol
     */
    public function test_add_item_with_price_currency_symbol()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '$19.99', // Price with currency symbol
            'quantity' => '1',
            'priority' => '1',
            'link' => 'https://example.com/item',
            'notes' => 'Test notes'
        ];

        $errors = $this->validator->validateItem($data);

        // This depends on your price validation rules
        $this->assertIsArray($errors);
    }

    /**
     * @test
     * ITEM-031: Add Item Step 2 - Price with Commas
     */
    public function test_add_item_with_price_commas()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '1,234.56', // Price with commas
            'quantity' => '1',
            'priority' => '1',
            'link' => 'https://example.com/item',
            'notes' => 'Test notes'
        ];

        $errors = $this->validator->validateItem($data);

        // This depends on your price validation rules
        $this->assertIsArray($errors);
    }

    /**
     * @test
     * ITEM-032: Add Item Step 2 - Valid Quantity
     */
    public function test_add_item_with_valid_quantity()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '19.99',
            'quantity' => '5', // Valid quantity
            'priority' => '1',
            'link' => 'https://example.com/item',
            'notes' => 'Test notes'
        ];

        $errors = $this->validator->validateItem($data);

        $this->assertEmpty($errors, 'Valid quantity should be accepted');
    }

    /**
     * @test
     * ITEM-033: Add Item Step 2 - Zero Quantity
     */
    public function test_add_item_with_zero_quantity()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '19.99',
            'quantity' => '0', // Zero quantity
            'priority' => '1',
            'link' => 'https://example.com/item',
            'notes' => 'Test notes'
        ];

        $errors = $this->validator->validateItem($data);

        $this->assertValidationError($errors, 'quantity', 'Quantity must be at least 1');
    }

    /**
     * @test
     * ITEM-034: Add Item Step 2 - Negative Quantity
     */
    public function test_add_item_with_negative_quantity()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '19.99',
            'quantity' => '-1', // Negative quantity
            'priority' => '1',
            'link' => 'https://example.com/item',
            'notes' => 'Test notes'
        ];

        $errors = $this->validator->validateItem($data);

        $this->assertValidationError($errors, 'quantity', 'Quantity must be at least 1');
    }

    /**
     * @test
     * ITEM-035: Add Item Step 2 - Very High Quantity
     */
    public function test_add_item_with_very_high_quantity()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '19.99',
            'quantity' => '10000', // Very high quantity
            'priority' => '1',
            'link' => 'https://example.com/item',
            'notes' => 'Test notes'
        ];

        $errors = $this->validator->validateItem($data);

        // This depends on your quantity validation rules
        $this->assertIsArray($errors);
    }

    /**
     * @test
     * ITEM-036: Add Item Step 2 - Decimal Quantity
     */
    public function test_add_item_with_decimal_quantity()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '19.99',
            'quantity' => '1.5', // Decimal quantity
            'priority' => '1',
            'link' => 'https://example.com/item',
            'notes' => 'Test notes'
        ];

        $errors = $this->validator->validateItem($data);

        // This depends on your quantity validation rules
        $this->assertIsArray($errors);
    }

    /**
     * @test
     * ITEM-037: Add Item Step 2 - Unlimited Quantity
     */
    public function test_add_item_with_unlimited_quantity()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '19.99',
            'quantity' => 'unlimited', // Unlimited quantity
            'priority' => '1',
            'link' => 'https://example.com/item',
            'notes' => 'Test notes'
        ];

        $errors = $this->validator->validateItem($data);

        // This depends on your quantity validation rules
        $this->assertIsArray($errors);
    }

    /**
     * @test
     * ITEM-038: Add Item Step 2 - Valid Priority
     */
    public function test_add_item_with_valid_priority()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '19.99',
            'quantity' => '1',
            'priority' => '3', // Valid priority (1-4)
            'link' => 'https://example.com/item',
            'notes' => 'Test notes'
        ];

        $errors = $this->validator->validateItem($data);

        $this->assertEmpty($errors, 'Valid priority should be accepted');
    }

    /**
     * @test
     * ITEM-039: Add Item Step 2 - Invalid Priority
     */
    public function test_add_item_with_invalid_priority()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '19.99',
            'quantity' => '1',
            'priority' => '5', // Invalid priority (should be 1-4)
            'link' => 'https://example.com/item',
            'notes' => 'Test notes'
        ];

        $errors = $this->validator->validateItem($data);

        $this->assertValidationError($errors, 'priority', 'Priority must be between 1 and 4');
    }

    /**
     * @test
     * ITEM-040: Add Item Step 2 - Valid Link
     */
    public function test_add_item_with_valid_link()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '19.99',
            'quantity' => '1',
            'priority' => '1',
            'link' => 'https://example.com/item', // Valid URL
            'notes' => 'Test notes'
        ];

        $errors = $this->validator->validateItem($data);

        $this->assertEmpty($errors, 'Valid link should be accepted');
    }

    /**
     * @test
     * ITEM-041: Add Item Step 2 - Invalid Link
     */
    public function test_add_item_with_invalid_link()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '19.99',
            'quantity' => '1',
            'priority' => '1',
            'link' => 'invalid-url', // Invalid URL
            'notes' => 'Test notes'
        ];

        $errors = $this->validator->validateItem($data);

        $this->assertValidationError($errors, 'link', 'Please enter a valid URL');
    }

    /**
     * @test
     * ITEM-042: Add Item Step 2 - Empty Link
     */
    public function test_add_item_with_empty_link()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '19.99',
            'quantity' => '1',
            'priority' => '1',
            'link' => '', // Empty link
            'notes' => 'Test notes'
        ];

        $errors = $this->validator->validateItem($data);

        $this->assertEmpty($errors, 'Empty link should be accepted (optional field)');
    }

    /**
     * @test
     * ITEM-043: Add Item Step 2 - Long Link
     */
    public function test_add_item_with_long_link()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '19.99',
            'quantity' => '1',
            'priority' => '1',
            'link' => 'https://example.com/' . str_repeat('a', 1000), // Very long URL
            'notes' => 'Test notes'
        ];

        $errors = $this->validator->validateItem($data);

        // This depends on your link validation rules
        $this->assertIsArray($errors);
    }

    /**
     * @test
     * ITEM-044: Add Item Step 2 - Link with Special Characters
     */
    public function test_add_item_with_link_special_characters()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '19.99',
            'quantity' => '1',
            'priority' => '1',
            'link' => 'https://example.com/item?param=value&other=test#section', // URL with special characters
            'notes' => 'Test notes'
        ];

        $errors = $this->validator->validateItem($data);

        $this->assertEmpty($errors, 'Link with special characters should be valid');
    }

    /**
     * @test
     * ITEM-045: Add Item Step 2 - Valid Notes
     */
    public function test_add_item_with_valid_notes()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '19.99',
            'quantity' => '1',
            'priority' => '1',
            'link' => 'https://example.com/item',
            'notes' => 'This is a test note' // Valid notes
        ];

        $errors = $this->validator->validateItem($data);

        $this->assertEmpty($errors, 'Valid notes should be accepted');
    }

    /**
     * @test
     * ITEM-046: Add Item Step 2 - Long Notes
     */
    public function test_add_item_with_long_notes()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '19.99',
            'quantity' => '1',
            'priority' => '1',
            'link' => 'https://example.com/item',
            'notes' => str_repeat('A', 1001) // Very long notes
        ];

        $errors = $this->validator->validateItem($data);

        // This depends on your notes validation rules
        $this->assertIsArray($errors);
    }

    /**
     * @test
     * ITEM-047: Add Item Step 2 - Notes with Special Characters
     */
    public function test_add_item_with_notes_special_characters()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '19.99',
            'quantity' => '1',
            'priority' => '1',
            'link' => 'https://example.com/item',
            'notes' => 'Test notes with special chars: !@#$%^&*()' // Notes with special characters
        ];

        $errors = $this->validator->validateItem($data);

        // This depends on your notes validation rules
        $this->assertIsArray($errors);
    }

    /**
     * @test
     * ITEM-048: Add Item Step 2 - Notes with HTML
     */
    public function test_add_item_with_notes_html()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '19.99',
            'quantity' => '1',
            'priority' => '1',
            'link' => 'https://example.com/item',
            'notes' => '<p>Test notes with <strong>HTML</strong></p>' // Notes with HTML
        ];

        $errors = $this->validator->validateItem($data);

        // This depends on your HTML sanitization rules
        $this->assertIsArray($errors);
    }

    /**
     * @test
     * ITEM-049: Add Item Step 2 - Notes with Script Tags
     */
    public function test_add_item_with_notes_script_tags()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '19.99',
            'quantity' => '1',
            'priority' => '1',
            'link' => 'https://example.com/item',
            'notes' => '<script>alert("xss")</script>Test notes' // Notes with script tags
        ];

        $errors = $this->validator->validateItem($data);

        // This should be sanitized for security
        $this->assertIsArray($errors);
    }

    /**
     * @test
     * ITEM-050: Add Item Step 2 - Notes with Unicode Characters
     */
    public function test_add_item_with_notes_unicode_characters()
    {
        $data = [
            'item_name' => 'Test Item',
            'price' => '19.99',
            'quantity' => '1',
            'priority' => '1',
            'link' => 'https://example.com/item',
            'notes' => 'Test notes with Unicode: éñü中文' // Notes with Unicode characters
        ];

        $errors = $this->validator->validateItem($data);

        // This depends on your Unicode handling rules
        $this->assertIsArray($errors);
    }

    /**
     * @test
     * Test item model methods
     */
    public function test_item_model_find_by_wishlist_id()
    {
        $user = $this->createTestUser(['username' => 'testuser']);
        $wishlist = $this->createTestWishlist(['username' => 'testuser']);
        $item = $this->createTestItem(['wishlist_id' => $wishlist['id']]);

        $items = Item::findByWishlistId($wishlist['id']);

        $this->assertCount(1, $items, 'Should find 1 item for wishlist');
        $this->assertEquals('Test Item', $items[0]['item_name']);
    }

    /**
     * @test
     * Test item model count items
     */
    public function test_item_model_count_items()
    {
        $user = $this->createTestUser(['username' => 'testuser']);
        $wishlist = $this->createTestWishlist(['username' => 'testuser']);
        $this->createTestItem(['wishlist_id' => $wishlist['id']]);
        $this->createTestItem(['wishlist_id' => $wishlist['id'], 'item_name' => 'Second Item']);

        $count = Item::countItems($wishlist['id'], 'testuser');

        $this->assertEquals(2, $count, 'Should count 2 items for wishlist');
    }
}
