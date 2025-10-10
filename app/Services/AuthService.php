<?php

namespace App\Services;

use App\Models\User;

class AuthService
{
    public function __construct()
    {
        // No need for user instance since we use static methods
    }

    public function login(string $username, string $password, bool $remember = false): bool
    {
        $user = User::findByUsernameOrEmail($username);
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        // Start session
        if (!isset($_SESSION)) {
            session_start();
        }

        // Set session variables
        $_SESSION['wishlist_logged_in'] = true;
        $_SESSION['username'] = $user['username'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['admin'] = $user['role'] === 'Admin';
        $_SESSION['dark'] = $user['dark'] === 'Yes';

        // Handle remember me
        if ($remember) {
            $expireDate = date('Y-m-d H:i:s', strtotime('+1 year'));
            User::updateSession($user['id'], session_id(), $expireDate);
            
            // Set cookie
            $cookieTime = 3600 * 24 * 365; // 1 year
            setcookie('wishlist_session_id', session_id(), time() + $cookieTime);
        } else {
            User::updateSession($user['id'], null, null);
        }

        return true;
    }

    public function logout(): void
    {
        // Use session_status() - more reliable than isset($_SESSION)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear all session variables at once
        session_unset();
        
        // Destroy the session
        session_destroy();
        
        // Clear remember me cookie with proper path
        if (isset($_COOKIE['wishlist_session_id'])) {
            setcookie('wishlist_session_id', '', time() - 3600, '/');
        }
        
        // Clear session cookie (optional, for thoroughness)
        if (isset($_COOKIE['PHPSESSID'])) {
            setcookie('PHPSESSID', '', time() - 3600, '/');
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
        if (!isset($_SESSION)) {
            session_start();
        }

        // Check if already logged in via session
        if (isset($_SESSION['wishlist_logged_in']) && $_SESSION['wishlist_logged_in']) {
            return User::findByUsernameOrEmail($_SESSION['username']);
        }

        // Check remember me cookie
        if (isset($_COOKIE['wishlist_session_id'])) {
            $sessionId = $_COOKIE['wishlist_session_id'];
            $user = User::findBySessionId($sessionId);
            
            if ($user) {
                // Auto-login
                $_SESSION['wishlist_logged_in'] = true;
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['admin'] = $user['role'] === 'Admin';
                $_SESSION['dark'] = $user['dark'] === 'Yes';
                
                return $user;
            }
        }

        return null;
    }

    public function getCurrentUser(): ?array
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        if (isset($_SESSION['wishlist_logged_in']) && $_SESSION['wishlist_logged_in']) {
            return User::findByUsernameOrEmail($_SESSION['username']);
        }

        return null;
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
