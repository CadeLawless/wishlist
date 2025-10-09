<?php

namespace App\Models;

use App\Core\Model;

class Item extends Model
{
    protected string $table = 'items';
    protected array $fillable = [
        'wishlist_id', 'copy_id', 'name', 'notes', 'price', 'quantity',
        'unlimited', 'link', 'image', 'priority', 'quantity_purchased',
        'purchased', 'date_added'
    ];

    public function createItem(array $data): static
    {
        // Set default values
        $data['quantity'] = $data['quantity'] ?? 1;
        $data['unlimited'] = $data['unlimited'] ?? 'No';
        $data['priority'] = $data['priority'] ?? '1';
        $data['quantity_purchased'] = $data['quantity_purchased'] ?? 0;
        $data['purchased'] = $data['purchased'] ?? 'No';
        $data['date_added'] = date('Y-m-d H:i:s');
        
        return $this->create($data);
    }

    public function updateItem(array $data): bool
    {
        return $this->update($data);
    }

    public function purchase(int $quantity = 1): bool
    {
        $newQuantityPurchased = $this->quantity_purchased + $quantity;
        $isFullyPurchased = $newQuantityPurchased >= $this->quantity;
        
        return $this->update([
            'quantity_purchased' => $newQuantityPurchased,
            'purchased' => $isFullyPurchased ? 'Yes' : 'No'
        ]);
    }

    public function unpurchase(int $quantity = 1): bool
    {
        $newQuantityPurchased = max(0, $this->quantity_purchased - $quantity);
        
        return $this->update([
            'quantity_purchased' => $newQuantityPurchased,
            'purchased' => 'No'
        ]);
    }

    public function isPurchased(): bool
    {
        return $this->purchased === 'Yes';
    }

    public function isUnlimited(): bool
    {
        return $this->unlimited === 'Yes';
    }

    public function getRemainingQuantity(): int
    {
        if ($this->isUnlimited()) {
            return -1; // Unlimited
        }
        
        return max(0, (int)$this->quantity - (int)$this->quantity_purchased);
    }

    public function getTotalPrice(): float
    {
        if ($this->price && $this->quantity) {
            return (float)$this->price * (int)$this->quantity;
        }
        return 0;
    }

    public function getPurchasedPrice(): float
    {
        if ($this->price && $this->quantity_purchased) {
            return (float)$this->price * (int)$this->quantity_purchased;
        }
        return 0;
    }

    public function wishlist()
    {
        return $this->belongsTo(Wishlist::class, 'wishlist_id', 'id');
    }

    public function getImagePath(): string
    {
        if ($this->image) {
            return "images/item-images/{$this->wishlist_id}/{$this->image}";
        }
        return "images/site-images/default-photo.png";
    }

    public function hasValidLink(): bool
    {
        return !empty($this->link) && filter_var($this->link, FILTER_VALIDATE_URL);
    }

    public function getPriorityClass(): string
    {
        switch ($this->priority) {
            case '1': return 'priority-high';
            case '2': return 'priority-medium';
            case '3': return 'priority-low';
            case '4': return 'priority-very-low';
            default: return 'priority-medium';
        }
    }

    public function getPriorityText(): string
    {
        switch ($this->priority) {
            case '1': return 'High';
            case '2': return 'Medium';
            case '3': return 'Low';
            case '4': return 'Very Low';
            default: return 'Medium';
        }
    }
}
