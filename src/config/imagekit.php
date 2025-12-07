<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Storage Disk
    |--------------------------------------------------------------------------
    |
    | The storage disk to use for saving images.
    | You can use any disk defined in config/filesystems.php (local, public, s3, etc.)
    |
    */
    'disk' => 'public',

    /*
    |--------------------------------------------------------------------------
    | Default Image Path
    |--------------------------------------------------------------------------
    |
    | The default path where images will be saved (relative to disk root).
    | For local disk: 'uploads/images'
    | For S3: 'uploads/images' (will be saved in S3 bucket)
    |
    */
    'default_saved_path' => 'uploads/images',

    /*
    |--------------------------------------------------------------------------
    | Allowed Extensions
    |--------------------------------------------------------------------------
    |
    | The allowed image file extensions.
    |
    */
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp'],

    /*
    |--------------------------------------------------------------------------
    | Max File Size
    |--------------------------------------------------------------------------
    |
    | Maximum file size in kilobytes (KB). Set to null for no limit.
    | Example: 5120 = 5MB, 10240 = 10MB
    |
    */
    'max_file_size' => null, // null = no limit, or set value in KB (e.g., 5120 for 5MB)

    /*
    |--------------------------------------------------------------------------
    | Max Image Dimensions
    |--------------------------------------------------------------------------
    |
    | Maximum width and height for uploaded images in pixels.
    | Set to null for no limit.
    |
    */
    'max_dimensions' => [
        'width' => null, // null = no limit
        'height' => null, // null = no limit
    ],

    /*
    |--------------------------------------------------------------------------
    | File Naming Strategy
    |--------------------------------------------------------------------------
    |
    | The strategy to use for generating file names.
    | Options: 'default', 'uuid', 'hash', 'timestamp'
    | Or provide a custom callable function.
    |
    | - 'default': image_timestamp_random (e.g., image_1234567890_abc123def456)
    | - 'uuid': UUID v4 (requires ramsey/uuid package)
    | - 'hash': Hash-based name (e.g., hash_content_time)
    | - 'timestamp': timestamp_random (e.g., 1234567890_abc123def456)
    | - callable: Custom function that receives the image and returns a string
    |
    */
    'naming_strategy' => 'default', // default, uuid, hash, timestamp, or callable

    /*
    |--------------------------------------------------------------------------
    | Single Image Resize Settings
    |--------------------------------------------------------------------------
    |
    | Default dimensions for resizing single images.
    | Set to null for no resizing.
    |
    */
    'dimensions' => [
        'width' => null, // Set default width (null if no resizing)
        'height' => null, // Set default height (null if no resizing)
    ],

    /*
    |--------------------------------------------------------------------------
    | Aspect Ratio
    |--------------------------------------------------------------------------
    |
    | Whether to maintain the aspect ratio when resizing images.
    | If set to true, images will maintain their original aspect ratio.
    | If set to false, images will be resized to exact dimensions (may cause distortion).
    |
    */
    'aspectRatio' => true, // Maintain aspect ratio when resizing (recommended: true)

    /*
    |--------------------------------------------------------------------------
    | Multi-Size Resize Settings
    |--------------------------------------------------------------------------
    |
    | Settings for resizing images into multiple sizes.
    |
    */
    'enable_multi_size' => false, // Enable or disable resizing images into multiple sizes

    /*
    |--------------------------------------------------------------------------
    | Multi Size Options
    |--------------------------------------------------------------------------
    |
    | The default sizes to use when enable_multi_size is true.
    | This should be an array of size names that exist in multi_size_dimensions.
    | These sizes will be used if 'enable_multi_size' is set to true and no custom sizes are provided.
    |
    */
    'multi_size_options' => ['small', 'medium', 'large'], // Default multi-size options

    /*
    |--------------------------------------------------------------------------
    | Multi Size Dimensions
    |--------------------------------------------------------------------------
    |
    | The dimensions for each resize size.
    | These dimensions define the actual width and height for each size name.
    |
    */
    'multi_size_dimensions' => [
        'small' => ['width' => 300, 'height' => 300],
        'medium' => ['width' => 600, 'height' => 600],
        'large' => ['width' => 1024, 'height' => 1024],
    ],

    /*
    |--------------------------------------------------------------------------
    | Compression Settings
    |--------------------------------------------------------------------------
    |
    | Default quality for image compression (0-100).
    | - If set to null, the quality will be calculated dynamically based on the image size.
    | - 100 = highest quality, largest file size
    | - 50 = lower quality, smaller file size
    | - Recommended: 75-90 for good quality/size balance
    |
    */
    'compression_quality' => null, // Set to null for dynamic quality, or 0-100 for fixed quality

    /*
    |--------------------------------------------------------------------------
    | Watermark Settings
    |--------------------------------------------------------------------------
    |
    | Settings for applying watermarks to images.
    |
    */
    'enable_watermark' => false, // Enable or disable watermarking images by default

    /*
    |--------------------------------------------------------------------------
    | Default Watermark Configuration
    |--------------------------------------------------------------------------
    |
    | The default settings for applying watermarks.
    | These settings are used when enable_watermark is true or when watermark is applied via code.
    |
    */
    'watermark' => [
        'image' => 'watermark.png', // Path to the watermark image (relative to storage disk or absolute path)
        'position' => 'bottom-right', // Position: top-left, top-right, bottom-left, bottom-right, center
        'opacity' => 50, // Opacity: 0-100 (0 = transparent, 100 = opaque)
        'x' => 10, // Horizontal offset in pixels from the position
        'y' => 10, // Vertical offset in pixels from the position
        'width' => null, // Watermark width in pixels (null = use original size)
        'height' => null, // Watermark height in pixels (null = use original size)
    ],

    /*
    |--------------------------------------------------------------------------
    | Watermark Storage Path
    |--------------------------------------------------------------------------
    |
    | The default path where uploaded watermark images will be saved.
    | This path is relative to the storage disk root.
    |
    */
    'watermark_storage_path' => 'watermarks',
];
