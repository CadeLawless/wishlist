<?php

namespace App\Services;

use App\Models\User;

class AuthService
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
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
            $user = $this->user->createUser($data);
            return $user !== null;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function checkSession(): ?User
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        // Check if already logged in via session
        if (isset($_SESSION['wishlist_logged_in']) && $_SESSION['wishlist_logged_in']) {
            return $this->user;
        }

        // Check remember me cookie
        if (isset($_COOKIE['wishlist_session_id'])) {
            $sessionId = $_COOKIE['wishlist_session_id'];
            $user = $this->user->findBySession($sessionId);
            
            if ($user) {
                // Auto-login
                $_SESSION['wishlist_logged_in'] = true;
                $_SESSION['username'] = $user->username;
                $_SESSION['name'] = $user->name;
                $_SESSION['user_email'] = $user->email;
                $_SESSION['user_id'] = $user->id;
                $_SESSION['admin'] = $user->isAdmin();
                $_SESSION['dark'] = $user->dark === 'Yes';
                
                return $user;
            }
        }

        return null;
    }

    public function getCurrentUser(): ?User
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        if (isset($_SESSION['wishlist_logged_in']) && $_SESSION['wishlist_logged_in']) {
            return $this->user->findByUsernameOrEmail($_SESSION['username']);
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
        return $user && $user->isAdmin();
    }

    public function updatePassword(string $username, string $newPassword): bool
    {
        $user = $this->user->findByUsernameOrEmail($username);
        if ($user) {
            return $user->updatePassword($newPassword);
        }
        return false;
    }

    public function setUnverifiedEmail(string $username, string $email): bool
    {
        $user = $this->user->findByUsernameOrEmail($username);
        if ($user) {
            return $user->setUnverifiedEmail($email);
        }
        return false;
    }

    public function verifyEmail(string $username): bool
    {
        $user = $this->user->findByUsernameOrEmail($username);
        if ($user) {
            return $user->verifyEmail();
        }
        return false;
    }
}
