<?php

namespace App\Models;

use App\Core\Model;

class Wishlist extends Model
{
    protected string $table = 'wishlists';
    protected array $fillable = [
        'type', 'wishlist_name', 'theme_background_id', 'theme_gift_wrap_id',
        'year', 'duplicate', 'username', 'secret_key', 'visibility', 'complete',
        'date_created'
    ];

    public function createWishlist(array $data): static
    {
        // Generate unique secret key
        $data['secret_key'] = $this->generateSecretKey();
        
        // Set year based on current date
        $currentYear = date('Y');
        $data['year'] = date('m/d/Y') >= "12/25/$currentYear" ? $currentYear + 1 : $currentYear;
        
        // Check for duplicates
        $duplicateCount = $this->where('type', $data['type'])
            ->where('wishlist_name', $data['wishlist_name'])
            ->where('username', $data['username'])
            ->count();
        $data['duplicate'] = $duplicateCount;
        
        // Set default values
        $data['visibility'] = $data['visibility'] ?? 'Public';
        $data['complete'] = $data['complete'] ?? 'No';
        $data['date_created'] = date('Y-m-d H:i:s');
        
        return $this->create($data);
    }

    public function updateName(string $name): bool
    {
        return $this->update(['wishlist_name' => $name]);
    }

    public function updateTheme(int $backgroundId, int $giftWrapId): bool
    {
        return $this->update([
            'theme_background_id' => $backgroundId,
            'theme_gift_wrap_id' => $giftWrapId
        ]);
    }

    public function toggleVisibility(): bool
    {
        $newVisibility = $this->visibility === 'Public' ? 'Hidden' : 'Public';
        return $this->update(['visibility' => $newVisibility]);
    }

    public function toggleComplete(): bool
    {
        $newComplete = $this->complete === 'No' ? 'Yes' : 'No';
        return $this->update(['complete' => $newComplete]);
    }

    public function isPublic(): bool
    {
        return $this->visibility === 'Public';
    }

    public function isComplete(): bool
    {
        return $this->complete === 'Yes';
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'wishlist_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }

    public function theme()
    {
        return $this->belongsTo(Theme::class, 'theme_background_id', 'theme_id');
    }

    public function getTotalPrice(): float
    {
        $items = $this->items();
        $total = 0;
        
        foreach ($items as $item) {
            if ($item->price && $item->quantity) {
                $total += (float)$item->price * (int)$item->quantity;
            }
        }
        
        return $total;
    }

    public function getDisplayName(): string
    {
        $duplicate = $this->duplicate > 0 ? " ({$this->duplicate})" : "";
        return $this->wishlist_name . $duplicate;
    }

    private function generateSecretKey(): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        
        do {
            $randomString = '';
            for ($i = 0; $i < 10; $i++) {
                $randomString .= $characters[random_int(0, $charactersLength - 1)];
            }
        } while ($this->where('secret_key', $randomString)->first());
        
        return $randomString;
    }

    public function findBySecretKey(string $secretKey): ?static
    {
        return $this->where('secret_key', $secretKey)->first();
    }

    public function findByUserAndId(string $username, int $id): ?static
    {
        return $this->where('username', $username)
            ->where('id', $id)
            ->first();
    }
}
