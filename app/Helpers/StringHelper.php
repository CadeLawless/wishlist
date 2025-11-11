<?php

namespace App\Helpers;

class StringHelper
{
    /**
     * Generate a cryptographically secure random string
     * 
     * @param int $length The length of the random string to generate
     * @return string A random string of the specified length
     */
    public static function generateRandomString(int $length = 50): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }
    
    /**
     * Generate a random string with custom character set
     * 
     * @param int $length The length of the random string to generate
     * @param string $characters Custom character set to use
     * @return string A random string of the specified length
     */
    public static function generateRandomStringWithChars(int $length, string $characters): string
    {
        $charactersLength = strlen($characters);
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }
    
    /**
     * Generate a random string suitable for URLs (alphanumeric only)
     * 
     * @param int $length The length of the random string to generate
     * @return string A random alphanumeric string
     */
    public static function generateUrlSafeString(int $length = 50): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return self::generateRandomStringWithChars($length, $characters);
    }
    
    /**
     * Generate a random string suitable for tokens (includes special characters)
     * 
     * @param int $length The length of the random string to generate
     * @return string A random string with special characters
     */
    public static function generateTokenString(int $length = 50): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+-=[]{}|;:,.<>?';
        return self::generateRandomStringWithChars($length, $characters);
    }
}
