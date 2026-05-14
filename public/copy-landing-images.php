<?php
/**
 * Copy AI-generated landing images from the project's temp staging area.
 * The source files are inside the DDEV-mounted project directory.
 * DELETE THIS FILE after use.
 */

$projectRoot = dirname(__DIR__); // /var/www/html in DDEV
$source = $projectRoot . '/storage/app/landing-staging';
$dest = __DIR__ . '/assets/landing';

if (!is_dir($dest)) {
    mkdir($dest, 0755, true);
}

header('Content-Type: text/html; charset=utf-8');
echo "<h2>Landing Page Image Copy</h2>";
echo "<p>Source: {$source} (exists: " . (is_dir($source) ? 'yes' : 'no') . ")</p>";
echo "<p>Dest: {$dest} (exists: " . (is_dir($dest) ? 'yes' : 'no') . ")</p>";

$mappings = [
    'hero.png' => 'hero.webp',
    'produce.png' => 'produce.webp',
    'household.png' => 'household.webp',
    'beverages.png' => 'beverages.webp',
    'personal-care.png' => 'personal-care.webp',
];

$allGood = true;
foreach ($mappings as $srcFile => $destFile) {
    $srcPath = $source . '/' . $srcFile;
    $destPath = $dest . '/' . $destFile;
    
    if (file_exists($srcPath)) {
        if (copy($srcPath, $destPath)) {
            echo "<p>✅ Copied {$srcFile} → {$destFile} (" . number_format(filesize($destPath)) . " bytes)</p>";
        } else {
            echo "<p>❌ Failed to copy {$srcFile}</p>";
            $allGood = false;
        }
    } else {
        echo "<p>⚠️ Source not found: {$srcPath}</p>";
        $allGood = false;
    }
}

if ($allGood) {
    echo "<p style='color:green'><strong>All images copied!</strong> <a href='/'>← View Landing Page</a></p>";
} else {
    echo "<p style='color:orange'>Some files missing. Run this in your terminal first:</p>";
    echo "<pre>mkdir -p storage/app/landing-staging\n";
    echo "cp ~/.gemini/antigravity/brain/74a0c4f8-fafd-4060-aa91-1fc18c102e95/hero_*.png storage/app/landing-staging/hero.png\n";
    echo "cp ~/.gemini/antigravity/brain/74a0c4f8-fafd-4060-aa91-1fc18c102e95/produce_*.png storage/app/landing-staging/produce.png\n";
    echo "cp ~/.gemini/antigravity/brain/74a0c4f8-fafd-4060-aa91-1fc18c102e95/household_*.png storage/app/landing-staging/household.png\n";
    echo "cp ~/.gemini/antigravity/brain/74a0c4f8-fafd-4060-aa91-1fc18c102e95/beverages_*.png storage/app/landing-staging/beverages.png\n";
    echo "cp ~/.gemini/antigravity/brain/74a0c4f8-fafd-4060-aa91-1fc18c102e95/personal_care_*.png storage/app/landing-staging/personal-care.png</pre>";
    echo "<p>Then reload this page.</p>";
}

echo "<p style='color:red'>⚠️ DELETE this file after use: public/copy-landing-images.php</p>";
