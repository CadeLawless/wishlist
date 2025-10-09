<?php

namespace App\Services;

class EmailService
{
    private string $fromEmail;
    private string $fromName;

    public function __construct()
    {
        $this->fromEmail = Config::get('app.email.from', 'noreply@wishlist.com');
        $this->fromName = Config::get('app.email.name', 'Wish List');
    }

    public function sendVerificationEmail(string $email, string $username): bool
    {
        $subject = 'Verify Your Email Address';
        $verificationLink = $this->generateVerificationLink($username);
        
        $message = $this->getVerificationEmailTemplate($username, $verificationLink);
        
        return $this->sendEmail($email, $subject, $message);
    }

    public function sendPasswordResetEmail(string $email, string $username, string $resetToken): bool
    {
        $subject = 'Reset Your Password';
        $resetLink = $this->generateResetLink($resetToken);
        
        $message = $this->getPasswordResetEmailTemplate($username, $resetLink);
        
        return $this->sendEmail($email, $subject, $message);
    }

    public function sendWelcomeEmail(string $email, string $username): bool
    {
        $subject = 'Welcome to Wish List!';
        $message = $this->getWelcomeEmailTemplate($username);
        
        return $this->sendEmail($email, $subject, $message);
    }

    private function sendEmail(string $to, string $subject, string $message): bool
    {
        $headers = [
            'From: ' . $this->fromName . ' <' . $this->fromEmail . '>',
            'Reply-To: ' . $this->fromEmail,
            'Content-Type: text/html; charset=UTF-8',
            'X-Mailer: PHP/' . phpversion()
        ];

        return mail($to, $subject, $message, implode("\r\n", $headers));
    }

    private function generateVerificationLink(string $username): string
    {
        $baseUrl = Config::get('app.url');
        $token = $this->generateToken($username);
        
        return $baseUrl . '/verify-email?token=' . $token . '&user=' . urlencode($username);
    }

    private function generateResetLink(string $token): string
    {
        $baseUrl = Config::get('app.url');
        return $baseUrl . '/reset-password?token=' . $token;
    }

    private function generateToken(string $data): string
    {
        return hash('sha256', $data . time() . Config::get('app.key', 'default-secret-key'));
    }

    private function getVerificationEmailTemplate(string $username, string $link): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Verify Your Email</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2c3e50;'>Welcome to Wish List!</h2>
                <p>Hello {$username},</p>
                <p>Thank you for creating an account with Wish List. To complete your registration, please verify your email address by clicking the link below:</p>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='{$link}' style='background-color: #3498db; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>Verify Email Address</a>
                </p>
                <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
                <p style='word-break: break-all; color: #666;'>{$link}</p>
                <p>This link will expire in 24 hours.</p>
                <p>If you didn't create an account with Wish List, please ignore this email.</p>
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                <p style='font-size: 12px; color: #666;'>This email was sent from Wish List. Please do not reply to this email.</p>
            </div>
        </body>
        </html>";
    }

    private function getPasswordResetEmailTemplate(string $username, string $link): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Reset Your Password</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2c3e50;'>Password Reset Request</h2>
                <p>Hello {$username},</p>
                <p>We received a request to reset your password for your Wish List account. If you made this request, click the link below to reset your password:</p>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='{$link}' style='background-color: #e74c3c; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a>
                </p>
                <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
                <p style='word-break: break-all; color: #666;'>{$link}</p>
                <p>This link will expire in 1 hour for security reasons.</p>
                <p>If you didn't request a password reset, please ignore this email. Your password will remain unchanged.</p>
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                <p style='font-size: 12px; color: #666;'>This email was sent from Wish List. Please do not reply to this email.</p>
            </div>
        </body>
        </html>";
    }

    private function getWelcomeEmailTemplate(string $username): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Welcome to Wish List!</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2c3e50;'>Welcome to Wish List!</h2>
                <p>Hello {$username},</p>
                <p>Thank you for joining Wish List! We're excited to help you create and manage your wish lists.</p>
                <p>Here's what you can do with your new account:</p>
                <ul>
                    <li>Create multiple wish lists for different occasions</li>
                    <li>Add items with photos, descriptions, and links</li>
                    <li>Share your wish lists with family and friends</li>
                    <li>Track which items have been purchased</li>
                    <li>Customize your lists with themes</li>
                </ul>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='" . Config::get('app.url') . "' style='background-color: #27ae60; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>Get Started</a>
                </p>
                <p>If you have any questions, feel free to reach out to us.</p>
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                <p style='font-size: 12px; color: #666;'>This email was sent from Wish List. Please do not reply to this email.</p>
            </div>
        </body>
        </html>";
    }
}
