<?php
require_once 'vendor/autoload.php';
use App\Services\UrlMetadataService;

$service = new UrlMetadataService();
$result = $service->fetchMetadata('https://www.amazon.com/Sitsink-Dragon-Diamond-Painting-Magnets/dp/B0F1XS8ZK4/ref=pd_ci_mcx_mh_mcx_views_0_image?pd_rd_w=Q5NLZ&content-id=amzn1.sym.fa5f5a6f-007b-4550-8a8d-e53f286a7287%3Aamzn1.symc.294486fd-3479-4d36-ab6a-3dd8fa235a5b&pf_rd_p=fa5f5a6f-007b-4550-8a8d-e53f286a7287&pf_rd_r=MRTVGWR5SKHYQNV71K66&pd_rd_wg=43SCt&pd_rd_r=c4d7250f-accf-411d-94ad-0f6ad6ba1b0d&pd_rd_i=B0F1XS8ZK4&th=1');

echo "Success: " . ($result['success'] ? 'Yes' : 'No') . "\n";
echo "Title: " . $result['title'] . "\n";
echo "Price: " . $result['price'] . "\n";
echo "Error: " . $result['error'] . "\n";
?>