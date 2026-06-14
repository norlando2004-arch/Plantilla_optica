<?php

declare(strict_types=1);

// Usage:
//   php tools/crop_logo.php [input] [output] [padding]
// Defaults:
//   input:  public/image/logo-navbar.png
//   output: public/image/logo-navbar-cropped.png
//   padding: 4

$inputPath = $argv[1] ?? 'public/image/logo-navbar.png';
$outputPath = $argv[2] ?? 'public/image/logo-navbar-cropped.png';
$padding = (int) ($argv[3] ?? 4);

if (!file_exists($inputPath)) {
    fwrite(STDERR, "Input not found: {$inputPath}\n");
    exit(1);
}

if (!function_exists('imagecreatefrompng')) {
    fwrite(STDERR, "GD extension is required (imagecreatefrompng missing).\n");
    exit(1);
}

$src = @imagecreatefrompng($inputPath);
if ($src === false) {
    fwrite(STDERR, "Failed to load PNG: {$inputPath}\n");
    exit(1);
}

imagesavealpha($src, true);

$width = imagesx($src);
$height = imagesy($src);

$minX = $width;
$minY = $height;
$maxX = -1;
$maxY = -1;

for ($y = 0; $y < $height; $y++) {
    for ($x = 0; $x < $width; $x++) {
        $rgba = imagecolorat($src, $x, $y);
        $alpha = ($rgba & 0x7F000000) >> 24; // 0 (opaque) .. 127 (transparent)

        if ($alpha < 127) {
            if ($x < $minX) $minX = $x;
            if ($y < $minY) $minY = $y;
            if ($x > $maxX) $maxX = $x;
            if ($y > $maxY) $maxY = $y;
        }
    }
}

if ($maxX === -1 || $maxY === -1) {
    imagedestroy($src);
    fwrite(STDERR, "Image appears fully transparent; nothing to crop.\n");
    exit(1);
}

$minX = max(0, $minX - $padding);
$minY = max(0, $minY - $padding);
$maxX = min($width - 1, $maxX + $padding);
$maxY = min($height - 1, $maxY + $padding);

$cropWidth = ($maxX - $minX) + 1;
$cropHeight = ($maxY - $minY) + 1;

$dst = imagecreatetruecolor($cropWidth, $cropHeight);
imagealphablending($dst, false);
imagesavealpha($dst, true);

$transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
imagefilledrectangle($dst, 0, 0, $cropWidth, $cropHeight, $transparent);

imagecopy(
    $dst,
    $src,
    0,
    0,
    $minX,
    $minY,
    $cropWidth,
    $cropHeight
);

// Ensure output directory exists.
$outDir = dirname($outputPath);
if (!is_dir($outDir)) {
    @mkdir($outDir, 0777, true);
}

$ok = imagepng($dst, $outputPath, 9);
imagedestroy($dst);
imagedestroy($src);

if (!$ok) {
    fwrite(STDERR, "Failed to write: {$outputPath}\n");
    exit(1);
}

fwrite(STDOUT, "Cropped {$inputPath} ({$width}x{$height}) -> {$outputPath} ({$cropWidth}x{$cropHeight})\n");
