<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Helpers\Helper;

class ImageController extends Controller
{
    protected $helper;

    public function __construct(Helper $helper)
    {
        $this->helper = $helper;
    }

    public function proxyImage(Request $request)
    {
        $url = $request->query('url');

        if (!$url) {
            return $this->helper->Error(400, 'No URL provided');
        }

        if (str_contains($url, 'favicon.ico')) {
            return $this->helper->Error(400, 'Favicon requests are not supported');
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return $this->helper->Error(400, 'Invalid image URL');
        }

        try {
            $response = Http::timeout(10)
                ->withOptions(['verify' => false]) 
                ->get($url);

            if ($response->failed()) {
                return $this->helper->Error(500, 'Failed to fetch image: HTTP ' . $response->status());
            }

            $contentType = $response->header('Content-Type', 'image/jpeg');

            if (!str_starts_with($contentType, 'image/')) {
                return $this->helper->Error(400, 'URL does not point to a valid image');
            }

            return response($response->body(), 200)
                ->header('Content-Type', $contentType)
                ->header('Cache-Control', 'public, max-age=3600');
        } catch (\Exception $e) {
            \Log::error("Image proxy error for URL: $url, Error: {$e->getMessage()}");
            return $this->helper->Error(500, 'Failed to fetch image: ' . $e->getMessage());
        }
    }
}