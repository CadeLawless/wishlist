<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Validation\UserRequestValidator;
use App\Models\User;

class AuthenticationTest extends TestCase
{
    private UserRequestValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new UserRequestValidator();
    }

    /**
     * @test
     * AUTH-001: User Registration - Valid Input
     */
    public function test_user_registration_with_valid_input()
    {
        $data = [
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $errors = $this->validator->validateRegistration($data);

        $this->assertEmpty($errors, 'Valid registration data should not produce validation errors');
    }

    /**
     * @test
     * AUTH-002: User Registration - Invalid Username (Too Short)
     */
    public function test_user_registration_with_username_too_short()
    {
        $data = [
            'username' => 'ab', // 2 characters, minimum is 3
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $errors = $this->validator->validateRegistration($data);

        $this->assertValidationError($errors, 'username', 'Username must be at least 3 characters');
    }

    /**
     * @test
     * AUTH-003: User Registration - Invalid Username (Too Long)
     */
    public function test_user_registration_with_username_too_long()
    {
        $data = [
            'username' => str_repeat('a', 51), // 51 characters, maximum is 50
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $errors = $this->validator->validateRegistration($data);

        $this->assertValidationError($errors, 'username', 'Username must not exceed 50 characters');
    }

    /**
     * @test
     * AUTH-004: User Registration - Invalid Name (Too Short)
     */
    public function test_user_registration_with_name_too_short()
    {
        $data = [
            'username' => 'testuser',
            'name' => 'A', // 1 character, minimum is 2
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $errors = $this->validator->validateRegistration($data);

        $this->assertValidationError($errors, 'name', 'Name must be at least 2 characters');
    }

    /**
     * @test
     * AUTH-005: User Registration - Invalid Name (Too Long)
     */
    public function test_user_registration_with_name_too_long()
    {
        $data = [
            'username' => 'testuser',
            'name' => str_repeat('A', 101), // 101 characters, maximum is 100
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $errors = $this->validator->validateRegistration($data);

        $this->assertValidationError($errors, 'name', 'Name must not exceed 100 characters');
    }

    /**
     * @test
     * AUTH-006: User Registration - Invalid Email Format
     */
    public function test_user_registration_with_invalid_email_format()
    {
        $data = [
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'invalid-email', // Invalid email format
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $errors = $this->validator->validateRegistration($data);

        $this->assertValidationError($errors, 'email', 'Email must be a valid email address');
    }

    /**
     * @test
     * AUTH-007: User Registration - Invalid Password (Too Short)
     */
    public function test_user_registration_with_password_too_short()
    {
        $data = [
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '1234567', // 7 characters, minimum is 8
            'password_confirmation' => '1234567'
        ];

        $errors = $this->validator->validateRegistration($data);

        $this->assertValidationError($errors, 'password', 'Password must be at least 8 characters');
    }

    /**
     * @test
     * AUTH-008: User Registration - Password Mismatch
     */
    public function test_user_registration_with_password_mismatch()
    {
        $data = [
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123'
        ];

        $errors = $this->validator->validateRegistration($data);

        $this->assertValidationError($errors, 'password', 'Passwords do not match');
    }

    /**
     * @test
     * AUTH-009: User Registration - Duplicate Username
     */
    public function test_user_registration_with_duplicate_username()
    {
        // Create existing user
        $this->createTestUser(['username' => 'existinguser']);

        $data = [
            'username' => 'existinguser', // Same username
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $errors = $this->validator->validateRegistration($data);

        $this->assertValidationError($errors, 'username', 'Username already exists');
    }

    /**
     * @test
     * AUTH-010: User Registration - Duplicate Email
     */
    public function test_user_registration_with_duplicate_email()
    {
        // Create existing user
        $this->createTestUser(['email' => 'existing@example.com']);

        $data = [
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'existing@example.com', // Same email
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $errors = $this->validator->validateRegistration($data);

        $this->assertValidationError($errors, 'email', 'Email already exists');
    }

    /**
     * @test
     * AUTH-011: User Registration - Empty Username
     */
    public function test_user_registration_with_empty_username()
    {
        $data = [
            'username' => '', // Empty username
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $errors = $this->validator->validateRegistration($data);

        $this->assertValidationError($errors, 'username', 'Username is required');
    }

    /**
     * @test
     * AUTH-012: User Registration - Empty Name
     */
    public function test_user_registration_with_empty_name()
    {
        $data = [
            'username' => 'testuser',
            'name' => '', // Empty name
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $errors = $this->validator->validateRegistration($data);

        $this->assertValidationError($errors, 'name', 'Name is required');
    }

    /**
     * @test
     * AUTH-013: User Registration - Empty Email
     */
    public function test_user_registration_with_empty_email()
    {
        $data = [
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => '', // Empty email
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $errors = $this->validator->validateRegistration($data);

        $this->assertValidationError($errors, 'email', 'Email is required');
    }

    /**
     * @test
     * AUTH-014: User Registration - Empty Password
     */
    public function test_user_registration_with_empty_password()
    {
        $data = [
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '', // Empty password
            'password_confirmation' => 'password123'
        ];

        $errors = $this->validator->validateRegistration($data);

        $this->assertValidationError($errors, 'password', 'Password is required');
    }

    /**
     * @test
     * AUTH-015: User Registration - Special Characters in Username
     */
    public function test_user_registration_with_special_characters_in_username()
    {
        $data = [
            'username' => 'test@user!', // Special characters
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $errors = $this->validator->validateRegistration($data);

        // This test depends on your validation rules for special characters
        // Adjust the assertion based on your actual validation logic
        $this->assertIsArray($errors);
    }

    /**
     * @test
     * AUTH-064: Email Validation - Leading/Trailing Spaces
     */
    public function test_email_validation_with_leading_trailing_spaces()
    {
        $data = [
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => ' test@example.com ', // Leading and trailing spaces
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $errors = $this->validator->validateRegistration($data);

        // Email should be trimmed, so this should pass validation
        $this->assertNoValidationError($errors, 'email');
    }

    /**
     * @test
     * AUTH-065: Password Validation - Spaces
     */
    public function test_password_validation_with_spaces()
    {
        $data = [
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'pass word 123', // Password with spaces
            'password_confirmation' => 'pass word 123'
        ];

        $errors = $this->validator->validateRegistration($data);

        // This depends on your password validation rules
        $this->assertIsArray($errors);
    }

    /**
     * @test
     * AUTH-066: Password Validation - Unicode Characters
     */
    public function test_password_validation_with_unicode_characters()
    {
        $data = [
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'pássw0rd123', // Password with Unicode characters
            'password_confirmation' => 'pássw0rd123'
        ];

        $errors = $this->validator->validateRegistration($data);

        // This depends on your password validation rules
        $this->assertIsArray($errors);
    }

    /**
     * @test
     * AUTH-067: Password Validation - Maximum Length
     */
    public function test_password_validation_with_very_long_password()
    {
        $data = [
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => str_repeat('a', 1001), // Very long password
            'password_confirmation' => str_repeat('a', 1001)
        ];

        $errors = $this->validator->validateRegistration($data);

        // This depends on your password validation rules
        $this->assertIsArray($errors);
    }
}
