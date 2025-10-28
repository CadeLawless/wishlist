<?php

namespace App\Core;

class Constants
{
    // File Upload Constants
    public const MAX_FILE_SIZE_MB = 5;
    public const MAX_FILE_SIZE_BYTES = 5 * 1024 * 1024; // 5MB
    public const ALLOWED_IMAGE_TYPES = ['jpg', 'jpeg', 'png', 'webp'];
    
    // Pagination Constants
    public const ITEMS_PER_PAGE = 12;
    public const ADMIN_ITEMS_PER_PAGE = 10;
    public const MAX_VISIBLE_PAGES = 5;
    
    // String Length Constants
    public const MAX_ITEM_NAME_LENGTH = 100;
    public const MAX_WISHLIST_NAME_LENGTH = 100;
    public const MAX_USERNAME_LENGTH = 50;
    public const MAX_NAME_LENGTH = 100;
    public const MIN_USERNAME_LENGTH = 3;
    public const MIN_NAME_LENGTH = 2;
    public const MIN_ITEM_NAME_LENGTH = 1;
    public const MIN_WISHLIST_NAME_LENGTH = 1;
    
    // Display Constants
    public const ITEM_NAME_SHORT_LENGTH = 25;
    public const ITEM_NOTES_SHORT_LENGTH = 30;
    public const THEME_PREVIEW_HEIGHT = 300;
    public const THEME_PREVIEW_WIDTH_MOBILE = 200;
    
    // Time Constants (in seconds)
    public const SESSION_TIMEOUT = 600; // 10 minutes
    public const EMAIL_VERIFICATION_HOURS = 24;
    public const PASSWORD_RESET_HOURS = 24;
    public const REMEMBER_ME_DAYS = 365;
    public const REMEMBER_ME_SECONDS = 3600 * 24 * 365; // 1 year
    
    // HTTP Constants
    public const CURL_TIMEOUT = 30;
    public const CURL_MAX_REDIRECTS = 5;
    public const HTTP_STATUS_OK = 200;
    public const HTTP_STATUS_BAD_REQUEST = 400;
    public const HTTP_STATUS_UNAUTHORIZED = 401;
    public const HTTP_STATUS_FORBIDDEN = 403;
    public const HTTP_STATUS_NOT_FOUND = 404;
    public const HTTP_STATUS_INTERNAL_SERVER_ERROR = 500;
    
    // String Generation Constants
    public const RANDOM_STRING_LENGTH_EMAIL = 50;
    public const RANDOM_STRING_LENGTH_RESET = 50;
    public const RANDOM_STRING_LENGTH_SECRET = 10;
    public const RANDOM_BYTES_LENGTH = 25; // For bin2hex, results in 50 chars
    
    // Price Constants
    public const MIN_PRICE = 1;
    public const MAX_PRICE = 1000;
    
    // User Agent Constants
    public const DEFAULT_USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    
    // Amazon User Agents
    public const AMAZON_USER_AGENTS = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/121.0',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15'
    ];
    
    // Error Messages
    public const ERROR_INVALID_FILE = 'Invalid file. Please upload a valid image file (JPG, PNG, WEBP) under 5MB.';
    public const ERROR_DIRECTORY_CREATION = 'Failed to create upload directory. Please check permissions.';
    public const ERROR_EMAIL_SENDING = 'Email sending failed';
    public const ERROR_DARK_MODE_TOGGLE = 'Dark mode toggle failed';
    
    // Success Messages
    public const SUCCESS_EMAIL_VERIFICATION = 'Email verification sent successfully!';
    public const SUCCESS_PASSWORD_RESET = 'Password reset email sent successfully!';
    public const SUCCESS_WELCOME_EMAIL = 'Welcome email sent successfully!';
    
    // Validation Messages
    public const VALIDATION_REQUIRED = 'is required.';
    public const VALIDATION_EMAIL_INVALID = 'must be a valid email address.';
    public const VALIDATION_MIN_LENGTH = 'must be at least';
    public const VALIDATION_MAX_LENGTH = 'must not exceed';
    public const VALIDATION_NUMERIC = 'must be numeric.';
    public const VALIDATION_URL_INVALID = 'must be a valid URL.';
    public const VALIDATION_CHARACTERS = 'characters.';
}
