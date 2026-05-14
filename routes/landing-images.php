<?php
// routes/landing-images.php
// Temporary route file to serve generated images from the artifact store
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

$imageDir = '/home/oluwadamilare/.gemini/antigravity/brain/74a0c4f8-fafd-4060-aa91-1fc18c102e95';

// Fallback: use project-local staging if host path not available
$localDir = storage_path('app/landing-staging');

Route::get('/landing-img/{name}', function (string $name) use ($imageDir, $localDir) {
    $fileMap = [
        'hero' => 'hero_1778552351404.png',
        'produce' => 'produce_1778552370432.png',
        'household' => 'household_1778552385701.png',
        'beverages' => 'beverages_1778552420519.png',
        'personal-care' => 'personal_care_1778552436029.png',
    ];
    
    if (!isset($fileMap[$name])) {
        abort(404);
    }
    
    // Try host path first, then local staging
    $path = $imageDir . '/' . $fileMap[$name];
    if (!file_exists($path)) {
        $path = $localDir . '/' . $name . '.png';
    }
    
    if (!file_exists($path)) {
        abort(404, 'Image not found');
    }
    
    return response()->file($path, [
        'Content-Type' => 'image/png',
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('name', '[a-z\-]+');
