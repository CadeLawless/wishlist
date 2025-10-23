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
            // Provide specific error messages for known problematic sites
            if (strpos($url, 'walmart.com') !== false) {
                $response['error'] = 'Walmart has very aggressive anti-bot protection. Please enter the product details manually.';
            } elseif (strpos($url, 'target.com') !== false) {
                $response['error'] = 'Target.com blocks automated requests. Please enter the product details manually.';
            } elseif (strpos($url, 'bestbuy.com') !== false) {
                $response['error'] = 'Best Buy blocks automated requests. Please enter the product details manually.';
            } else {
                $response['error'] = 'Unable to fetch URL content. The site may be blocking automated requests or the URL is inaccessible.';
            }
            return $response;
        }

        // Parse HTML and extract metadata
        $metadata = $this->parseHtml($html, $url);
        
        // If we got a title but no price, that's still success (many sites don't have price in meta tags)
        if (!empty($metadata['title'])) {
            $response['success'] = true;
            $response['title'] = $metadata['title'];
            $response['price'] = $metadata['price'];
            $response['image'] = $metadata['image'];
            $response['product_details'] = $metadata['product_details'];
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
        
        // Check for common bot detection responses (more specific patterns)
        if (strpos($html, 'Access Denied') !== false || 
            strpos($html, 'blocked by') !== false ||
            (strpos($html, 'captcha') !== false && (strpos($html, 'verify you are human') !== false || strpos($html, 'Please verify') !== false)) ||
            strpos($html, 'robot detection') !== false ||
            strpos($html, 'bot detected') !== false ||
            strpos($html, 'Please verify you are human') !== false ||
            strpos($html, 'Cloudflare') !== false && strpos($html, 'blocked') !== false) {
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

        // Use shorter timeout for known problematic sites
        $timeout = self::TIMEOUT;
        if (strpos($url, 'walmart.com') !== false || 
            strpos($url, 'target.com') !== false || 
            strpos($url, 'bestbuy.com') !== false) {
            $timeout = 15; // Shorter timeout for problematic sites
        }
        
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => $timeout,
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
    private function parseHtml(string $html, string $url): array
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
        $metadata['price'] = $this->extractPrice($dom, $html, $url);
        
        // Extract image
        $metadata['image'] = $this->extractImage($dom);
        
        // Extract additional product details
        $metadata['product_details'] = $this->extractProductDetails($dom, $html, $url);

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
     * @param string $url The original URL for site-specific extraction
     * @return string
     */
    private function extractPrice(\DOMDocument $dom, string $html, string $url): string
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

        // Try Amazon-specific price extraction (only for Amazon URLs)
        if (strpos($url, 'amazon.com') !== false || strpos($url, 'amazon.') !== false) {
            $amazonPrice = $this->extractAmazonPrice($html);
            if (!empty($amazonPrice)) {
                return $amazonPrice;
            }
        }

        // Try Target-specific price extraction (only for Target URLs)
        if (strpos($url, 'target.com') !== false) {
            $targetPrice = $this->extractTargetPrice($html);
            if (!empty($targetPrice)) {
                return $targetPrice;
            }
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
     * Extract Target-specific product price
     * 
     * @param string $html
     * @return string
     */
    private function extractTargetPrice(string $html): string
    {
        // Target price patterns (prioritize main product price)
        $pricePatterns = [
            // Main product price selectors
            '/<span[^>]*data-test="product-price"[^>]*>([^<]+)<\/span>/i',
            '/<span[^>]*class="[^"]*h-text-lg[^"]*"[^>]*>([^<]+)<\/span>/i',
            '/<span[^>]*class="[^"]*h-text-xl[^"]*"[^>]*>([^<]+)<\/span>/i',
            '/<span[^>]*data-test="current-price"[^>]*>([^<]+)<\/span>/i',
            '/<span[^>]*class="[^"]*styles__StyledText-sc-1x8c2b5-0[^"]*"[^>]*>([^<]+)<\/span>/i',
            '/<span[^>]*class="[^"]*h-display-block[^"]*"[^>]*>([^<]+)<\/span>/i',
            // Look for price in specific containers
            '/<div[^>]*class="[^"]*price[^"]*"[^>]*>([^<]+)<\/div>/i',
            '/<div[^>]*data-test="[^"]*price[^"]*"[^>]*>([^<]+)<\/div>/i'
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

        // If no specific patterns found, try to find the most likely price
        // Look for prices that are likely the main product price (not shipping, tax, etc.)
        if (preg_match_all('/\$[\d,]+\.?\d*/', $html, $matches, PREG_OFFSET_CAPTURE)) {
            $prices = [];
            $shippingPrices = [];
            
            // Analyze each price with its context
            foreach ($matches[0] as $i => $match) {
                $price = $match[0];
                $offset = $match[1];
                
                // Get context around the price
                $start = max(0, $offset - 100);
                $end = min(strlen($html), $offset + 100);
                $context = substr($html, $start, $end - $start);
                
                // Check if this price is in a shipping context
                $isShipping = (
                    stripos($context, 'shipping') !== false ||
                    stripos($context, 'free') !== false ||
                    stripos($context, 'order') !== false ||
                    stripos($context, 'delivery') !== false ||
                    stripos($context, 'pickup') !== false ||
                    stripos($context, 'threshold') !== false
                );
                
                if ($isShipping) {
                    $shippingPrices[] = $price;
                } else {
                    $prices[] = $price;
                }
            }
            
            // Remove duplicates
            $prices = array_unique($prices);
            $shippingPrices = array_unique($shippingPrices);
            
            // Filter out very small prices (likely unit prices) and very large prices
            $filteredPrices = array_filter($prices, function($price) {
                $value = (float) str_replace(['$', ','], '', $price);
                return $value >= 1 && $value <= 1000;
            });
            
            if (!empty($filteredPrices)) {
                // Sort prices and prefer the lower price (more likely to be the actual product price)
                usort($filteredPrices, function($a, $b) {
                    $valueA = (float) str_replace(['$', ','], '', $a);
                    $valueB = (float) str_replace(['$', ','], '', $b);
                    return $valueA <=> $valueB;
                });
                
                // Return the lowest reasonable product price found
                return $this->cleanPrice(reset($filteredPrices));
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

        // Try Amazon-specific image extraction
        $amazonImage = $this->extractAmazonImage($dom);
        if (!empty($amazonImage)) {
            return $amazonImage;
        }

        return '';
    }

    /**
     * Extract Amazon-specific product image
     * 
     * @param \DOMDocument $dom
     * @return string
     */
    private function extractAmazonImage(\DOMDocument $dom): string
    {
        $xpath = new \DOMXPath($dom);
        
        // Amazon-specific image selectors (in order of preference)
        $amazonImageQueries = [
            "//img[@id='landingImage']/@src",
            "//img[@id='landingImage']/@data-old-hires",
            "//img[@id='landingImage']/@data-a-dynamic-image",
            "//img[@id='landingImage']/@data-a-hires",
            "//img[contains(@class, 'a-dynamic-image')]/@src",
            "//img[contains(@class, 'a-dynamic-image')]/@data-old-hires",
            "//img[contains(@class, 'a-dynamic-image')]/@data-a-hires",
            "//div[@id='imgTagWrapperId']//img/@src",
            "//div[@id='imgTagWrapperId']//img/@data-old-hires",
            "//div[@id='imgTagWrapperId']//img/@data-a-hires"
        ];

        foreach ($amazonImageQueries as $query) {
            $nodes = $xpath->query($query);
            if ($nodes->length > 0) {
                $image = trim($nodes->item(0)->nodeValue);
                if (!empty($image) && strpos($image, 'amazon.com') !== false) {
                    // Convert relative URLs to absolute
                    if (strpos($image, '//') === 0) {
                        $image = 'https:' . $image;
                    } elseif (strpos($image, '/') === 0) {
                        $image = 'https://www.amazon.com' . $image;
                    }
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

    /**
     * Extract additional product details like size, color, material, etc.
     * 
     * @param \DOMDocument $dom
     * @param string $html Raw HTML for regex fallback
     * @param string $url The original URL for site-specific extraction
     * @return string Formatted product details for notes field
     */
    private function extractProductDetails(\DOMDocument $dom, string $html, string $url): string
    {
        $details = [];
        
        // Extract size information
        $size = $this->extractSize($dom, $html, $url);
        if (!empty($size)) {
            $details[] = "Size: $size";
        }
        
        // Extract color information
        $color = $this->extractColor($dom, $html, $url);
        if (!empty($color)) {
            $details[] = "Color: $color";
        }
        
        // Extract material information
        $material = $this->extractMaterial($dom, $html, $url);
        if (!empty($material)) {
            $details[] = "Material: $material";
        }
        
        // Extract brand information
        $brand = $this->extractBrand($dom, $html, $url);
        if (!empty($brand)) {
            $details[] = "Brand: $brand";
        }
        
        // Extract dimensions/weight
        $dimensions = $this->extractDimensions($dom, $html, $url);
        if (!empty($dimensions)) {
            $details[] = "Dimensions: $dimensions";
        }
        
        // Extract availability/condition
        $condition = $this->extractCondition($dom, $html, $url);
        if (!empty($condition)) {
            $details[] = "Condition: $condition";
        }
        
        return implode("\n", $details);
    }

    /**
     * Extract size information from product details
     */
    private function extractSize(\DOMDocument $dom, string $html, string $url): string
    {
        $xpath = new \DOMXPath($dom);
        
        // Common size selectors
        $sizeQueries = [
            "//span[contains(@class, 'size')]",
            "//div[contains(@class, 'size')]",
            "//span[contains(@class, 'variant')]",
            "//div[contains(@class, 'variant')]",
            "//select[@name='size']//option[@selected]",
            "//input[@name='size'][@checked]",
            "//span[contains(text(), 'Size:')]/following-sibling::span",
            "//div[contains(text(), 'Size:')]/following-sibling::div"
        ];
        
        foreach ($sizeQueries as $query) {
            $nodes = $xpath->query($query);
            if ($nodes->length > 0) {
                $size = trim($nodes->item(0)->textContent);
                if (!empty($size) && strlen($size) < 50 && !$this->isIrrelevantData($size)) {
                    return $size;
                }
            }
        }
        
        // Try regex patterns for size
        $sizePatterns = [
            '/Size[:\s]+([A-Z0-9\-\/]+)/i',
            '/Size\s*([A-Z0-9\-\/]+)/i',
            '/\b([XS|S|M|L|XL|XXL|XXXL])\b/i',
            '/\b(\d+[A-Z]?)\b/' // Numeric sizes
        ];
        
        foreach ($sizePatterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $size = trim($matches[1]);
                if (!empty($size) && strlen($size) < 20) {
                    return $size;
                }
            }
        }
        
        return '';
    }

    /**
     * Extract color information from product details
     */
    private function extractColor(\DOMDocument $dom, string $html, string $url): string
    {
        $xpath = new \DOMXPath($dom);
        
        // Common color selectors
        $colorQueries = [
            "//span[contains(@class, 'color')]",
            "//div[contains(@class, 'color')]",
            "//span[contains(@class, 'colour')]",
            "//div[contains(@class, 'colour')]",
            "//select[@name='color']//option[@selected]",
            "//input[@name='color'][@checked]",
            "//span[contains(text(), 'Color:')]/following-sibling::span",
            "//div[contains(text(), 'Color:')]/following-sibling::div"
        ];
        
        foreach ($colorQueries as $query) {
            $nodes = $xpath->query($query);
            if ($nodes->length > 0) {
                $color = trim($nodes->item(0)->textContent);
                if (!empty($color) && strlen($color) < 50 && !$this->isIrrelevantData($color)) {
                    return $color;
                }
            }
        }
        
        // Try regex patterns for color
        $colorPatterns = [
            '/Color[:\s]+([A-Za-z\s]+)/i',
            '/Colour[:\s]+([A-Za-z\s]+)/i',
            '/\b(Red|Blue|Green|Yellow|Black|White|Gray|Grey|Pink|Purple|Orange|Brown|Silver|Gold)\b/i'
        ];
        
        foreach ($colorPatterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $color = trim($matches[1]);
                if (!empty($color) && strlen($color) < 30) {
                    return $color;
                }
            }
        }
        
        return '';
    }

    /**
     * Extract material information from product details
     */
    private function extractMaterial(\DOMDocument $dom, string $html, string $url): string
    {
        $xpath = new \DOMXPath($dom);
        
        // Common material selectors
        $materialQueries = [
            "//span[contains(@class, 'material')]",
            "//div[contains(@class, 'material')]",
            "//span[contains(text(), 'Material:')]/following-sibling::span",
            "//div[contains(text(), 'Material:')]/following-sibling::div"
        ];
        
        foreach ($materialQueries as $query) {
            $nodes = $xpath->query($query);
            if ($nodes->length > 0) {
                $material = trim($nodes->item(0)->textContent);
                if (!empty($material) && strlen($material) < 100 && !$this->isIrrelevantData($material)) {
                    return $material;
                }
            }
        }
        
        // Try regex patterns for material
        $materialPatterns = [
            '/Material[:\s]+([A-Za-z\s,]+)/i',
            '/\b(Cotton|Polyester|Wool|Silk|Leather|Denim|Linen|Cashmere|Nylon|Spandex|Rayon|Bamboo|Hemp)\b/i'
        ];
        
        foreach ($materialPatterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $material = trim($matches[1]);
                if (!empty($material) && strlen($material) < 50) {
                    return $material;
                }
            }
        }
        
        return '';
    }

    /**
     * Extract brand information from product details
     */
    private function extractBrand(\DOMDocument $dom, string $html, string $url): string
    {
        $xpath = new \DOMXPath($dom);
        
        // Common brand selectors
        $brandQueries = [
            "//span[contains(@class, 'brand')]",
            "//div[contains(@class, 'brand')]",
            "//span[contains(@class, 'manufacturer')]",
            "//div[contains(@class, 'manufacturer')]",
            "//span[contains(text(), 'Brand:')]/following-sibling::span",
            "//div[contains(text(), 'Brand:')]/following-sibling::div"
        ];
        
        foreach ($brandQueries as $query) {
            $nodes = $xpath->query($query);
            if ($nodes->length > 0) {
                $brand = trim($nodes->item(0)->textContent);
                if (!empty($brand) && strlen($brand) < 50 && !$this->isIrrelevantData($brand)) {
                    return $brand;
                }
            }
        }
        
        return '';
    }

    /**
     * Extract dimensions/weight information from product details
     */
    private function extractDimensions(\DOMDocument $dom, string $html, string $url): string
    {
        $xpath = new \DOMXPath($dom);
        
        // Common dimension selectors
        $dimensionQueries = [
            "//span[contains(@class, 'dimension')]",
            "//div[contains(@class, 'dimension')]",
            "//span[contains(@class, 'weight')]",
            "//div[contains(@class, 'weight')]",
            "//span[contains(text(), 'Dimensions:')]/following-sibling::span",
            "//div[contains(text(), 'Dimensions:')]/following-sibling::div"
        ];
        
        foreach ($dimensionQueries as $query) {
            $nodes = $xpath->query($query);
            if ($nodes->length > 0) {
                $dimensions = trim($nodes->item(0)->textContent);
                if (!empty($dimensions) && strlen($dimensions) < 100 && !$this->isIrrelevantData($dimensions)) {
                    return $dimensions;
                }
            }
        }
        
        // Try regex patterns for dimensions
        $dimensionPatterns = [
            '/Dimensions[:\s]+([0-9\.\sx×]+)/i',
            '/Weight[:\s]+([0-9\.\s]+[a-z]+)/i',
            '/\b(\d+\.?\d*\s*x\s*\d+\.?\d*\s*x\s*\d+\.?\d*)\b/i'
        ];
        
        foreach ($dimensionPatterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $dimensions = trim($matches[1]);
                if (!empty($dimensions) && strlen($dimensions) < 50) {
                    return $dimensions;
                }
            }
        }
        
        return '';
    }

    /**
     * Extract condition information from product details
     */
    private function extractCondition(\DOMDocument $dom, string $html, string $url): string
    {
        $xpath = new \DOMXPath($dom);
        
        // Common condition selectors
        $conditionQueries = [
            "//span[contains(@class, 'condition')]",
            "//div[contains(@class, 'condition')]",
            "//span[contains(@class, 'availability')]",
            "//div[contains(@class, 'availability')]",
            "//span[contains(text(), 'Condition:')]/following-sibling::span",
            "//div[contains(text(), 'Condition:')]/following-sibling::div"
        ];
        
        foreach ($conditionQueries as $query) {
            $nodes = $xpath->query($query);
            if ($nodes->length > 0) {
                $condition = trim($nodes->item(0)->textContent);
                if (!empty($condition) && strlen($condition) < 50 && !$this->isIrrelevantData($condition)) {
                    return $condition;
                }
            }
        }
        
        // Try regex patterns for condition
        $conditionPatterns = [
            '/\b(New|Used|Refurbished|Like New|Good|Fair|Poor)\b/i',
            '/\b(In Stock|Out of Stock|Limited|Discontinued)\b/i'
        ];
        
        foreach ($conditionPatterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $condition = trim($matches[1]);
                if (!empty($condition) && strlen($condition) < 30) {
                    return $condition;
                }
            }
        }
        
        return '';
    }

    /**
     * Check if extracted data is irrelevant (CSS, JS, etc.)
     */
    private function isIrrelevantData(string $data): bool
    {
        $data = strtolower(trim($data));
        
        // Filter out common irrelevant data
        $irrelevantPatterns = [
            'px', 'em', 'rem', '%', 'vh', 'vw', // CSS units
            'var(', 'function', 'return', 'if(', 'for(', // JavaScript
            'ships from', 'shipping', 'delivery', 'pickup', // Shipping info
            'alt', 'src', 'href', 'class', 'id', 'style', // HTML attributes
            'color:', 'background:', 'font-', 'margin', 'padding', // CSS properties
            'var', 'let', 'const', 'this', 'that', // Programming keywords
            '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', // Single numbers
            'x', 'y', 'z', 'i', 'j', 'k', // Single letters
            'bold', 'italic', 'underline', 'normal', // CSS font styles
            'solid', 'dashed', 'dotted', 'none', // CSS border styles
            'left', 'right', 'center', 'justify', // CSS text alignment
            'top', 'bottom', 'middle', // CSS positioning
            'auto', 'inherit', 'initial', 'unset', // CSS values
            'block', 'inline', 'flex', 'grid', // CSS display
            'hidden', 'visible', 'scroll', 'overflow' // CSS visibility
        ];
        
        foreach ($irrelevantPatterns as $pattern) {
            if (strpos($data, $pattern) !== false) {
                return true;
            }
        }
        
        // Check if it's too short or too generic
        if (strlen($data) < 2 || strlen($data) > 50) {
            return true;
        }
        
        // Check if it contains only numbers or special characters
        if (preg_match('/^[\d\s\-\.]+$/', $data) || preg_match('/^[^a-zA-Z]+$/', $data)) {
            return true;
        }
        
        // Check if it's a single word that's too generic (but allow some useful ones)
        if (preg_match('/^(in stock|out of stock|limited|discontinued)$/i', $data)) {
            return true;
        }
        
        return false;
    }
}

