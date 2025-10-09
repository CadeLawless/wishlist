<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected string $table = 'wishlist_users';
    protected array $fillable = [
        'username', 'name', 'email', 'password', 'role', 'dark', 
        'unverified_email', 'session', 'session_expiration'
    ];

    public function authenticate(string $username, string $password): bool
    {
        $user = $this->where('username', $username)
            ->orWhere('email', $username)
            ->first();

        if ($user && password_verify($password, $user->password)) {
            $this->fill($user->toArray());
            return true;
        }

        return false;
    }

    public function createUser(array $data): static
    {
        // Hash password
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Set default values
        $data['role'] = $data['role'] ?? 'User';
        $data['dark'] = $data['dark'] ?? 'No';

        return $this->create($data);
    }

    public function updatePassword(string $password): bool
    {
        return $this->update(['password' => password_hash($password, PASSWORD_DEFAULT)]);
    }

    public function setSession(string $sessionId, string $expiration = null): bool
    {
        $data = ['session' => $sessionId];
        if ($expiration) {
            $data['session_expiration'] = $expiration;
        }
        return $this->update($data);
    }

    public function clearSession(): bool
    {
        return $this->update([
            'session' => null,
            'session_expiration' => null
        ]);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'Admin';
    }

    public function isEmailVerified(): bool
    {
        return empty($this->unverified_email);
    }

    public function setUnverifiedEmail(string $email): bool
    {
        return $this->update(['unverified_email' => $email]);
    }

    public function verifyEmail(): bool
    {
        if ($this->unverified_email) {
            $this->update([
                'email' => $this->unverified_email,
                'unverified_email' => null
            ]);
            return true;
        }
        return false;
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class, 'username', 'username');
    }

    public function findByUsernameOrEmail(string $identifier): ?static
    {
        return $this->where('username', $identifier)
            ->orWhere('email', $identifier)
            ->first();
    }

    public function findBySession(string $sessionId): ?static
    {
        $user = $this->where('session', $sessionId)
            ->where('session_expiration', '>', date('Y-m-d H:i:s'))
            ->first();

        return $user;
    }
}
