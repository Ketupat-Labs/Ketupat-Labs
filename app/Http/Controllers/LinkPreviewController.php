<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LinkPreviewController extends Controller
{
    /**
     * Fetch link preview data (title, description, etc.)
     */
    public function fetch(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $url = $request->input('url');

        try {
            // Fetch the page content
            $response = Http::timeout(5)->get($url);
            
            if (!$response->successful()) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Failed to fetch URL',
                ], 400);
            }

            $html = $response->body();

            // Extract title from HTML
            $title = $this->extractTitle($html);
            
            // Extract description (meta description)
            $description = $this->extractMetaDescription($html);
            
            // Extract site name (og:site_name or domain)
            $siteName = $this->extractSiteName($html, $url);
            
            // Extract favicon
            $favicon = $this->extractFavicon($html, $url);
            
            // Extract image (og:image)
            $image = $this->extractImage($html, $url);

            return response()->json([
                'status' => 200,
                'data' => [
                    'url' => $url,
                    'title' => $title ?: parse_url($url, PHP_URL_HOST),
                    'description' => $description,
                    'site_name' => $siteName,
                    'favicon' => $favicon,
                    'image' => $image,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Link preview error: ' . $e->getMessage());
            
            // Return fallback data
            return response()->json([
                'status' => 200,
                'data' => [
                    'url' => $url,
                    'title' => parse_url($url, PHP_URL_HOST),
                    'description' => null,
                    'site_name' => parse_url($url, PHP_URL_HOST),
                    'favicon' => null,
                    'image' => null,
                ],
            ], 200);
        }
    }

    /**
     * Extract title from HTML
     */
    private function extractTitle($html)
    {
        // Try og:title first
        if (preg_match('/<meta\s+property=["\']og:title["\']\s+content=["\']([^"\']+)["\']/i', $html, $matches)) {
            return trim($matches[1]);
        }

        // Try regular title tag
        if (preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Extract meta description
     */
    private function extractMetaDescription($html)
    {
        // Try og:description first
        if (preg_match('/<meta\s+property=["\']og:description["\']\s+content=["\']([^"\']+)["\']/i', $html, $matches)) {
            return trim($matches[1]);
        }

        // Try regular meta description
        if (preg_match('/<meta\s+name=["\']description["\']\s+content=["\']([^"\']+)["\']/i', $html, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Extract site name
     */
    private function extractSiteName($html, $url)
    {
        // Try og:site_name
        if (preg_match('/<meta\s+property=["\']og:site_name["\']\s+content=["\']([^"\']+)["\']/i', $html, $matches)) {
            return trim($matches[1]);
        }

        // Fallback to domain
        return parse_url($url, PHP_URL_HOST);
    }

    /**
     * Extract favicon
     */
    private function extractFavicon($html, $url)
    {
        $baseUrl = parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST);
        
        // Try various favicon patterns
        $patterns = [
            '/<link[^>]+rel=["\'](?:shortcut\s+)?icon["\'][^>]+href=["\']([^"\']+)["\']/i',
            '/<link[^>]+href=["\']([^"\']+)["\'][^>]+rel=["\'](?:shortcut\s+)?icon["\']/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $favicon = trim($matches[1]);
                if (!preg_match('/^https?:\/\//', $favicon)) {
                    $favicon = $baseUrl . ($favicon[0] === '/' ? '' : '/') . $favicon;
                }
                return $favicon;
            }
        }

        // Default favicon location
        return $baseUrl . '/favicon.ico';
    }

    /**
     * Extract og:image
     */
    private function extractImage($html, $url)
    {
        $baseUrl = parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST);
        
        if (preg_match('/<meta\s+property=["\']og:image["\']\s+content=["\']([^"\']+)["\']/i', $html, $matches)) {
            $image = trim($matches[1]);
            if (!preg_match('/^https?:\/\//', $image)) {
                $image = $baseUrl . ($image[0] === '/' ? '' : '/') . $image;
            }
            return $image;
        }

        return null;
    }
}

