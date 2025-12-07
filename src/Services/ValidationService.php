<?php

namespace DevMahmoudMustafa\ImageKit\Services;

use Illuminate\Support\Facades\Storage;

class ValidationService
{
    /**
     * Get allowed extensions from config or use default.
     *
     * @return array
     */
    protected function getAllowedExtensions(): array
    {
        return config('imagekit.allowed_extensions', ['jpg', 'jpeg', 'png', 'webp']);
    }

    /**
     * Validate the image file.
     *
     * @param mixed $image
     * @throws \InvalidArgumentException
     */
    public function validateImage($image)
    {
        if (!$image || !$image->isValid()) {
            throw new \InvalidArgumentException('Invalid image file provided.');
        }

        $extension = strtolower($image->extension());
        $allowedExtensions = $this->getAllowedExtensions();
        if (!in_array($extension, $allowedExtensions)) {
            throw new \InvalidArgumentException('Unsupported file extension. Allowed extensions are: ' . implode(', ', $allowedExtensions));
        }

        // Validate file size
        $maxFileSize = config('imagekit.max_file_size');
        if ($maxFileSize !== null && $image->getSize() > ($maxFileSize * 1024)) {
            throw new \InvalidArgumentException("File size exceeds maximum allowed size of {$maxFileSize}KB.");
        }

        // Validate image dimensions
        $this->validateImageDimensions($image);
    }

    /**
     * Validate image dimensions against max dimensions config.
     *
     * @param mixed $image
     * @throws \InvalidArgumentException
     */
    protected function validateImageDimensions($image)
    {
        $maxDimensions = config('imagekit.max_dimensions', []);
        $maxWidth = $maxDimensions['width'] ?? null;
        $maxHeight = $maxDimensions['height'] ?? null;

        if ($maxWidth === null && $maxHeight === null) {
            return; // No limits set
        }

        try {
            $imageInfo = getimagesize($image->getRealPath());
            if ($imageInfo === false) {
                return; // Can't determine dimensions, skip validation
            }

            [$width, $height] = $imageInfo;

            if ($maxWidth !== null && $width > $maxWidth) {
                throw new \InvalidArgumentException("Image width ({$width}px) exceeds maximum allowed width ({$maxWidth}px).");
            }

            if ($maxHeight !== null && $height > $maxHeight) {
                throw new \InvalidArgumentException("Image height ({$height}px) exceeds maximum allowed height ({$maxHeight}px).");
            }
        } catch (\InvalidArgumentException $e) {
            // Re-throw validation exceptions
            throw $e;
        } catch (\Exception $e) {
            // If we can't read image dimensions due to other errors, skip validation
            // This allows the validation to fail later if the image is actually invalid
        }
    }

    /**
     * Validate the image name.
     *
     * @param string $name
     * @throws \InvalidArgumentException
     */
    public function validateImageName(string $name)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException("Image name cannot be empty.");
        }
    }

    /**
     * Validate the file extension.
     *
     * @param string $extension
     * @throws \InvalidArgumentException
     */
    public function validateExtension(string $extension)
    {
        if (empty($extension)) {
            throw new \InvalidArgumentException("Extension cannot be empty.");
        }

        $allowedExtensions = $this->getAllowedExtensions();
        if (!in_array(strtolower($extension), $allowedExtensions)) {
            throw new \InvalidArgumentException("Invalid file extension. Allowed extensions are: " . implode(', ', $allowedExtensions));
        }
    }

    /**
     * Validate the image path.
     *
     * @param string $path
     * @throws \InvalidArgumentException
     */
    public function validatePath(string $path)
    {
        if (empty($path)) {
            throw new \InvalidArgumentException("Path is invalid or does not exist.");
        }

        // Reject absolute paths - only relative paths are allowed
        if (str_starts_with($path, '/') || preg_match('/^[a-zA-Z]:\\\\/', $path)) {
            throw new \InvalidArgumentException("Absolute paths are not allowed. Please use a relative path instead. Provided path: {$path}");
        }

        // Check for dangerous path patterns
        if (str_contains($path, '..') || str_contains($path, '//')) {
            throw new \InvalidArgumentException("Invalid path format. Path cannot contain '..' or '//'. Provided path: {$path}");
        }
    }

    /**
     * Validate the dimensions for resizing.
     *
     * @param int|null $width
     * @param int|null $height
     * @throws \InvalidArgumentException
     */
    public function validateDimensions($width, $height)
    {
        // Allow both to be null (no resizing) - validation only checks type, not presence
        // The actual check for at least one value is done at usage time if needed
        
        if (!is_numeric($width) && !is_null($width)) {
            throw new \InvalidArgumentException('Width must be a numeric value or null.');
        }
        if (!is_numeric($height) && !is_null($height)) {
            throw new \InvalidArgumentException('Height must be a numeric value or null.');
        }
    }

    /**
     * Validate the resize options.
     *
     * @param array $resize
     * @throws \InvalidArgumentException
     */
    public function validateResizeOptions(array $resize, ?array $resizeOptions = null)
    {
        if ($resizeOptions === null || empty($resizeOptions)) {
            throw new \InvalidArgumentException("Resize options are not defined. Please check 'imagekit.multi_size_dimensions' in your configuration.");
        }
        
        foreach ($resize as $size) {
            if (!array_key_exists($size, $resizeOptions)) {
                throw new \InvalidArgumentException("Size '{$size}' is not defined. Please check 'imagekit.multi_size_dimensions' in your configuration.");
            }
        }
    }

    /**
     * Validate the watermark settings.
     *
     * @param array $watermark
     * @param string|null $disk Optional disk name. If not provided, uses config default.
     * @throws \InvalidArgumentException
     */
    public function validateWatermark(array $watermark, ?string $disk = null)
    {
        // Only validate image if watermark array is not empty
        if (!empty($watermark) && !isset($watermark['image'])) {
            throw new \InvalidArgumentException('Watermark image file is required.');
        }
        
        // If watermark is empty, skip validation
        if (empty($watermark)) {
            return;
        }

        // Normalize path for checking
        $watermarkPath = $watermark['image'];
        
        // Check if absolute path
        $isAbsolute = str_starts_with($watermarkPath, '/') || preg_match('/^[a-zA-Z]:\\\\/', $watermarkPath);
        
        // For absolute paths, check filesystem
        if ($isAbsolute) {
            if (!file_exists($watermarkPath)) {
                throw new \InvalidArgumentException('Watermark image file does not exist: ' . $watermarkPath);
            }
        } else {
            // For relative paths, try Storage first, then fallback to public_path
            // This supports both cloud storage and local storage
            $storageDisk = $disk ?? config('imagekit.disk', 'public');
            if (!Storage::disk($storageDisk)->exists($watermarkPath)) {
                $fallbackPath = public_path($watermarkPath);
                if (!file_exists($fallbackPath)) {
                    throw new \InvalidArgumentException('Watermark image file does not exist: ' . $watermarkPath);
                }
            }
        }

        $validPositions = ['top-left', 'top-right', 'bottom-left', 'bottom-right', 'center'];
        if (isset($watermark['position']) && !in_array($watermark['position'], $validPositions)) {
            throw new \InvalidArgumentException('Invalid watermark position. Valid positions are: ' . implode(', ', $validPositions));
        }

        if (isset($watermark['opacity']) && ($watermark['opacity'] < 0 || $watermark['opacity'] > 100)) {
            throw new \InvalidArgumentException('Watermark opacity must be between 0 and 100');
        }

        $watermark['x'] = isset($watermark['x']) ? (int)$watermark['x'] : 10;
        $watermark['y'] = isset($watermark['y']) ? (int)$watermark['y'] : 10;
        if ($watermark['x'] < 0 || $watermark['y'] < 0) {
            throw new \InvalidArgumentException('Watermark x and y coordinates cannot be negative.');
        }
    }
}
