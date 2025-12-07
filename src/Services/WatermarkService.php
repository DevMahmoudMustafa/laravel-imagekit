<?php

namespace DevMahmoudMustafa\ImageKit\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class WatermarkService
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
     * Apply a watermark to the image.
     *
     * @param string $imagePath
     * @param array $watermark
     */
    public function applyWatermark(string $imagePath, array $watermark)
    {
        if (!isset($watermark['image']) || empty($watermark['image'])) {
            throw new \InvalidArgumentException('Watermark image path is required.');
        }

        // Check if main image exists
        if (!Storage::disk($this->disk)->exists($imagePath)) {
            throw new \InvalidArgumentException('Image file does not exist: ' . $imagePath);
        }
        
        // Read main image from storage
        $imageContent = Storage::disk($this->disk)->get($imagePath);
        $image = Image::make($imageContent);
        
        // Handle watermark image path (can be absolute path or relative to disk)
        $wImagePath = $watermark['image'];
        
        // Check if watermark is absolute path or relative
        if (str_starts_with($wImagePath, '/') || preg_match('/^[a-zA-Z]:\\\\/', $wImagePath)) {
            // Absolute path - read from filesystem
            if (!file_exists($wImagePath)) {
                throw new \InvalidArgumentException('Watermark image file does not exist: ' . $wImagePath);
            }
            $wImage = Image::make($wImagePath);
        } else {
            // Relative path - try to read from storage disk
            if (!Storage::disk($this->disk)->exists($wImagePath)) {
                // Fallback to public_path for backward compatibility
                $fallbackPath = public_path($wImagePath);
                if (!file_exists($fallbackPath)) {
                    throw new \InvalidArgumentException('Watermark image file does not exist: ' . $wImagePath);
                }
                $wImage = Image::make($fallbackPath);
            } else {
                $watermarkContent = Storage::disk($this->disk)->get($wImagePath);
                $wImage = Image::make($watermarkContent);
            }
        }
        
        $wPosition = $watermark['position'] ?? 'bottom-right';
        $wX = $watermark['x'] ?? 10;
        $wY = $watermark['y'] ?? 10;

        // Resize watermark if dimensions are provided
        if (isset($watermark['width']) || isset($watermark['height'])) {
            $wWidth = $watermark['width'] ?? null;
            $wHeight = $watermark['height'] ?? null;
            
            if ($wWidth || $wHeight) {
                $wImage->resize($wWidth, $wHeight, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
        }

        if (isset($watermark['opacity']) && $watermark['opacity'] < 100) {
            $opacity = $watermark['opacity'];
            $wImage->opacity($opacity);
        }

        // Determine image format from extension
        $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
        $format = match(strtolower($extension)) {
            'jpg', 'jpeg' => 'jpg',
            'png' => 'png',
            'webp' => 'webp',
            default => null,
        };
        
        // Apply watermark
        $image->insert($wImage, $wPosition, $wX, $wY);
        
        // Save back to storage, preserving original format
        Storage::disk($this->disk)->put($imagePath, $image->encode($format));
        
        $image->destroy(); // Free up memory
        $wImage->destroy(); // Free up memory
    }

    /**
     * Resize watermark image and create a resized copy.
     *
     * @param string $watermarkPath Path to watermark image
     * @param int|null $width Width in pixels
     * @param int|null $height Height in pixels
     * @return string Path to resized watermark copy
     */
    public function resizeWatermarkImage(string $watermarkPath, ?int $width, ?int $height): string
    {
        if (($width === null && $height === null) || ($width === 0 && $height === 0)) {
            return $watermarkPath;
        }

        // Get watermark image content
        $wImage = null;
        if (str_starts_with($watermarkPath, '/') || preg_match('/^[a-zA-Z]:\\\\/', $watermarkPath)) {
            // Absolute path
            if (!file_exists($watermarkPath)) {
                throw new \InvalidArgumentException('Watermark image file does not exist: ' . $watermarkPath);
            }
            $wImage = Image::make($watermarkPath);
        } else {
            // Relative path - try storage disk first
            if (Storage::disk($this->disk)->exists($watermarkPath)) {
                $watermarkContent = Storage::disk($this->disk)->get($watermarkPath);
                $wImage = Image::make($watermarkContent);
            } else {
                // Fallback to public_path
                $fallbackPath = public_path($watermarkPath);
                if (!file_exists($fallbackPath)) {
                    throw new \InvalidArgumentException('Watermark image file does not exist: ' . $watermarkPath);
                }
                $wImage = Image::make($fallbackPath);
            }
        }

        // Create resized copy
        $wImage->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
        });

        // Create new filename for resized watermark
        $pathInfo = pathinfo($watermarkPath);
        $dir = $pathInfo['dirname'] ?? '';
        $filename = $pathInfo['filename'] ?? 'watermark';
        $extension = $pathInfo['extension'] ?? 'png';
        $resizedPath = rtrim($dir, '/') . '/' . $filename . '_' . ($width ?? 'auto') . 'x' . ($height ?? 'auto') . '.' . $extension;

        // Determine format
        $format = match(strtolower($extension)) {
            'jpg', 'jpeg' => 'jpg',
            'png' => 'png',
            'webp' => 'webp',
            default => 'png',
        };

        // Save resized watermark
        if (str_starts_with($watermarkPath, '/') || preg_match('/^[a-zA-Z]:\\\\/', $watermarkPath)) {
            // Absolute path - save to same location
            $wImage->save($resizedPath);
        } else {
            // Relative path - save to storage
            Storage::disk($this->disk)->put($resizedPath, $wImage->encode($format));
        }

        $wImage->destroy(); // Free up memory

        return $resizedPath;
    }
}
