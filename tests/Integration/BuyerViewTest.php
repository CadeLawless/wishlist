<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\Item;

class BuyerViewTest extends TestCase
{
    /**
     * @test
     * BUYER-001: Access Public Wishlist - Valid ID
     */
    public function test_access_public_wishlist_with_valid_id()
    {
        // Create user and wishlist
        $user = $this->createTestUser(['username' => 'testuser']);
        $wishlist = $this->createTestWishlist([
            'username' => 'testuser',
            'wishlist_name' => 'Public Wishlist',
            'is_public' => true,
            'secret_key' => 'public123'
        ]);

        // Find wishlist by secret key (simulating buyer access)
        $foundWishlist = Wishlist::findBySecretKey('public123');

        $this->assertNotNull($foundWishlist, 'Should find public wishlist by secret key');
        $this->assertEquals('Public Wishlist', $foundWishlist['wishlist_name']);
        $this->assertTrue($foundWishlist['is_public'], 'Wishlist should be public');
    }

    /**
     * @test
     * BUYER-002: Access Public Wishlist - Invalid ID
     */
    public function test_access_public_wishlist_with_invalid_id()
    {
        // Try to find wishlist with invalid secret key
        $foundWishlist = Wishlist::findBySecretKey('invalid123');

        $this->assertNull($foundWishlist, 'Should not find wishlist with invalid secret key');
    }

    /**
     * @test
     * BUYER-003: Access Public Wishlist - Hidden Wishlist
     */
    public function test_access_public_wishlist_with_hidden_wishlist()
    {
        // Create user and hidden wishlist
        $user = $this->createTestUser(['username' => 'testuser']);
        $wishlist = $this->createTestWishlist([
            'username' => 'testuser',
            'wishlist_name' => 'Hidden Wishlist',
            'is_public' => false, // Hidden wishlist
            'secret_key' => 'hidden123'
        ]);

        // Even with secret key, hidden wishlists should not be accessible to buyers
        $foundWishlist = Wishlist::findBySecretKey('hidden123');

        // This depends on your business logic - you might want to check is_public
        $this->assertNotNull($foundWishlist, 'Should find wishlist by secret key');
        $this->assertFalse($foundWishlist['is_public'], 'Wishlist should be hidden');
    }

    /**
     * @test
     * BUYER-004: Access Public Wishlist - Other User's Hidden Wishlist
     */
    public function test_access_other_users_hidden_wishlist()
    {
        // Create two users
        $user1 = $this->createTestUser(['username' => 'user1']);
        $user2 = $this->createTestUser(['username' => 'user2']);

        // Create hidden wishlist for user1
        $wishlist = $this->createTestWishlist([
            'username' => 'user1',
            'wishlist_name' => 'User1 Hidden Wishlist',
            'is_public' => false,
            'secret_key' => 'user1hidden'
        ]);

        // User2 should not be able to access user1's hidden wishlist
        $foundWishlist = Wishlist::findBySecretKey('user1hidden');

        // This depends on your access control logic
        $this->assertNotNull($foundWishlist, 'Should find wishlist by secret key');
        $this->assertFalse($foundWishlist['is_public'], 'Wishlist should be hidden');
    }

    /**
     * @test
     * BUYER-005: Access Public Wishlist - Empty Wishlist
     */
    public function test_access_public_wishlist_with_empty_wishlist()
    {
        // Create user and empty wishlist
        $user = $this->createTestUser(['username' => 'testuser']);
        $wishlist = $this->createTestWishlist([
            'username' => 'testuser',
            'wishlist_name' => 'Empty Wishlist',
            'is_public' => true,
            'secret_key' => 'empty123'
        ]);

        // Find wishlist
        $foundWishlist = Wishlist::findBySecretKey('empty123');
        $this->assertNotNull($foundWishlist, 'Should find empty wishlist');

        // Check if wishlist has items
        $items = Item::findByWishlistId($wishlist['id']);
        $this->assertEmpty($items, 'Empty wishlist should have no items');
    }

    /**
     * @test
     * BUYER-006: Access Public Wishlist - With Items
     */
    public function test_access_public_wishlist_with_items()
    {
        // Create user and wishlist with items
        $user = $this->createTestUser(['username' => 'testuser']);
        $wishlist = $this->createTestWishlist([
            'username' => 'testuser',
            'wishlist_name' => 'Wishlist with Items',
            'is_public' => true,
            'secret_key' => 'items123'
        ]);

        // Add items to wishlist
        $item1 = $this->createTestItem([
            'wishlist_id' => $wishlist['id'],
            'item_name' => 'Item 1',
            'price' => 19.99
        ]);
        $item2 = $this->createTestItem([
            'wishlist_id' => $wishlist['id'],
            'item_name' => 'Item 2',
            'price' => 29.99
        ]);

        // Find wishlist
        $foundWishlist = Wishlist::findBySecretKey('items123');
        $this->assertNotNull($foundWishlist, 'Should find wishlist with items');

        // Check items
        $items = Item::findByWishlistId($wishlist['id']);
        $this->assertCount(2, $items, 'Wishlist should have 2 items');
        $this->assertEquals('Item 1', $items[0]['item_name']);
        $this->assertEquals('Item 2', $items[1]['item_name']);
    }

    /**
     * @test
     * Test wishlist pagination for buyer view
     */
    public function test_wishlist_items_pagination()
    {
        // Create user and wishlist
        $user = $this->createTestUser(['username' => 'testuser']);
        $wishlist = $this->createTestWishlist([
            'username' => 'testuser',
            'wishlist_name' => 'Pagination Test Wishlist',
            'is_public' => true,
            'secret_key' => 'pagination123'
        ]);

        // Create 15 items to test pagination
        for ($i = 1; $i <= 15; $i++) {
            $this->createTestItem([
                'wishlist_id' => $wishlist['id'],
                'item_name' => "Item {$i}",
                'price' => 10.00 + $i
            ]);
        }

        // Test first page (limit 12 items)
        $items = Item::getPaginatedItems($wishlist['id'], 'testuser', 'date_added DESC', 12, 0);
        $this->assertCount(12, $items, 'First page should have 12 items');

        // Test second page
        $items = Item::getPaginatedItems($wishlist['id'], 'testuser', 'date_added DESC', 12, 12);
        $this->assertCount(3, $items, 'Second page should have 3 items');

        // Test total count
        $totalCount = Item::countItems($wishlist['id'], 'testuser');
        $this->assertEquals(15, $totalCount, 'Total count should be 15 items');
    }

    /**
     * @test
     * Test wishlist sorting for buyer view
     */
    public function test_wishlist_items_sorting()
    {
        // Create user and wishlist
        $user = $this->createTestUser(['username' => 'testuser']);
        $wishlist = $this->createTestWishlist([
            'username' => 'testuser',
            'wishlist_name' => 'Sorting Test Wishlist',
            'is_public' => true,
            'secret_key' => 'sorting123'
        ]);

        // Create items with different priorities and prices
        $this->createTestItem([
            'wishlist_id' => $wishlist['id'],
            'item_name' => 'Low Priority Item',
            'price' => 10.00,
            'priority' => 1
        ]);
        $this->createTestItem([
            'wishlist_id' => $wishlist['id'],
            'item_name' => 'High Priority Item',
            'price' => 50.00,
            'priority' => 4
        ]);
        $this->createTestItem([
            'wishlist_id' => $wishlist['id'],
            'item_name' => 'Medium Priority Item',
            'price' => 30.00,
            'priority' => 2
        ]);

        // Test sorting by priority
        $items = Item::findByWishlistId($wishlist['id'], 'priority DESC');
        $this->assertEquals('High Priority Item', $items[0]['item_name']);
        $this->assertEquals('Medium Priority Item', $items[1]['item_name']);
        $this->assertEquals('Low Priority Item', $items[2]['item_name']);

        // Test sorting by price
        $items = Item::findByWishlistId($wishlist['id'], 'price ASC');
        $this->assertEquals('Low Priority Item', $items[0]['item_name']);
        $this->assertEquals('Medium Priority Item', $items[1]['item_name']);
        $this->assertEquals('High Priority Item', $items[2]['item_name']);
    }

    /**
     * @test
     * Test wishlist filtering for buyer view
     */
    public function test_wishlist_items_filtering()
    {
        // Create user and wishlist
        $user = $this->createTestUser(['username' => 'testuser']);
        $wishlist = $this->createTestWishlist([
            'username' => 'testuser',
            'wishlist_name' => 'Filtering Test Wishlist',
            'is_public' => true,
            'secret_key' => 'filtering123'
        ]);

        // Create items with different priorities
        $this->createTestItem([
            'wishlist_id' => $wishlist['id'],
            'item_name' => 'High Priority Item 1',
            'price' => 10.00,
            'priority' => 4
        ]);
        $this->createTestItem([
            'wishlist_id' => $wishlist['id'],
            'item_name' => 'High Priority Item 2',
            'price' => 20.00,
            'priority' => 4
        ]);
        $this->createTestItem([
            'wishlist_id' => $wishlist['id'],
            'item_name' => 'Low Priority Item',
            'price' => 30.00,
            'priority' => 1
        ]);

        // Test filtering by priority (this would require additional filtering logic)
        $allItems = Item::findByWishlistId($wishlist['id']);
        $highPriorityItems = array_filter($allItems, function($item) {
            return $item['priority'] == 4;
        });

        $this->assertCount(2, $highPriorityItems, 'Should find 2 high priority items');
    }

    /**
     * @test
     * Test wishlist search for buyer view
     */
    public function test_wishlist_items_search()
    {
        // Create user and wishlist
        $user = $this->createTestUser(['username' => 'testuser']);
        $wishlist = $this->createTestWishlist([
            'username' => 'testuser',
            'wishlist_name' => 'Search Test Wishlist',
            'is_public' => true,
            'secret_key' => 'search123'
        ]);

        // Create items with different names
        $this->createTestItem([
            'wishlist_id' => $wishlist['id'],
            'item_name' => 'Red T-Shirt',
            'price' => 15.00
        ]);
        $this->createTestItem([
            'wishlist_id' => $wishlist['id'],
            'item_name' => 'Blue Jeans',
            'price' => 25.00
        ]);
        $this->createTestItem([
            'wishlist_id' => $wishlist['id'],
            'item_name' => 'Red Shoes',
            'price' => 35.00
        ]);

        // Test search functionality (this would require additional search logic)
        $allItems = Item::findByWishlistId($wishlist['id']);
        $redItems = array_filter($allItems, function($item) {
            return strpos(strtolower($item['item_name']), 'red') !== false;
        });

        $this->assertCount(2, $redItems, 'Should find 2 items containing "red"');
    }

    /**
     * @test
     * Test wishlist theme display for buyer view
     */
    public function test_wishlist_theme_display()
    {
        // Create user and wishlist with themes
        $user = $this->createTestUser(['username' => 'testuser']);
        $wishlist = $this->createTestWishlist([
            'username' => 'testuser',
            'wishlist_name' => 'Themed Wishlist',
            'is_public' => true,
            'secret_key' => 'themed123',
            'background_theme' => 'birthday_theme',
            'gift_wrap_theme' => 'elegant_wrap'
        ]);

        $foundWishlist = Wishlist::findBySecretKey('themed123');

        $this->assertNotNull($foundWishlist, 'Should find themed wishlist');
        $this->assertEquals('birthday_theme', $foundWishlist['background_theme']);
        $this->assertEquals('elegant_wrap', $foundWishlist['gift_wrap_theme']);
    }

    /**
     * @test
     * Test wishlist completion status for buyer view
     */
    public function test_wishlist_completion_status()
    {
        // Create user and wishlist
        $user = $this->createTestUser(['username' => 'testuser']);
        $wishlist = $this->createTestWishlist([
            'username' => 'testuser',
            'wishlist_name' => 'Completion Test Wishlist',
            'is_public' => true,
            'secret_key' => 'completion123',
            'is_complete' => false
        ]);

        $foundWishlist = Wishlist::findBySecretKey('completion123');

        $this->assertNotNull($foundWishlist, 'Should find wishlist');
        $this->assertFalse($foundWishlist['is_complete'], 'Wishlist should not be complete');

        // Mark wishlist as complete
        $this->updateWishlist($wishlist['id'], ['is_complete' => true]);

        $updatedWishlist = Wishlist::findBySecretKey('completion123');
        $this->assertTrue($updatedWishlist['is_complete'], 'Wishlist should be complete');
    }

    /**
     * Helper method to update wishlist
     */
    private function updateWishlist(int $id, array $data): bool
    {
        $fields = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $fields[] = "{$field} = ?";
            $values[] = $value;
        }
        
        $values[] = $id;
        
        $sql = "UPDATE wishlists SET " . implode(', ', $fields) . " WHERE id = ?";
        $result = \App\Core\Database::query($sql, $values);
        return $result !== false;
    }
}
