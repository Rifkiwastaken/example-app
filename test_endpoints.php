<?php
/**
 * Test critical endpoints after migration
 */

echo "==========================================\n";
echo "TESTING CRITICAL ENDPOINTS\n";
echo "==========================================\n\n";

$baseUrl = 'http://localhost:8000';

$endpoints = [
    'Landing Page' => '/',
    'Dashboard' => '/dashboard',
    'Plants List' => '/plants',
    'Plantings List' => '/plantings',
    'Harvests List' => '/harvests',
    'Certifications' => '/certifications',
    'Inventory Types' => '/seed-stock',
    'Warehouses' => '/warehouses',
    'Sales' => '/sales',
];

echo "Testing endpoints...\n\n";

foreach ($endpoints as $name => $path) {
    $url = $baseUrl . $path;
    
    echo "Testing: {$name}\n";
    echo "URL: {$url}\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ ERROR: {$error}\n";
    } elseif ($httpCode == 200) {
        echo "✅ SUCCESS (HTTP {$httpCode})\n";
        
        // Check for common error indicators in response
        if (stripos($response, 'SQLSTATE') !== false) {
            echo "⚠️  WARNING: SQL error detected in response\n";
            // Extract error message
            if (preg_match('/SQLSTATE\[.*?\]:.*?in \'where clause\'/', $response, $matches)) {
                echo "   Error: " . $matches[0] . "\n";
            }
        } elseif (stripos($response, 'Column not found') !== false) {
            echo "⚠️  WARNING: Column not found error\n";
            if (preg_match('/Column not found:.*?\'/', $response, $matches)) {
                echo "   Error: " . $matches[0] . "\n";
            }
        } elseif (stripos($response, 'ErrorException') !== false) {
            echo "⚠️  WARNING: Exception detected\n";
        }
    } elseif ($httpCode == 302 || $httpCode == 301) {
        echo "↪️  REDIRECT (HTTP {$httpCode}) - May need authentication\n";
    } elseif ($httpCode == 500) {
        echo "❌ SERVER ERROR (HTTP {$httpCode})\n";
        
        // Try to extract error message
        if (stripos($response, 'SQLSTATE') !== false) {
            if (preg_match('/SQLSTATE\[.*?\]:.*/', $response, $matches)) {
                echo "   SQL Error: " . substr($matches[0], 0, 200) . "...\n";
            }
        }
    } else {
        echo "⚠️  HTTP {$httpCode}\n";
    }
    
    echo "\n";
}

echo "==========================================\n";
echo "TEST COMPLETE\n";
echo "==========================================\n\n";

echo "Note: Some endpoints may require authentication.\n";
echo "If you see redirects (302), that's normal for protected routes.\n";
echo "If you see SQL errors, check the error message above.\n\n";
