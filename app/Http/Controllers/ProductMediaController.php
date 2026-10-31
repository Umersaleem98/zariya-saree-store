<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ProductMediaController extends Controller
{
    public function upload(Request $r)
    {
        $r->validate(['images' => 'required|array|min:1|max:4', 'images.*' => 'required|file|image|mimes:jpg,jpeg,png,webp|max:4096|dimensions:max_width=4096,max_height=4096']);
        $directory = storage_path('app/public/products');
        File::ensureDirectoryExists($directory, 0750);
        $paths = [];
        foreach ($r->file('images') as $file) {
            [$sourceWidth, $sourceHeight] = getimagesize($file->getRealPath());
            abort_if($sourceWidth * $sourceHeight > 12000000, 422, 'Use images up to 12 megapixels.');
            $source = imagecreatefromstring(file_get_contents($file->getRealPath()));
            abort_unless($source, 422, 'This image could not be read.');
            if ($file->getMimeType() === 'image/jpeg' && function_exists('exif_read_data')) {
                $orientation = @exif_read_data($file->getRealPath())['Orientation'] ?? 1;
                if (in_array($orientation, [2, 4, 5, 7])) imageflip($source, IMG_FLIP_HORIZONTAL);
                $degrees = match ($orientation) { 3, 4 => 180, 5, 6 => -90, 7, 8 => 90, default => 0 };
                if ($degrees) { $rotated = imagerotate($source, $degrees, 0); imagedestroy($source); $source = $rotated; }
            }
            $scale = min(1, 1400 / max(imagesx($source), imagesy($source)));
            $width = max(1, (int) round(imagesx($source) * $scale)); $height = max(1, (int) round(imagesy($source) * $scale));
            $output = imagecreatetruecolor($width, $height);
            imagealphablending($output, false); imagesavealpha($output, true);
            imagefill($output, 0, 0, imagecolorallocatealpha($output, 0, 0, 0, 127));
            imagecopyresampled($output, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));
            $name = bin2hex(random_bytes(16)).'.webp';
            $saved = imagewebp($output, $directory.'/'.$name, 80);
            imagedestroy($source); imagedestroy($output);
            unset($source, $output);
            abort_unless($saved && filesize($directory.'/'.$name) > 0, 500, 'Image processing failed.');
            chmod($directory.'/'.$name, 0640);
            $paths[] = '/media/products/'.$name;
        }
        return response()->json(['images' => $paths], 201);
    }

    public function show(string $file)
    {
        abort_unless(preg_match('/^[a-f0-9]{32}\.webp$/D', $file), 404);
        $path = storage_path('app/public/products/'.$file);
        abort_unless(is_file($path), 404);
        return response()->file($path, ['Content-Type' => 'image/webp', 'Cache-Control' => 'public, max-age=31536000, immutable', 'X-Content-Type-Options' => 'nosniff']);
    }
}
