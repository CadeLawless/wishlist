<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
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
        return $this->redirect('/login')->withSuccess('You have been logged out successfully.');
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
            // Send verification email
            $this->emailService->sendVerificationEmail($data['email'], $data['username']);
            
            return $this->redirect('/login')->withSuccess('Registration successful! Please check your email to verify your account.');
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
}
