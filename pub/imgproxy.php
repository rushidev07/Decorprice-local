<?php
/**
 * Local dev image proxy — fetches missing media from production and caches locally.
 * Placed at pub/imgproxy.php (outside pub/media so PHP execution is allowed).
 *
 * Flow:
 *   Browser → decorprice.local:8080/media/x.jpg (missing)
 *   → pub/media/.htaccess rewrites to /imgproxy.php?img=x.jpg
 *   → This script fetches https://www.decorprice.com/media/x.jpg
 *   → Saves to pub/media/x.jpg (next request is served directly by Apache)
 *   → Streams image to browser
 */

// --- Security: only image extensions ---
$imgPath = ltrim($_GET['img'] ?? '', '/');

if (!preg_match('/\.(jpg|jpeg|png|gif|webp|avif|avifs|svg|ico)$/i', $imgPath)) {
    http_response_code(403);
    exit('Forbidden');
}

// Prevent directory traversal
$imgPath = str_replace(['..', "\0"], '', $imgPath);
if (empty($imgPath)) {
    http_response_code(400);
    exit('Bad request');
}

$localPath  = __DIR__ . '/media/' . $imgPath;
$remoteUrl  = 'https://www.decorprice.com/media/' . $imgPath;

// --- Fetch from production ---
$ch = curl_init($remoteUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS      => 3,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    CURLOPT_HTTPHEADER     => [
        'Referer: https://www.decorprice.com/',
        'Accept: image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
    ],
]);

$imageData   = curl_exec($ch);
$httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: 'image/jpeg';
curl_close($ch);

if ($imageData === false || $httpCode !== 200) {
    http_response_code(404);
    exit('Image not found on production');
}

// --- Cache locally so Apache serves it directly next time ---
$dir = dirname($localPath);
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}
file_put_contents($localPath, $imageData);

// --- Stream to browser ---
header('Content-Type: ' . explode(';', $contentType)[0]);
header('Content-Length: ' . strlen($imageData));
header('Cache-Control: public, max-age=86400');
echo $imageData;
