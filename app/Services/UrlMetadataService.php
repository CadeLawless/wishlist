<?php

namespace App\Services;

class UrlMetadataService
{
    private const TIMEOUT = 10; // seconds - increased for Amazon
    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    
    // Third-party API configuration
    private const SCRAPERAPI_URL = 'http://api.scraperapi.com';
    
    // Amazon-specific user agents to rotate
    private const AMAZON_USER_AGENTS = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/121.0',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15'
    ];

    /**
     * Fetch and extract metadata from a URL
     * 
     * @param string $url The URL to fetch metadata from
     * @return array ['success' => bool, 'title' => string, 'price' => string, 'image' => string, 'error' => string]
     */
    public function fetchMetadata(string $url): array
    {
        // Initialize response
        $response = [
            'success' => false,
            'title' => '',
            'price' => '',
            'image' => '',
            'error' => ''
        ];

        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $response['error'] = 'Invalid URL format';
            return $response;
        }

        // Check if it's Amazon and use ScraperAPI directly
        if (strpos($url, 'amazon.com') !== false || strpos($url, 'amazon.') !== false) {
            $scraperConfig = require __DIR__ . '/../../config/scraperapi.php';
            if ($scraperConfig['enabled']) {
                $html = $this->fetchWithScraperAPI($url, $scraperConfig['api_key']);
            } else {
                $response['error'] = 'Amazon URLs are not supported due to their anti-bot measures. Please enter the product details manually.';
                return $response;
            }
        } else {
            // For non-Amazon URLs, try direct fetch first
            $html = $this->fetchUrlContent($url);
            
            if ($html === false) {
                // Try ScraperAPI for other blocked sites
                $scraperConfig = require __DIR__ . '/../../config/scraperapi.php';
                if ($scraperConfig['enabled']) {
                    $html = $this->fetchWithScraperAPI($url, $scraperConfig['api_key']);
                }
            }
        }
        
        if ($html === false) {
            $response['error'] = 'Unable to fetch URL content. The site may be blocking automated requests or the URL is inaccessible.';
            return $response;
        }

        // Parse HTML and extract metadata
        $metadata = $this->parseHtml($html);
        
        // If we got a title but no price, that's still success (many sites don't have price in meta tags)
        if (!empty($metadata['title'])) {
            $response['success'] = true;
            $response['title'] = $metadata['title'];
            $response['price'] = $metadata['price'];
            $response['image'] = $metadata['image'];
        } else {
            $response['error'] = 'No product information found. Please enter details manually.';
        }
        
        return $response;
    }

    /**
     * Fetch HTML content from URL with timeout and headers
     * 
     * @param string $url The URL to fetch
     * @return string|false HTML content or false on failure
     */
    private function fetchUrlContent(string $url): string|false
    {
        // Create stream context with headers and timeout
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: " . self::USER_AGENT . "\r\n" .
                           "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n" .
                           "Accept-Language: en-US,en;q=0.5\r\n" .
                           "Connection: keep-alive\r\n" .
                           "Cache-Control: no-cache\r\n",
                'timeout' => self::TIMEOUT,
                'follow_location' => true,
                'max_redirects' => 5,
                'ignore_errors' => true
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);

        // Suppress warnings and handle errors manually
        $html = @file_get_contents($url, false, $context);
        
        // Check if we got a response
        if ($html === false) {
            return false;
        }
        
        // Check for common bot detection responses
        if (strpos($html, 'Access Denied') !== false || 
            strpos($html, 'blocked') !== false ||
            strpos($html, 'captcha') !== false ||
            strpos($html, 'robot') !== false) {
            return false;
        }
        
        return $html;
    }

    /**
     * Amazon-specific content fetching with multiple strategies
     * 
     * @param string $url Amazon URL
     * @return string|false HTML content or false on failure
     */
    private function fetchAmazonContent(string $url): string|false
    {
        // Strategy 1: Try with cURL first (most reliable for Amazon)
        if (function_exists('curl_init')) {
            $result = $this->fetchWithCurl($url);
            if ($result !== false) {
                return $result;
            }
        }

        // Strategy 2: Try with different user agents using file_get_contents
        foreach (self::AMAZON_USER_AGENTS as $userAgent) {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => "User-Agent: $userAgent\r\n" .
                               "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n" .
                               "Accept-Language: en-US,en;q=0.5\r\n" .
                               "Accept-Encoding: identity\r\n" . // Don't use compression
                               "Connection: keep-alive\r\n" .
                               "Cache-Control: no-cache\r\n" .
                               "Pragma: no-cache\r\n" .
                               "Referer: https://www.google.com/\r\n", // Fake referer
                    'timeout' => self::TIMEOUT,
                    'follow_location' => true,
                    'max_redirects' => 5,
                    'ignore_errors' => true
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);

            $html = @file_get_contents($url, false, $context);
            
            if ($html !== false && !$this->isBlockedResponse($html)) {
                return $html;
            }
            
            // Small delay between attempts
            usleep(1000000); // 1 second delay
        }

        return false;
    }

    /**
     * Fetch content using cURL (more reliable for Amazon)
     * 
     * @param string $url
     * @return string|false
     */
    private function fetchWithCurl(string $url): string|false
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => self::TIMEOUT,
            CURLOPT_USERAGENT => self::AMAZON_USER_AGENTS[array_rand(self::AMAZON_USER_AGENTS)],
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Accept-Encoding: gzip, deflate',
                'Connection: keep-alive',
                'Cache-Control: no-cache',
                'Pragma: no-cache',
                'Referer: https://www.google.com/',
                'Upgrade-Insecure-Requests: 1',
                'Sec-Fetch-Dest: document',
                'Sec-Fetch-Mode: navigate',
                'Sec-Fetch-Site: cross-site'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_ENCODING => '', // Let cURL handle encoding
            CURLOPT_COOKIEJAR => '', // Enable cookies
            CURLOPT_COOKIEFILE => '',
        ]);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($html !== false && $httpCode === 200 && !$this->isBlockedResponse($html)) {
            return $html;
        }
        
        return false;
    }

    /**
     * Fetch content using ScraperAPI (free tier: 2,000 requests)
     * 
     * @param string $url
     * @param string $apiKey
     * @return string|false
     */
    private function fetchWithScraperAPI(string $url, string $apiKey): string|false
    {
        if (empty($apiKey)) {
            return false;
        }

        $apiUrl = self::SCRAPERAPI_URL . '?' . http_build_query([
            'api_key' => $apiKey,
            'url' => $url,
            'render' => 'true', // Enable JavaScript rendering
            'country_code' => 'us', // US for Amazon
            'premium' => 'false' // Use free tier
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => self::TIMEOUT,
                'ignore_errors' => true
            ]
        ]);

        $html = @file_get_contents($apiUrl, false, $context);
        
        if ($html !== false) {
            // Only check for specific blocking indicators, not general "error" words
            if (!$this->isBlockedResponse($html)) {
                return $html;
            }
        }
        
        return false;
    }

    /**
     * Check if response indicates we're blocked
     * 
     * @param string $html
     * @return bool
     */
    private function isBlockedResponse(string $html): bool
    {
        // Only check for very specific blocking indicators
        $blockedIndicators = [
            'Access Denied',
            'Please verify you are human',
            'Security check',
            'Sorry, we just need to make sure you\'re not a robot',
            'Continue shopping', // Amazon bot detection page
            'Page Not Found' // Amazon 404 page
        ];
        
        foreach ($blockedIndicators as $indicator) {
            if (stripos($html, $indicator) !== false) {
                return true;
            }
        }
        
        // Check for CAPTCHA pages
        if (strpos($html, 'captcha') !== false && strpos($html, 'verify') !== false) {
            return true;
        }
        
        return false;
    }

    /**
     * Parse HTML and extract metadata using OpenGraph, Twitter Cards, and standard meta tags
     * 
     * @param string $html The HTML content to parse
     * @return array ['title' => string, 'price' => string, 'image' => string]
     */
    private function parseHtml(string $html): array
    {
        $metadata = [
            'title' => '',
            'price' => '',
            'image' => ''
        ];

        // Suppress DOMDocument warnings for malformed HTML
        libxml_use_internal_errors(true);
        
        $dom = new \DOMDocument();
        $result = @$dom->loadHTML($html);
        
        libxml_clear_errors();

        // Only proceed if HTML was parsed successfully
        if ($result === false) {
            return $metadata;
        }

        // Extract title
        $metadata['title'] = $this->extractTitle($dom);
        
        // Extract price
        $metadata['price'] = $this->extractPrice($dom, $html);
        
        // Extract image
        $metadata['image'] = $this->extractImage($dom);

        return $metadata;
    }

    /**
     * Extract title from OpenGraph, Twitter, or HTML title tag
     * 
     * @param \DOMDocument $dom
     * @return string
     */
    private function extractTitle(\DOMDocument $dom): string
    {
        $xpath = new \DOMXPath($dom);
        
        // Priority order: og:title, twitter:title, title tag
        $titleQueries = [
            "//meta[@property='og:title']/@content",
            "//meta[@name='twitter:title']/@content",
            "//meta[@property='product:title']/@content",
            "//meta[@name='title']/@content",
            "//title"
        ];

        foreach ($titleQueries as $query) {
            $nodes = $xpath->query($query);
            if ($nodes->length > 0) {
                $title = trim($nodes->item(0)->nodeValue);
                if (!empty($title)) {
                    return $this->cleanTitle($title);
                }
            }
        }

        return '';
    }

    /**
     * Extract price from various meta tags and schema.org markup
     * 
     * @param \DOMDocument $dom
     * @param string $html Raw HTML for regex fallback
     * @return string
     */
    private function extractPrice(\DOMDocument $dom, string $html): string
    {
        $xpath = new \DOMXPath($dom);
        
        // Check OpenGraph and product-specific meta tags
        $priceQueries = [
            "//meta[@property='og:price:amount']/@content",
            "//meta[@property='product:price:amount']/@content",
            "//meta[@property='product:price']/@content",
            "//meta[@name='price']/@content",
            "//meta[@itemprop='price']/@content",
            "//span[@itemprop='price']",
            "//span[@class='price']",
            "//div[@class='price']",
        ];

        foreach ($priceQueries as $query) {
            $nodes = $xpath->query($query);
            if ($nodes->length > 0) {
                $price = trim($nodes->item(0)->nodeValue);
                if (!empty($price)) {
                    $cleanedPrice = $this->cleanPrice($price);
                    if (!empty($cleanedPrice)) {
                        return $cleanedPrice;
                    }
                }
            }
        }

        // Try JSON-LD schema.org markup
        $schemaPrice = $this->extractPriceFromSchema($html);
        if (!empty($schemaPrice)) {
            return $schemaPrice;
        }

        // Try Amazon-specific price extraction
        $amazonPrice = $this->extractAmazonPrice($html);
        if (!empty($amazonPrice)) {
            return $amazonPrice;
        }

        return '';
    }

    /**
     * Extract price from JSON-LD schema.org markup
     * 
     * @param string $html
     * @return string
     */
    private function extractPriceFromSchema(string $html): string
    {
        // Look for JSON-LD script tags with Product schema
        if (preg_match('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches)) {
            $json = json_decode($matches[1], true);
            
            if (isset($json['@type']) && ($json['@type'] === 'Product' || $json['@type'] === 'ProductModel')) {
                // Check offers.price
                if (isset($json['offers']['price'])) {
                    return $this->cleanPrice($json['offers']['price']);
                }
                // Check offers array
                if (isset($json['offers'][0]['price'])) {
                    return $this->cleanPrice($json['offers'][0]['price']);
                }
            }
        }

        return '';
    }

    /**
     * Extract price from Amazon-specific HTML patterns
     * 
     * @param string $html
     * @return string
     */
    private function extractAmazonPrice(string $html): string
    {
        // Amazon price patterns (they use various formats)
        $pricePatterns = [
            // New Amazon price selectors (2024)
            '/<span[^>]*class="[^"]*a-price-whole[^"]*"[^>]*>([^<]+)<\/span>/i',
            '/<span[^>]*class="[^"]*a-price[^"]*"[^>]*>([^<]+)<\/span>/i',
            '/<span[^>]*class="[^"]*a-offscreen[^"]*"[^>]*>([^<]+)<\/span>/i',
            '/<span[^>]*id="priceblock_dealprice"[^>]*>([^<]+)<\/span>/i',
            '/<span[^>]*id="priceblock_ourprice"[^>]*>([^<]+)<\/span>/i',
            '/<span[^>]*id="priceblock_saleprice"[^>]*>([^<]+)<\/span>/i',
            '/<span[^>]*data-a-price="([^"]+)"/i',
            '/<span[^>]*data-a-price-amount="([^"]+)"/i',
            // Additional patterns for current Amazon
            '/<span[^>]*class="[^"]*a-price-range[^"]*"[^>]*>([^<]+)<\/span>/i',
            '/<span[^>]*class="[^"]*a-price-symbol[^"]*"[^>]*>([^<]+)<\/span>/i',
            // Look for any span with price-like content
            '/<span[^>]*>([$]?[\d,]+\.?\d*)[^<]*<\/span>/i'
        ];

        foreach ($pricePatterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $price = trim($matches[1]);
                $cleanedPrice = $this->cleanPrice($price);
                if (!empty($cleanedPrice)) {
                    return $cleanedPrice;
                }
            }
        }

        // Try to find price in JSON-LD structured data
        if (preg_match('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches)) {
            $json = json_decode($matches[1], true);
            if (isset($json['offers']['price'])) {
                return $this->cleanPrice($json['offers']['price']);
            }
        }

        return '';
    }

    /**
     * Extract image from OpenGraph or Twitter meta tags
     * 
     * @param \DOMDocument $dom
     * @return string
     */
    private function extractImage(\DOMDocument $dom): string
    {
        $xpath = new \DOMXPath($dom);
        
        $imageQueries = [
            "//meta[@property='og:image']/@content",
            "//meta[@name='twitter:image']/@content",
            "//meta[@property='og:image:url']/@content",
        ];

        foreach ($imageQueries as $query) {
            $nodes = $xpath->query($query);
            if ($nodes->length > 0) {
                $image = trim($nodes->item(0)->nodeValue);
                if (!empty($image)) {
                    return $image;
                }
            }
        }

        return '';
    }

    /**
     * Clean and format title (remove site name, extra whitespace)
     * 
     * @param string $title
     * @return string
     */
    private function cleanTitle(string $title): string
    {
        // Remove common separators and site names at the end (e.g., "Product Name | Amazon.com")
        $title = preg_replace('/\s*[\|\-–—]\s*[^|\-–—]*(?:Amazon|eBay|Walmart|Target|Etsy|\.com).*$/i', '', $title);
        
        // Trim and normalize whitespace
        $title = preg_replace('/\s+/', ' ', trim($title));
        
        // Limit length (database field is 200 chars)
        if (strlen($title) > 200) {
            $title = substr($title, 0, 197) . '...';
        }

        return $title;
    }

    /**
     * Clean and format price (extract numeric value, format as US currency)
     * 
     * @param string $price
     * @return string
     */
    private function cleanPrice(string $price): string
    {
        // Remove currency symbols, commas, and whitespace
        $price = preg_replace('/[^\d.]/', '', $price);
        
        // Validate it's a number
        if (!is_numeric($price) || empty($price)) {
            return '';
        }

        // Convert to float and format
        $price = floatval($price);
        
        // Validate reasonable price range
        if ($price <= 0 || $price > 999999.99) {
            return '';
        }

        // Format as decimal (no dollar sign - form will add it)
        return number_format($price, 2, '.', '');
    }
}

