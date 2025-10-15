<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Services\AuthService;
use App\Services\ValidationService;
use App\Services\EmailService;

class AuthController extends Controller
{
    private AuthService $authService;
    private ValidationService $validationService;
    private EmailService $emailService;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthService();
        $this->validationService = new ValidationService();
        $this->emailService = new EmailService();
    }

    public function showLogin(): Response
    {
        $this->requireGuest();
        
        $data = [
            'username' => $this->request->input('username', ''),
            'password' => '',
            'remember_me' => $this->request->input('remember_me', false)
        ];

        return $this->view('auth/login', $data, 'auth');
    }

    public function login(): Response
    {
        $this->requireGuest();

        // Only process form submission on POST requests
        if ($this->request->isPost()) {
            $data = $this->request->input();
            $errors = $this->validationService->validateLogin($data);

            if ($this->validationService->hasErrors($errors)) {
                return $this->view('auth/login', [
                    'username' => $data['username'] ?? '',
                    'password' => '',
                    'remember_me' => isset($data['remember_me']),
                    'error_msg' => $this->validationService->formatErrorsForDisplay($errors)
                ], 'auth');
            }

            $remember = isset($data['remember_me']);
            
            if ($this->authService->login($data['username'], $data['password'], $remember)) {
                return $this->redirect('/wishlist/');
            }

            return $this->view('auth/login', [
                'username' => $data['username'] ?? '',
                'password' => '',
                'remember_me' => $remember,
                'error_msg' => '<div class="submit-error"><strong>Login failed due to the following errors:</strong><ul><li>Username/email or password is incorrect</li></ul></div>'
            ], 'auth');
        }

        // Show login form for GET requests
        return $this->view('auth/login', [
            'username' => '',
            'password' => '',
            'remember_me' => false,
            'error_msg' => ''
        ], 'auth');
    }

    public function logout(): Response
    {
        $this->authService->logout();
        return $this->redirect('/wishlist/login')->withSuccess('You have been logged out successfully.');
    }

    public function showRegister(): Response
    {
        $this->requireGuest();

        $data = [
            'username' => $this->request->input('username', ''),
            'name' => $this->request->input('name', ''),
            'email' => $this->request->input('email', ''),
            'password' => '',
            'password_confirmation' => ''
        ];

        return $this->view('auth/register', $data, 'auth');
    }

    public function register(): Response
    {
        $this->requireGuest();

        // Only process form submission on POST requests
        if ($this->request->isPost()) {
            $data = $this->request->input();
            $errors = $this->validationService->validateUser($data);

            // Add password confirmation validation
            if (!empty($data['password']) && $data['password'] !== $data['password_confirmation']) {
                $errors['password_confirmation'][] = 'Passwords do not match.';
            }

            if ($this->validationService->hasErrors($errors)) {
                return $this->view('auth/register', [
                    'username' => $data['username'] ?? '',
                    'name' => $data['name'] ?? '',
                    'email' => $data['email'] ?? '',
                    'password' => '',
                    'password_confirmation' => '',
                    'error_msg' => $this->validationService->formatErrorsForDisplay($errors)
                ], 'auth');
            }

            // Check if username or email already exists
            $existingUser = $this->authService->getCurrentUser();
            if ($existingUser) {
                return $this->view('auth/register', [
                    'username' => $data['username'] ?? '',
                    'name' => $data['name'] ?? '',
                    'email' => $data['email'] ?? '',
                    'password' => '',
                    'password_confirmation' => '',
                    'error_msg' => '<div class="submit-error"><strong>Registration failed:</strong><ul><li>Username or email already exists</li></ul></div>'
                ], 'auth');
            }

            if ($this->authService->register($data)) {
                // Set up session and cookies like the original code
                if (!isset($_SESSION)) {
                    session_start();
                }
                
                // Set session variables
                $_SESSION['wishlist_logged_in'] = true;
                $_SESSION['username'] = $data['username'];
                $_SESSION['account_created'] = true;
                
                // Set remember me cookie
                $cookieTime = 3600 * 24 * 365; // 1 year
                setcookie('wishlist_session_id', session_id(), time() + $cookieTime);
                
                // Send verification email
                $this->emailService->sendVerificationEmail($data['email'], $data['username']);
                
                return $this->redirect('/wishlist/')->withSuccess('Registration successful! Please check your email to verify your account.');
            }

            return $this->view('auth/register', [
                'username' => $data['username'] ?? '',
                'name' => $data['name'] ?? '',
                'email' => $data['email'] ?? '',
                'password' => '',
                'password_confirmation' => '',
                'error_msg' => '<div class="submit-error"><strong>Registration failed:</strong><ul><li>Unable to create account. Please try again.</li></ul></div>'
            ], 'auth');
        }

        // Show registration form for GET requests
        return $this->view('auth/register', [
            'username' => '',
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
            'error_msg' => ''
        ], 'auth');
    }

    public function showForgotPassword(): Response
    {
        $this->requireGuest();

        $data = [
            'email' => $this->request->input('email', '')
        ];

        return $this->view('auth/forgot-password', $data, 'auth');
    }

    public function sendResetLink(): Response
    {
        $this->requireGuest();

        $data = $this->request->input();
        $errors = $this->validationService->validatePasswordReset($data);

        if ($this->validationService->hasErrors($errors)) {
            return $this->view('auth/forgot-password', [
                'email' => $data['email'] ?? '',
                'error_msg' => $this->validationService->formatErrorsForDisplay($errors)
            ], 'auth');
        }

        // In a real implementation, you would generate a reset token and send email
        // For now, just show success message
        return $this->redirect('/login')->withSuccess('If an account with that email exists, a password reset link has been sent.');
    }

    public function showResetPassword(): Response
    {
        $this->requireGuest();

        $token = $this->request->get('token');
        if (!$token) {
            return $this->redirect('/login')->withError('Invalid reset token.');
        }

        return $this->view('auth/reset-password', [
            'token' => $token,
            'password' => '',
            'password_confirmation' => ''
        ], 'auth');
    }

    public function resetPassword(): Response
    {
        $this->requireGuest();

        $data = $this->request->input();
        $errors = $this->validationService->validateNewPassword($data);

        if ($this->validationService->hasErrors($errors)) {
            return $this->view('auth/reset-password', [
                'token' => $data['token'] ?? '',
                'password' => '',
                'password_confirmation' => '',
                'error_msg' => $this->validationService->formatErrorsForDisplay($errors)
            ], 'auth');
        }

        // In a real implementation, you would validate the token and update password
        // For now, just show success message
        return $this->redirect('/login')->withSuccess('Password has been reset successfully. You can now log in with your new password.');
    }

    public function verifyEmail(): Response
    {
        $username = $this->request->get('user');
        $token = $this->request->get('token');

        if (!$username || !$token) {
            return $this->redirect('/login')->withError('Invalid verification link.');
        }

        if ($this->authService->verifyEmail($username)) {
            return $this->redirect('/login')->withSuccess('Email verified successfully! You can now log in.');
        }

        return $this->redirect('/login')->withError('Email verification failed. Please try again.');
    }

    public function toggleDarkMode(): Response
    {
        // Check authentication without redirecting for AJAX requests
        $user = $this->authService->getCurrentUser();
        if (!$user) {
            return new Response('unauthorized', 401);
        }
        
        $dark = $this->request->input('dark');
        
        if ($dark === 'Yes' || $dark === 'No') {
            try {
                User::update($user['id'], ['dark' => $dark]);
                
                // Also update the session
                $_SESSION['dark'] = $dark === 'Yes';
                return new Response('success');
            } catch (\Exception $e) {
                error_log('Dark mode toggle failed: ' . $e->getMessage());
                return new Response('error', 500);
            }
        }
        
        return new Response('invalid_data', 400);
    }

    public function profile(): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        
        $data = [
            'user' => $user,
            'name' => $this->request->input('name', $user['name']),
            'email' => $this->request->input('email', $user['email']),
            'current_password' => $this->request->input('current_password', ''),
            'new_password' => $this->request->input('new_password', ''),
            'confirm_password' => $this->request->input('confirm_password', ''),
            'name_error_msg' => '',
            'email_error_msg' => '',
            'password_error_msg' => ''
        ];

        return $this->view('auth/profile', $data);
    }

    public function updateProfile(): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $data = $this->request->input();
        
        // Handle name update
        if (isset($data['name_submit_button'])) {
            $errors = $this->validationService->validateName($data);
            
            if ($this->validationService->hasErrors($errors)) {
                return $this->view('auth/profile', [
                    'user' => $user,
                    'name' => $data['name'] ?? $user['name'],
                    'email' => $user['email'],
                    'current_password' => '',
                    'new_password' => '',
                    'confirm_password' => '',
                    'name_error_msg' => $this->validationService->formatErrorsForDisplay($errors),
                    'email_error_msg' => '',
                    'password_error_msg' => ''
                ]);
            }
            
            try {
                User::update($user['id'], ['name' => $data['name']]);
                return $this->redirect('/profile')->withSuccess('Name updated successfully!');
            } catch (\Exception $e) {
                return $this->view('auth/profile', [
                    'user' => $user,
                    'name' => $data['name'] ?? $user['name'],
                    'email' => $user['email'],
                    'current_password' => '',
                    'new_password' => '',
                    'confirm_password' => '',
                    'name_error_msg' => '<div class="submit-error"><strong>Name could not be updated due to the following errors:</strong><ul><li>Something went wrong while trying to update your name</li></ul></div>',
                    'email_error_msg' => '',
                    'password_error_msg' => ''
                ]);
            }
        }
        
        // Handle email update
        if (isset($data['email_submit_button'])) {
            $errors = $this->validationService->validateEmail($data);
            
            if ($this->validationService->hasErrors($errors)) {
                return $this->view('auth/profile', [
                    'user' => $user,
                    'name' => $user['name'],
                    'email' => $data['email'] ?? $user['email'],
                    'current_password' => '',
                    'new_password' => '',
                    'confirm_password' => '',
                    'name_error_msg' => '',
                    'email_error_msg' => $this->validationService->formatErrorsForDisplay($errors),
                    'password_error_msg' => ''
                ]);
            }
            
            // Check if email already exists
            $existingUser = User::where('email', $data['email'])->first();
            if ($existingUser && $existingUser['id'] != $user['id']) {
                return $this->view('auth/profile', [
                    'user' => $user,
                    'name' => $user['name'],
                    'email' => $data['email'] ?? $user['email'],
                    'current_password' => '',
                    'new_password' => '',
                    'confirm_password' => '',
                    'name_error_msg' => '',
                    'email_error_msg' => '<div class="submit-error"><strong>Email could not be updated due to the following errors:</strong><ul><li>That email already has an account associated with it. Try a different one.</li></ul></div>',
                    'password_error_msg' => ''
                ]);
            }
            
            try {
                // Generate email verification key
                $emailKey = $this->generateRandomString(50);
                $emailKeyExpiration = date('Y-m-d H:i:s', strtotime('+24 hours'));
                
                User::update($user['id'], [
                    'unverified_email' => $data['email'],
                    'email_key' => $emailKey,
                    'email_key_expiration' => $emailKeyExpiration
                ]);
                
                // Send verification email
                $this->emailService->sendVerificationEmail($data['email'], $user['username']);
                
                return $this->redirect('/profile')->withSuccess('Email update initiated! Please check your email to verify your new address.');
            } catch (\Exception $e) {
                return $this->view('auth/profile', [
                    'user' => $user,
                    'name' => $user['name'],
                    'email' => $data['email'] ?? $user['email'],
                    'current_password' => '',
                    'new_password' => '',
                    'confirm_password' => '',
                    'name_error_msg' => '',
                    'email_error_msg' => '<div class="submit-error"><strong>Email could not be updated due to the following errors:</strong><ul><li>Something went wrong while trying to update your email</li></ul></div>',
                    'password_error_msg' => ''
                ]);
            }
        }
        
        // Handle password update
        if (isset($data['password_submit_button'])) {
            $errors = $this->validationService->validatePasswordChange($data);
            
            // Verify current password
            if (!password_verify($data['current_password'], $user['password'])) {
                $errors['current_password'][] = 'Incorrect current password';
            }
            
            if ($this->validationService->hasErrors($errors)) {
                return $this->view('auth/profile', [
                    'user' => $user,
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'current_password' => $data['current_password'] ?? '',
                    'new_password' => $data['new_password'] ?? '',
                    'confirm_password' => $data['confirm_password'] ?? '',
                    'name_error_msg' => '',
                    'email_error_msg' => '',
                    'password_error_msg' => $this->validationService->formatErrorsForDisplay($errors)
                ]);
            }
            
            try {
                $hashedPassword = password_hash($data['new_password'], PASSWORD_DEFAULT);
                User::update($user['id'], ['password' => $hashedPassword]);
                
                return $this->redirect('/profile')->withSuccess('Password changed successfully!');
            } catch (\Exception $e) {
                return $this->view('auth/profile', [
                    'user' => $user,
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'current_password' => $data['current_password'] ?? '',
                    'new_password' => $data['new_password'] ?? '',
                    'confirm_password' => $data['confirm_password'] ?? '',
                    'name_error_msg' => '',
                    'email_error_msg' => '',
                    'password_error_msg' => '<div class="submit-error"><strong>Password could not be changed due to the following errors:</strong><ul><li>Something went wrong while trying to change your password</li></ul></div>'
                ]);
            }
        }
        
        // Handle forgot password
        if (isset($data['forgot_password_submit_button'])) {
            if (empty($user['email'])) {
                return $this->view('auth/profile', [
                    'user' => $user,
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'current_password' => '',
                    'new_password' => '',
                    'confirm_password' => '',
                    'name_error_msg' => '',
                    'email_error_msg' => '<p>In order for you to reset a password that you don\'t know, an email with a password reset link needs to be sent to you. Please set up your email above before trying to reset your password</p>',
                    'password_error_msg' => ''
                ]);
            }
            
            try {
                // Generate reset key
                $resetKey = $this->generateRandomString(50);
                $resetExpiration = date('Y-m-d H:i:s', strtotime('+24 hours'));
                
                User::update($user['id'], [
                    'reset_password_key' => $resetKey,
                    'reset_password_expiration' => $resetExpiration
                ]);
                
                // Send reset email
                $this->emailService->sendPasswordResetEmail($user['email'], $resetKey);
                
                return $this->redirect('/profile')->withSuccess('Password reset email sent! Please check your email.');
            } catch (\Exception $e) {
                return $this->view('auth/profile', [
                    'user' => $user,
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'current_password' => '',
                    'new_password' => '',
                    'confirm_password' => '',
                    'name_error_msg' => '',
                    'email_error_msg' => '',
                    'password_error_msg' => '<div class="submit-error"><strong>Something went wrong while trying to send the reset email</strong></div>'
                ]);
            }
        }
        
        return $this->redirect('/profile');
    }

    public function admin(): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        if ($user['role'] !== 'admin') {
            return $this->redirect('/')->withError('Access denied. Admin privileges required.');
        }
        
        return $this->view('auth/admin', ['user' => $user]);
    }

    public function adminUsers(): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        if ($user['role'] !== 'admin') {
            return $this->redirect('/')->withError('Access denied. Admin privileges required.');
        }
        
        // Get paginated users
        $page = (int)($this->request->get('pageno', 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        $users = User::paginate($perPage, $offset);
        $totalUsers = User::count();
        $totalPages = ceil($totalUsers / $perPage);
        
        $data = [
            'user' => $user,
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalUsers' => $totalUsers
        ];
        
        return $this->view('auth/admin-users', $data);
    }

    public function adminWishlists(): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        if ($user['role'] !== 'admin') {
            return $this->redirect('/')->withError('Access denied. Admin privileges required.');
        }
        
        // Get paginated wishlists
        $page = (int)($this->request->get('pageno', 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        $wishlists = \App\Models\Wishlist::paginate($perPage, $offset);
        $totalWishlists = \App\Models\Wishlist::count();
        $totalPages = ceil($totalWishlists / $perPage);
        
        $data = [
            'user' => $user,
            'wishlists' => $wishlists,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalWishlists' => $totalWishlists
        ];
        
        return $this->view('auth/admin-wishlists', $data);
    }

    private function generateRandomString(int $length = 50): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
