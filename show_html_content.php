<?php
// Show actual HTML content
require_once 'vendor/autoload.php';

use App\Services\UrlMetadataService;

$url = 'https://www.amazon.com/Darong-Womens-Casual-Shoulder-Sleeve/dp/B0CFR36HR9/ref=sxin_16_pa_sp_search_thematic_sspa?content-id=amzn1.sym.d2879d59-7642-4685-b55e-4ac84515d99b%3Aamzn1.sym.d2879d59-7642-4685-b55e-4ac84515d99b&cv_ct_cx=cute%2Bshirt&keywords=cute%2Bshirt&pd_rd_i=B0CFR36HR9&pd_rd_r=4aeb0eaa-16e2-4364-81fc-5b4d3d126eca&pd_rd_w=Ma4F0&pd_rd_wg=eJFZ3&pf_rd_p=d2879d59-7642-4685-b55e-4ac84515d99b&pf_rd_r=R6RKRHGQG1RYQ60JVFAP&qid=1761405384&sbo=RZvfv%2F%2FHxDF%2BO5021pAnSA%3D%3D&sr=1-2-183302c6-8dec-4386-8e58-6031e7be5ad8-spons&sp_csd=d2lkZ2V0TmFtZT1zcF9zZWFyY2hfdGhlbWF0aWM&th=1&psc=1';

echo "=== HTML CONTENT ANALYSIS ===\n";

try {
    $service = new UrlMetadataService();
    
    // Use reflection to get HTML
    $reflection = new ReflectionClass($service);
    $fetchMethod = $reflection->getMethod('fetchUrlContent');
    $fetchMethod->setAccessible(true);
    $html = $fetchMethod->invoke($service, $url);
    
    if ($html === false) {
        echo "Failed to fetch HTML\n";
        exit;
    }
    
    echo "HTML Length: " . strlen($html) . " characters\n\n";
    
    // Show first 500 characters
    echo "First 500 characters:\n";
    echo htmlspecialchars(substr($html, 0, 500)) . "\n\n";
    
    // Show last 500 characters
    echo "Last 500 characters:\n";
    echo htmlspecialchars(substr($html, -500)) . "\n\n";
    
    // Look for any price-related content
    if (preg_match_all('/price[^>]*>.*?<\/[^>]*>/i', $html, $matches)) {
        echo "Price-related HTML:\n";
        foreach ($matches[0] as $match) {
            echo htmlspecialchars($match) . "\n";
        }
    }
    
    // Look for any dollar signs
    if (preg_match_all('/\$[^<\s]+/', $html, $matches)) {
        echo "\nDollar amounts found:\n";
        foreach ($matches[0] as $match) {
            echo htmlspecialchars($match) . "\n";
        }
    }
    
    // Save full HTML to file
    file_put_contents('amazon_full.html', $html);
    echo "\nFull HTML saved to amazon_full.html\n";
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

echo "\n=== ANALYSIS COMPLETE ===\n";
