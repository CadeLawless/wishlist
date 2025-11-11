<?php
require 'vendor/autoload.php';

use App\Services\UrlMetadataService;

$service = new UrlMetadataService();

$url = 'https://www.etsy.com/listing/1043251248/anyone-can-cook-embroidered-sweatshirt?ref=user_profile&frs=1&pro=1&variation0=2136858430';

echo "Testing Etsy URL with variations:\n";
echo "$url\n\n";

$startTime = microtime(true);
$result = $service->fetchMetadata($url);
$duration = round(microtime(true) - $startTime, 2);

echo "Duration: {$duration}s\n\n";
echo "Result:\n";
print_r($result);
