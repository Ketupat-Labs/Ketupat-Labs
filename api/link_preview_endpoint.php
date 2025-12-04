<?php

header('Content-Type: application/json');
require_once '../config/database.php';

function sendResponse($status, $data, $message = '') {
    http_response_code($status);
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

function extractUrl($text) {
    // Regex to find URLs
    $pattern = '/(https?:\/\/[^\s]+)/i';
    preg_match($pattern, $text, $matches);
    return $matches[1] ?? null;
}

function isValidUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

function fetchLinkPreview($url) {
    // Initialize preview data
    $preview = [
        'url' => $url,
        'title' => '',
        'description' => '',
        'image' => '',
        'site_name' => '',
        'favicon' => ''
    ];
    
    // Set user agent to avoid blocking
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Accept-Encoding: gzip, deflate',
                'Connection: keep-alive',
            ],
            'timeout' => 10,
            'follow_location' => true,
            'max_redirects' => 5
        ]
    ]);
    
    try {
        // Fetch the HTML content
        $html = @file_get_contents($url, false, $context);
        
        if ($html === false) {
            return $preview;
        }
        
        // Extract domain for site name
        $parsedUrl = parse_url($url);
        $preview['site_name'] = $parsedUrl['host'] ?? '';
        $preview['favicon'] = ($parsedUrl['scheme'] ?? 'https') . '://' . ($parsedUrl['host'] ?? '') . '/favicon.ico';
        
        // Create DOMDocument to parse HTML
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        
        // Extract Open Graph tags
        $ogTitle = $xpath->query("//meta[@property='og:title']/@content");
        if ($ogTitle->length > 0) {
            $preview['title'] = trim($ogTitle->item(0)->nodeValue);
        }
        
        $ogDescription = $xpath->query("//meta[@property='og:description']/@content");
        if ($ogDescription->length > 0) {
            $preview['description'] = trim($ogDescription->item(0)->nodeValue);
        }
        
        $ogImage = $xpath->query("//meta[@property='og:image']/@content");
        if ($ogImage->length > 0) {
            $imageUrl = trim($ogImage->item(0)->nodeValue);
            // Convert relative URLs to absolute
            if (!preg_match('/^https?:\/\//', $imageUrl)) {
                $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
                if (isset($parsedUrl['port'])) {
                    $baseUrl .= ':' . $parsedUrl['port'];
                }
                if (strpos($imageUrl, '/') !== 0) {
                    $baseUrl .= '/';
                }
                $imageUrl = $baseUrl . $imageUrl;
            }
            $preview['image'] = $imageUrl;
        }
        
        $ogSiteName = $xpath->query("//meta[@property='og:site_name']/@content");
        if ($ogSiteName->length > 0) {
            $preview['site_name'] = trim($ogSiteName->item(0)->nodeValue);
        }
        
        // Fallback to standard meta tags if Open Graph not available
        if (empty($preview['title'])) {
            $titleTag = $xpath->query("//title");
            if ($titleTag->length > 0) {
                $preview['title'] = trim($titleTag->item(0)->nodeValue);
            }
        }
        
        if (empty($preview['description'])) {
            $metaDescription = $xpath->query("//meta[@name='description']/@content");
            if ($metaDescription->length > 0) {
                $preview['description'] = trim($metaDescription->item(0)->nodeValue);
            }
        }
        
        // Fallback: extract first image from page
        if (empty($preview['image'])) {
            $images = $xpath->query("//img[@src]");
            if ($images->length > 0) {
                $firstImage = $images->item(0)->getAttribute('src');
                if (!preg_match('/^https?:\/\//', $firstImage)) {
                    $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
                    if (isset($parsedUrl['port'])) {
                        $baseUrl .= ':' . $parsedUrl['port'];
                    }
                    if (strpos($firstImage, '/') !== 0) {
                        $baseUrl .= '/';
                    }
                    $firstImage = $baseUrl . $firstImage;
                }
                $preview['image'] = $firstImage;
            }
        }
        
        // Limit description length
        if (strlen($preview['description']) > 200) {
            $preview['description'] = substr($preview['description'], 0, 197) . '...';
        }
        
    } catch (Exception $e) {
        error_log("Error fetching link preview: " . $e->getMessage());
    }
    
    return $preview;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method !== 'GET') {
    sendResponse(405, null, 'Method not allowed');
}

$url = $_GET['url'] ?? '';

if (empty($url)) {
    sendResponse(400, null, 'URL parameter is required');
}

if (!isValidUrl($url)) {
    sendResponse(400, null, 'Invalid URL');
}

$preview = fetchLinkPreview($url);

sendResponse(200, $preview, 'Link preview fetched');

