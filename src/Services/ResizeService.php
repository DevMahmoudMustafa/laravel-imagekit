<?php

namespace DevMahmoudMustafa\ImageKit\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ResizeService
{
    const IMAGE_SIZE = [
        'small' => ['width' => 300, 'height' => 300],
        'medium' => ['width' => 600, 'height' => 600],
        'large' => ['width' => 1024, 'height' => 1024],
    ];

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
     * Resize the image to specific dimensions.
     *
     * @param string $imagePath Full path to the image (path + filename)
     * @param array $dimensions
     * @param bool $aspectRatio
     * @return \Intervention\Image\Image|null
     */
    public function setDimensionsImage(string $imagePath, array $dimensions, bool $aspectRatio)
    {
        $width = $dimensions['width'] ?? null;
        $height = $dimensions['height'] ?? null;

        if ($width || $height) {
            if (!Storage::disk($this->disk)->exists($imagePath)) {
                throw new \InvalidArgumentException('Image file does not exist: ' . $imagePath);
            }
            
            // Read image content from storage
            $imageContent = Storage::disk($this->disk)->get($imagePath);
            
            // Determine image format from extension
            $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
            $format = match(strtolower($extension)) {
                'jpg', 'jpeg' => 'jpg',
                'png' => 'png',
                'webp' => 'webp',
                default => null,
            };
            
            // Process image
            $image = Image::make($imageContent)
                ->resize($width, $height, function ($constraint) use ($aspectRatio){
                    if ($aspectRatio){
                        $constraint->aspectRatio();
                    }
                });

            // Save back to storage, preserving original format
            Storage::disk($this->disk)->put($imagePath, $image->encode($format));

            $image->destroy(); // Free up memory
        }

        return $image ?? null;
    }

    /**
     * Resize the image to multiple sizes.
     *
     * @param string $path
     * @param string $imageName
     * @param array $sizes
     */
    public function resizeImage(string $path, string $imageName, array $sizes)
    {
        // Get the resize options from the config file, or use the default sizes if not set
        $resizeOptions = config('imagekit.multi_size_dimensions', []);

        foreach ($sizes as $size) {
            // Check if the size exists in the resize options
            if (!isset($resizeOptions[$size])) {
                throw new \InvalidArgumentException("Size '{$size}' is not defined in resize options.");
            }

            // Get the width and height for the current size
            $width = $resizeOptions[$size]['width'] ?? null;
            $height = $resizeOptions[$size]['height'] ?? null;

            // Ensure both width and height are provided
            if ($width === null || $height === null) {
                throw new \InvalidArgumentException("Width and height for size '{$size}' must be defined.");
            }

            // Resize the image
            $originalPath = rtrim($path, '/') . '/' . $imageName;
            
            if (!Storage::disk($this->disk)->exists($originalPath)) {
                throw new \InvalidArgumentException("Original image file does not exist: {$originalPath}");
            }
            
            // Read original image
            $imageContent = Storage::disk($this->disk)->get($originalPath);
            
            // Determine image format from original extension
            $extension = pathinfo($imageName, PATHINFO_EXTENSION);
            $format = match(strtolower($extension)) {
                'jpg', 'jpeg' => 'jpg',
                'png' => 'png',
                'webp' => 'webp',
                default => null,
            };
            
            // Process and resize
            $image = Image::make($imageContent)
                ->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                });

            // Save resized version, preserving original format
            $resizedPath = rtrim($path, '/') . '/' . $size . '_' . $imageName;
            Storage::disk($this->disk)->put($resizedPath, $image->encode($format));

            // Free up memory
            $image->destroy();
        }
    }

}
