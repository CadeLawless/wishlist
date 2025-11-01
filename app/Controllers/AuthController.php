<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Constants;
use App\Models\User;
use App\Services\AuthService;
use App\Validation\UserRequestValidator;
use App\Services\EmailService;
use App\Services\SessionManager;
use App\Services\UserPreferencesService;
use App\Helpers\StringHelper;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService = new AuthService(),
        private UserRequestValidator $userValidator = new UserRequestValidator(),
        private EmailService $emailService = new EmailService()
    ) {
        parent::__construct();
    }

    public function showLogin(): Response
    {
        
        $data = [
            'username' => $this->request->input('username', ''),
            'password' => '',
            'remember_me' => $this->request->input('remember_me', false),
            'customStyles' =>
                'input:not([type=submit], #new_password, #current_password) {
                margin-bottom: 0;
            }'
        ];

        return $this->view('auth/login', $data, 'auth');
    }

    /**
     * Handle user login authentication
     * 
     * Processes login form submission, validates credentials, and establishes
     * user session. Redirects authenticated users away from login page.
     * 
     * @return Response Login form view or redirect after successful login
     */
    public function login(): Response
    {

        // Only process form submission on POST requests
        if ($this->request->isPost()) {
            $data = $this->request->input();
            $errors = $this->userValidator->validateLogin($data);

            if ($this->userValidator->hasErrors($errors)) {
                return $this->view('auth/login', [
                    'username' => $data['username'] ?? '',
                    'password' => '',
                    'remember_me' => isset($data['remember_me']),
                    'error_msg' => $this->userValidator->formatErrorsForDisplay($errors)
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

        $data = [
            'username' => $this->request->input('username', ''),
            'name' => $this->request->input('name', ''),
            'email' => $this->request->input('email', ''),
            'password' => '',
            'password_confirmation' => '',
            'customStyles' =>
                'input:not([type=submit], #new_password, #current_password) {
                margin-bottom: 0;
            }'
        ];

        return $this->view('auth/register', $data, 'auth');
    }

    public function register(): Response
    {

        // Only process form submission on POST requests
        if ($this->request->isPost()) {
            $data = $this->request->input();
            $errors = $this->userValidator->validateRegistration($data);

            if ($this->userValidator->hasErrors($errors)) {
                return $this->view('auth/register', [
                    'username' => $data['username'] ?? '',
                    'name' => $data['name'] ?? '',
                    'email' => $data['email'] ?? '',
                    'password' => '',
                    'password_confirmation' => '',
                    'error_msg' => $this->userValidator->formatErrorsForDisplay($errors)
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
                // Set up session and cookies
                SessionManager::setupRegistrationSession($data);
                
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

    public function forgotPassword(): Response
    {
        // Only process form submission on POST requests
        if ($this->request->isPost()) {
            $data = $this->request->input();
            
            // Validate that identifier field is not empty
            $errors = [];
            if (empty($data['identifier'])) {
                $errors['identifier'][] = 'Email or username is required.';
            }
            
            if ($this->userValidator->hasErrors($errors)) {
                return $this->view('auth/forgot-password', [
                    'identifier' => $data['identifier'] ?? '',
                    'error_msg' => $this->userValidator->formatErrorsForDisplay($errors)
                ], 'auth');
            }

            // Check if email or username exists
            $user = User::findByUsernameOrEmail($data['identifier']);
            
            if ($user) {
                // Generate reset password key
                $resetPasswordKey = StringHelper::generateRandomString(Constants::RANDOM_STRING_LENGTH_EMAIL);
                $resetPasswordExpiration = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Update user with reset key
                User::update($user['id'], [
                    'reset_password_key' => $resetPasswordKey,
                    'reset_password_expiration' => $resetPasswordExpiration
                ]);
                
                // Send password reset email
                $this->emailService->sendPasswordResetEmailWithUsername($user['email'], $user['username'], $resetPasswordKey);
            }
            
            // Always show success message for security (don't reveal if account exists)
            return $this->redirect('/wishlist/login')->withSuccess('If an account with that email or username exists, a password reset link has been sent.');
        }

        // Show forgot password form for GET requests
        return $this->view('auth/forgot-password', [
            'identifier' => '',
            'error_msg' => ''
        ], 'auth');
    }

    public function resetPassword(): Response
    {
        // Only process form submission on POST requests
        if ($this->request->isPost()) {
            $data = $this->request->input();
            $errors = $this->userValidator->validateNewPassword($data);

            if ($this->userValidator->hasErrors($errors)) {
                return $this->view('auth/reset-password', [
                    'key' => $data['key'] ?? '',
                    'email' => $data['email'] ?? '',
                    'password' => $data['password'] ?? '',
                    'password_confirmation' => $data['password_confirmation'] ?? '',
                    'error_msg' => $this->userValidator->formatErrorsForDisplay($errors)
                ], 'auth');
            }

            // Validate the reset key and expiration
            $user = User::findByUsernameOrEmail($data['email']);
            
            if (!$user || $user['reset_password_key'] !== $data['key']) {
                return $this->redirect('/wishlist/login')->withError('Invalid reset link.');
            }
            
            if (isset($user['reset_password_expiration']) && strtotime($user['reset_password_expiration']) < time()) {
                return $this->redirect('/wishlist/login')->withError('Reset link has expired. Please request a new one.');
            }
            
            // Update password and clear reset fields
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            User::update($user['id'], [
                'password' => $hashedPassword,
                'reset_password_key' => null,
                'reset_password_expiration' => null
            ]);
            
            return $this->redirect('/wishlist/login')->withSuccess('Password has been reset successfully. You can now log in with your new password.');
        }

        // Show reset password form for GET requests
        $key = $this->request->get('key');
        $email = $this->request->get('email');
        
        if (!$key || !$email) {
            return $this->redirect('/wishlist/login')->withError('Invalid reset link.');
        }

        // Validate the reset key and expiration
        $user = User::findByUsernameOrEmail($email);
        
        if (!$user || $user['reset_password_key'] !== $key) {
            return $this->view('auth/reset-password-error', [
                'error' => 'Invalid reset link.',
                'link_text' => 'Go to Login',
                'link_url' => '/wishlist/login'
            ], 'auth');
        }
        
        if (isset($user['reset_password_expiration']) && strtotime($user['reset_password_expiration']) < time()) {
            return $this->view('auth/reset-password-error', [
                'error' => 'This password reset link has expired. Try again!',
                'link_text' => 'Go to Login',
                'link_url' => '/wishlist/login'
            ], 'auth');
        }

        return $this->view('auth/reset-password', [
            'key' => $key,
            'email' => $email,
            'password' => '',
            'password_confirmation' => '',
            'error_msg' => ''
        ], 'auth');
    }

    public function verifyEmail(): Response
    {
        $username = $this->request->get('username');
        $key = $this->request->get('key');

        if (!$username || !$key) {
            return $this->view('auth/verify-email', [
                'success' => false,
                'expired' => false,
                'notFound' => true
            ], 'auth');
        }

        // Get user by username
        $user = User::findByUsernameOrEmail($username);
        
        if (!$user) {
            return $this->view('auth/verify-email', [
                'success' => false,
                'expired' => false,
                'notFound' => true
            ], 'auth');
        }

        // Verify the key matches
        if ($user['email_key'] !== $key) {
            return $this->view('auth/verify-email', [
                'success' => false,
                'expired' => false,
                'notFound' => true
            ], 'auth');
        }

        // Check if key has expired
        if (isset($user['email_key_expiration']) && strtotime($user['email_key_expiration']) < time()) {
            return $this->view('auth/verify-email', [
                'success' => false,
                'expired' => true,
                'notFound' => false
            ], 'auth');
        }

        // Move unverified_email to email field and clear the verification fields
        try {
            User::update($user['id'], [
                'email' => $user['unverified_email'],
                'unverified_email' => null,
                'email_key' => null,
                'email_key_expiration' => null
            ]);
            
            return $this->view('auth/verify-email', [
                'success' => true,
                'expired' => false,
                'notFound' => false
            ], 'auth');
        } catch (\Exception $e) {
            return $this->view('auth/verify-email', [
                'success' => false,
                'expired' => false,
                'notFound' => false
            ], 'auth');
        }
    }

    public function toggleDarkMode(): Response
    {
        // Check authentication without redirecting for AJAX requests
        $user = $this->auth();
        if (!$user) {
            return new Response(content: 'unauthorized', status: 401);
        }
        
        $dark = $this->request->input('dark');
        return UserPreferencesService::toggleDarkMode($user['id'], $dark);
    }

    public function profile(): Response
    {
        
        $user = $this->auth();
        
        // Use unverified_email if email is not set (newly registered users)
        $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
        
        $data = [
            'user' => $user,
            'name' => $this->request->input('name', $user['name']),
            'email' => $this->request->input('email', $currentEmail),
            'current_password' => $this->request->input('current_password', ''),
            'new_password' => $this->request->input('new_password', ''),
            'confirm_password' => $this->request->input('confirm_password', ''),
            'name_error_msg' => '',
            'email_error_msg' => '',
            'password_error_msg' => '',
            'customStyles' =>
                '.form-flex {
                    max-width: unset;
                }'
        ];

        return $this->view('auth/profile', $data);
    }

    public function updateProfile(): Response
    {
        
        $user = $this->auth();
        $data = $this->request->input();
        
        // Handle name update
        if (isset($data['name_submit_button'])) {
            $errors = $this->userValidator->validateNameUpdate($data);
            
            if ($this->userValidator->hasErrors($errors)) {
                $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
                return $this->view('auth/profile', [
                    'user' => $user,
                    'name' => $data['name'] ?? $user['name'],
                    'email' => $currentEmail,
                    'current_password' => '',
                    'new_password' => '',
                    'confirm_password' => '',
                    'name_error_msg' => $this->userValidator->formatErrorsForDisplay($errors),
                    'email_error_msg' => '',
                    'password_error_msg' => ''
                ]);
            }
            
            try {
                User::update($user['id'], ['name' => $data['name']]);
                return $this->redirect('/wishlist/profile')->withSuccess('Name updated successfully!');
            } catch (\Exception $e) {
                $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
                return $this->view('auth/profile', [
                    'user' => $user,
                    'name' => $data['name'] ?? $user['name'],
                    'email' => $currentEmail,
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
            $errors = $this->userValidator->validateEmailUpdate($data);
            
            if ($this->userValidator->hasErrors($errors)) {
                $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
                return $this->view('auth/profile', [
                    'user' => $user,
                    'name' => $user['name'],
                    'email' => $data['email'] ?? $currentEmail,
                    'current_password' => '',
                    'new_password' => '',
                    'confirm_password' => '',
                    'name_error_msg' => '',
                    'email_error_msg' => $this->userValidator->formatErrorsForDisplay($errors),
                    'password_error_msg' => ''
                ]);
            }
            
            // Check if email already exists
            $existingUsers = User::where('email', '=', $data['email']);
            $existingUser = !empty($existingUsers) ? $existingUsers[0] : null;
            if ($existingUser && $existingUser['id'] != $user['id']) {
                $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
                return $this->view('auth/profile', [
                    'user' => $user,
                    'name' => $user['name'],
                    'email' => $data['email'] ?? $currentEmail,
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
                $emailKey = StringHelper::generateRandomString(Constants::RANDOM_STRING_LENGTH_EMAIL);
                $emailKeyExpiration = date(format: 'Y-m-d H:i:s', timestamp: strtotime('+' . Constants::EMAIL_VERIFICATION_HOURS . ' hours'));
                
                User::update($user['id'], [
                    'unverified_email' => $data['email'],
                    'email_key' => $emailKey,
                    'email_key_expiration' => $emailKeyExpiration
                ]);
                
                // Send verification email
                $this->emailService->sendVerificationEmail($data['email'], $user['username']);
                
                return $this->redirect('/wishlist/profile')->withSuccess('Email update initiated! Please check your email to verify your new address.');
            } catch (\Exception $e) {
                $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
                return $this->view('auth/profile', [
                    'user' => $user,
                    'name' => $user['name'],
                    'email' => $data['email'] ?? $currentEmail,
                    'current_password' => '',
                    'new_password' => '',
                    'confirm_password' => '',
                    'name_error_msg' => '',
                    'email_error_msg' => '<div class="submit-error"><strong>Email could not be updated due to the following errors:</strong><ul><li>Something went wrong while trying to update your email</li></ul></div>',
                    'password_error_msg' => ''
                ]);
            }
        }
        
        // Handle new email submission (when user already has unverified email)
        if (isset($data['new_email_submit_button'])) {
            $errors = $this->userValidator->validateEmailUpdate($data);
            
            if ($this->userValidator->hasErrors($errors)) {
                $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
                return $this->view('auth/profile', [
                    'user' => $user,
                    'name' => $user['name'],
                    'email' => $data['email'] ?? $currentEmail,
                    'current_password' => '',
                    'new_password' => '',
                    'confirm_password' => '',
                    'name_error_msg' => '',
                    'email_error_msg' => $this->userValidator->formatErrorsForDisplay($errors),
                    'password_error_msg' => ''
                ]);
            }
            
            // Check if email already exists
            $existingUsers = User::where('email', '=', $data['email']);
            $existingUser = !empty($existingUsers) ? $existingUsers[0] : null;
            if ($existingUser && $existingUser['id'] != $user['id']) {
                $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
                return $this->view('auth/profile', [
                    'user' => $user,
                    'name' => $user['name'],
                    'email' => $data['email'] ?? $currentEmail,
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
                $emailKey = StringHelper::generateRandomString(Constants::RANDOM_STRING_LENGTH_EMAIL);
                $emailKeyExpiration = date(format: 'Y-m-d H:i:s', timestamp: strtotime('+' . Constants::EMAIL_VERIFICATION_HOURS . ' hours'));
                
                User::update($user['id'], [
                    'unverified_email' => $data['email'],
                    'email_key' => $emailKey,
                    'email_key_expiration' => $emailKeyExpiration
                ]);
                
                // Send verification email
                $this->emailService->sendVerificationEmail($data['email'], $user['username']);
                
                return $this->redirect('/wishlist/profile')->withSuccess('Email update initiated! Please check your email to verify your new address.');
            } catch (\Exception $e) {
                $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
                return $this->view('auth/profile', [
                    'user' => $user,
                    'name' => $user['name'],
                    'email' => $data['email'] ?? $currentEmail,
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
            $errors = $this->userValidator->validatePasswordChange($data);
            
            // Verify current password
            if (!password_verify($data['current_password'], $user['password'])) {
                $errors['current_password'][] = 'Incorrect current password';
            }
            
            if ($this->userValidator->hasErrors($errors)) {
                $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
                return $this->view('auth/profile', [
                    'user' => $user,
                    'name' => $user['name'],
                    'email' => $currentEmail,
                    'current_password' => $data['current_password'] ?? '',
                    'new_password' => $data['new_password'] ?? '',
                    'confirm_password' => $data['confirm_password'] ?? '',
                    'name_error_msg' => '',
                    'email_error_msg' => '',
                    'password_error_msg' => $this->userValidator->formatErrorsForDisplay($errors)
                ]);
            }
            
            try {
                $hashedPassword = password_hash($data['new_password'], PASSWORD_DEFAULT);
                User::update($user['id'], ['password' => $hashedPassword]);
                
                return $this->redirect('/wishlist/profile')->withSuccess('Password changed successfully!');
            } catch (\Exception $e) {
                $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
                return $this->view('auth/profile', [
                    'user' => $user,
                    'name' => $user['name'],
                    'email' => $currentEmail,
                    'current_password' => $data['current_password'] ?? '',
                    'new_password' => $data['new_password'] ?? '',
                    'confirm_password' => $data['confirm_password'] ?? '',
                    'name_error_msg' => '',
                    'email_error_msg' => '',
                    'password_error_msg' => '<div class="submit-error"><strong>Password could not be changed due to the following errors:</strong><ul><li>Something went wrong while trying to change your password</li></ul></div>'
                ]);
            }
        }
        
        // Handle resend verification email
        if (isset($data['resend_verification_button'])) {
            $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
            
            if (empty($currentEmail)) {
                return $this->redirect('/wishlist/profile')->withError('No email address found to send verification to.');
            }
            
            try {
                // Send verification email
                $this->emailService->sendVerificationEmail($currentEmail, $user['username']);
                
                return $this->redirect('/wishlist/profile')->withSuccess('Verification email resent! Please check your inbox.');
            } catch (\Exception $e) {
                return $this->redirect('/wishlist/profile')->withError('Failed to resend verification email. Please try again.');
            }
        }
        
        // Handle forgot password
        if (isset($data['forgot_password_submit_button'])) {
            $currentEmail = $user['email'] ?? $user['unverified_email'] ?? '';
            
            if (empty($currentEmail)) {
                return $this->view('auth/profile', [
                    'user' => $user,
                    'name' => $user['name'],
                    'email' => $currentEmail,
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
                $resetKey = StringHelper::generateRandomString(Constants::RANDOM_STRING_LENGTH_RESET);
                $resetExpiration = date(format: 'Y-m-d H:i:s', timestamp: strtotime('+' . Constants::PASSWORD_RESET_HOURS . ' hours'));
                
                User::update($user['id'], [
                    'reset_password_key' => $resetKey,
                    'reset_password_expiration' => $resetExpiration
                ]);
                
                // Send reset email to the current email (verified or unverified)
                $this->emailService->sendPasswordResetEmail($currentEmail, $resetKey);
                
                return $this->redirect('/wishlist/profile')->withSuccess('Password reset email sent! Please check your email.');
            } catch (\Exception $e) {
                return $this->view('auth/profile', [
                    'user' => $user,
                    'name' => $user['name'],
                    'email' => $currentEmail,
                    'current_password' => '',
                    'new_password' => '',
                    'confirm_password' => '',
                    'name_error_msg' => '',
                    'email_error_msg' => '',
                    'password_error_msg' => '<div class="submit-error"><strong>Something went wrong while trying to send the reset email</strong></div>'
                ]);
            }
        }
        
        return $this->redirect('/wishlist/profile');
    }

    public function admin(): Response
    {
        $user = $this->auth();
        
        // Get paginated users for the admin view
        $page = (int)($this->request->get('pageno', 1));
        $perPage = Constants::ADMIN_ITEMS_PER_PAGE;
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
        
        return $this->view('auth/admin', $data);
    }


    public function adminWishlists(): Response
    {
        $user = $this->auth();
        
        // Get paginated wishlists
        $page = (int)($this->request->get('pageno', 1));
        $perPage = Constants::ADMIN_ITEMS_PER_PAGE;
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
        
        return $this->view('auth/admin', $data);
    }

    /**
     * AJAX endpoint to check if username exists
     */
    public function checkUsername(): Response
    {
        $username = $this->request->input('username');
        
        if (empty($username)) {
            return new Response(
                content: json_encode(['available' => true]),
                status: 200,
                headers: ['Content-Type' => 'application/json']
            );
        }
        
        $existingUser = User::findByUsernameOrEmail($username);
        
        return new Response(
            content: json_encode(['available' => !$existingUser]),
            status: 200,
            headers: ['Content-Type' => 'application/json']
        );
    }

    /**
     * AJAX endpoint to check if email exists
     */
    public function checkEmail(): Response
    {
        $email = $this->request->input('email');
        
        if (empty($email)) {
            return new Response(
                content: json_encode(['available' => true]),
                status: 200,
                headers: ['Content-Type' => 'application/json']
            );
        }
        
        $existingUser = User::findByUsernameOrEmail($email);
        
        return new Response(
            content: json_encode(['available' => !$existingUser]),
            status: 200,
            headers: ['Content-Type' => 'application/json']
        );
    }

}
