<?php

namespace App\Services;

use App\Models\User;

class AuthService
{

    public function login(string $username, string $password, bool $remember = false): bool
    {
        $user = User::findByUsernameOrEmail($username);
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        // Set session variables using SessionManager
        \App\Services\SessionManager::setAuthUser($user, $remember);
        
        // Handle remember me database update
        if ($remember) {
            $expireDate = date('Y-m-d H:i:s', strtotime('+1 year'));
            User::updateSession($user['id'], session_id(), $expireDate);
        } else {
            User::updateSession($user['id'], null, null);
        }

        return true;
    }

    public function logout(): void
    {
        // Use SessionManager for logout
        \App\Services\SessionManager::logout();
        
        // Clear remember me cookie with proper path
        if (isset($_COOKIE['wishlist_session_id'])) {
            setcookie('wishlist_session_id', '', time() - 3600, '/wishlist');
        }
        
        // Clear session cookie (optional, for thoroughness)
        if (isset($_COOKIE['PHPSESSID'])) {
            setcookie('PHPSESSID', '', time() - 3600, '/wishlist');
        }
    }

    public function register(array $data): bool
    {
        try {
            // Hash the password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Generate email verification key
            $emailKey = bin2hex(random_bytes(25)); // 50 character string
            $emailKeyExpiration = date('Y-m-d H:i:s', strtotime('+24 hours'));
            $sessionExpiration = date('Y-m-d H:i:s', strtotime('+1 year'));
            
            // Prepare data for database insertion
            $userData = [
                'name' => $data['name'],
                'unverified_email' => $data['email'],
                'username' => $data['username'],
                'password' => $hashedPassword,
                'session' => session_id(),
                'session_expiration' => $sessionExpiration,
                'email_key' => $emailKey,
                'email_key_expiration' => $emailKeyExpiration
            ];
            
            // Create user using static method
            $userId = User::create($userData);
            return $userId > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function checkSession(): ?array
    {
        // Check if already logged in via session using SessionManager
        if (\App\Services\SessionManager::isLoggedIn()) {
            return User::findByUsernameOrEmail(\App\Services\SessionManager::getUsername());
        }

        // Check remember me cookie
        if (isset($_COOKIE['wishlist_session_id'])) {
            $sessionId = $_COOKIE['wishlist_session_id'];
            $user = User::findBySessionId($sessionId);
            
            if ($user) {
                // Auto-login using SessionManager
                \App\Services\SessionManager::setAuthUser($user, false);
                
                return $user;
            }
        }

        return null;
    }

    public function getCurrentUser(): ?array
    {
        return \App\Services\SessionManager::getAuthUser();
    }

    public function isLoggedIn(): bool
    {
        return $this->getCurrentUser() !== null;
    }

    public function isAdmin(): bool
    {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === 'Admin';
    }

    public function updatePassword(string $username, string $newPassword): bool
    {
        $user = User::findByUsernameOrEmail($username);
        if ($user) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            return User::update($user['id'], ['password' => $hashedPassword]);
        }
        return false;
    }

    public function setUnverifiedEmail(string $username, string $email): bool
    {
        $user = User::findByUsernameOrEmail($username);
        if ($user) {
            return User::update($user['id'], ['email' => $email, 'verified' => 0]);
        }
        return false;
    }

    public function verifyEmail(string $username): bool
    {
        $user = User::findByUsernameOrEmail($username);
        if ($user) {
            return User::update($user['id'], ['verified' => 1]);
        }
        return false;
    }
}
