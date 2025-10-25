<?php
// Debug Amazon HTML to find the right selectors for Size and Color
require_once 'vendor/autoload.php';

use App\Services\UrlMetadataService;

$url = 'https://www.amazon.com/Darong-Womens-Casual-Shoulder-Sleeve/dp/B0CFR36HR9/ref=sxin_16_pa_sp_search_thematic_sspa?content-id=amzn1.sym.d2879d59-7642-4685-b55e-4ac84515d99b%3Aamzn1.sym.d2879d59-7642-4685-b55e-4ac84515d99b&cv_ct_cx=cute%2Bshirt&keywords=cute%2Bshirt&pd_rd_i=B0CFR36HR9&pd_rd_r=4aeb0eaa-16e2-4364-81fc-5b4d3d126eca&pd_rd_w=Ma4F0&pd_rd_wg=eJFZ3&pf_rd_p=d2879d59-7642-4685-b55e-4ac84515d99b&pf_rd_r=R6RKRHGQG1RYQ60JVFAP&qid=1761405384&sbo=RZvfv%2F%2FHxDF%2BO5021pAnSA%3D%3D&sr=1-2-183302c6-8dec-4386-8e58-6031e7be5ad8-spons&sp_csd=d2lkZ2V0TmFtZT1zcF9zZWFyY2hfdGhlbWF0aWM&th=1&psc=1';

echo "=== AMAZON HTML DEBUG ===\n";

try {
    $service = new UrlMetadataService();
    
    // Use reflection to get ScraperAPI HTML directly
    $reflection = new ReflectionClass($service);
    $fetchMethod = $reflection->getMethod('fetchWithScraperAPI');
    $fetchMethod->setAccessible(true);
    
    $config = include 'config/scraperapi.php';
    $apiKey = $config['api_key'] ?? '';
    
    if (empty($apiKey)) {
        echo "No ScraperAPI key found\n";
        exit;
    }
    
    $html = $fetchMethod->invoke($service, $url, $apiKey);
    
    if ($html === false) {
        echo "ScraperAPI failed to fetch HTML\n";
        exit;
    }
    
    echo "HTML Length: " . strlen($html) . " characters\n\n";
    
    // Look for size-related content
    echo "=== SIZE SEARCH ===\n";
    if (preg_match_all('/Size[:\s]*([A-Za-z0-9\-\/]+)/i', $html, $matches)) {
        echo "Found size patterns:\n";
        foreach ($matches[1] as $i => $size) {
            echo "  " . ($i+1) . ": '$size'\n";
        }
    } else {
        echo "No size patterns found\n";
    }
    
    // Look for color-related content
    echo "\n=== COLOR SEARCH ===\n";
    if (preg_match_all('/Color[:\s]*([A-Za-z\s]+)/i', $html, $matches)) {
        echo "Found color patterns:\n";
        foreach ($matches[1] as $i => $color) {
            echo "  " . ($i+1) . ": '$color'\n";
        }
    } else {
        echo "No color patterns found\n";
    }
    
    // Look for any text that might be size or color
    echo "\n=== GENERAL SEARCH ===\n";
    if (preg_match_all('/\b(XS|S|M|L|XL|XXL|XXXL|Small|Medium|Large|Extra Large)\b/i', $html, $matches)) {
        echo "Found size-like text:\n";
        $unique = array_unique($matches[0]);
        foreach ($unique as $size) {
            echo "  '$size'\n";
        }
    }
    
    if (preg_match_all('/\b(Red|Blue|Green|Yellow|Black|White|Gray|Grey|Pink|Purple|Orange|Brown|Silver|Gold|Navy|Maroon|Beige|Tan|Cream|Ivory|Dark Purple|Light Blue|Dark Blue|Light Green|Dark Green)\b/i', $html, $matches)) {
        echo "Found color-like text:\n";
        $unique = array_unique($matches[0]);
        foreach ($unique as $color) {
            echo "  '$color'\n";
        }
    }
    
    // Save HTML for manual inspection
    file_put_contents('amazon_debug.html', $html);
    echo "\nHTML saved to amazon_debug.html for manual inspection\n";
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
