<?php

namespace DevMahmoudMustafa\ImageKit\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class CompressionService
{
    protected string $disk;

    public function __construct()
    {
        $this->disk = config('imagekit.disk', 'public');
    }

    /**
     * Set the storage disk.
     *
     * @param string $disk
     * @return $this
     */
    public function setDisk(string $disk): self
    {
        $this->disk = $disk;
        return $this;
    }

    /**
     * Get the current storage disk.
     *
     * @return string
     */
    public function getDisk(): string
    {
        return $this->disk;
    }
    /**
     * Compress the image.
     *
     * @param string $imagePath
     * @param int|null $quality
     */
    public function compressImage($imagePath, $compressionRatio)
    {
        $qualityDefault = null;
        if ($compressionRatio)
        {
            $qualityDefault = $compressionRatio;
        }elseif (config('imagekit.compression_quality')){
            $qualityDefault = config('imagekit.compression_quality');
        }

        // imagePath should be relative to disk root
        if (!Storage::disk($this->disk)->exists($imagePath)) {
            throw new \InvalidArgumentException('Image file does not exist: ' . $imagePath);
        }
        
        // Read image from storage
        $imageContent = Storage::disk($this->disk)->get($imagePath);
        $image = Image::make($imageContent);
        
        // Determine image format from extension
        $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
        $format = match(strtolower($extension)) {
            'jpg', 'jpeg' => 'jpg',
            'png' => 'png',
            'webp' => 'webp',
            default => null,
        };
        
        $quality = $qualityDefault ?? $this->calculateQuality($image->filesize());
        
        // Save compressed image back to storage, preserving original format
        Storage::disk($this->disk)->put($imagePath, $image->encode($format, $quality));
        
        $image->destroy(); // Free up memory
    }

    /**
     * Calculate compression quality based on file size.
     * Size is in bytes.
     *
     * @param int $size File size in bytes
     * @return int Quality value (0-100)
     */
    private function calculateQuality(int $size): int
    {
        // Convert bytes to KB for comparison
        $sizeKB = $size / 1024;
        
        if ($sizeKB <= 100) return 95;   // Small files - high quality
        if ($sizeKB <= 500) return 85;   // Medium files - good quality
        if ($sizeKB <= 1000) return 75;  // Large files - medium quality
        if ($sizeKB <= 2000) return 60;  // Very large files - lower quality
        return 50; // Huge files - lowest acceptable quality
    }

}
