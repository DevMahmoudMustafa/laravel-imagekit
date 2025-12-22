<p align="center">
  <img src="https://raw.githubusercontent.com/DevMahmoudMustafa/laravel-imagekit/main/assets/imageKit.png"  alt="Laravel ImageKit">
</p>


# Laravel ImageKit

[![Latest Version](https://img.shields.io/badge/version-1.1.0-blue.svg)](https://packagist.org/packages/devmahmoudmustafa/laravel-imagekit)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-blue.svg)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/laravel-%5E9.0%7C%5E10.0%7C%5E11.0%7C%5E12.0-red.svg)](https://laravel.com)

## Overview

The **Laravel ImageKit** package (v1.1.0) is a comprehensive image management tool that allows developers to handle various image operations such as uploading, resizing, watermarking, compressing, and storing images. This package integrates seamlessly with Laravel's Storage system, providing full support for local storage, cloud storage (S3, GCS, Azure), and maximum flexibility for diverse use cases.

---

## Table of Contents

1. [Features](#features)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Quick Start](#quick-start)
5. [Storage Disks](#storage-disks)
6. [Methods Reference](#methods-reference)
7. [Examples](#examples)
8. [Events](#events)
9. [Error Handling](#error-handling)
10. [Support and Contribution](#support-and-contribution)

---

## Features

The ImageKit package provides a comprehensive set of features for image management:

### 📤 Image Upload & Validation
- **Simple Image Upload** - Upload images with minimal configuration
- **Automatic Validation** - Built-in validation for file types, size, and dimensions
- **Multiple Format Support** - Supports JPG, JPEG, PNG, and WebP formats
- **File Size Validation** - Configurable maximum file size limits
- **Dimension Validation** - Optional maximum width and height validation
- **Extension Validation** - Configurable allowed file extensions

### 🖼️ Image Resizing
- **Single Size Resizing** - Resize images to specific dimensions
- **Multi-Size Resizing** - Generate multiple sizes (thumbnail, small, medium, large, etc.) in one operation
- **Aspect Ratio Control** - Maintain or override aspect ratio when resizing
- **Flexible Dimensions** - Resize by width, height, or both
- **Dynamic Sizing** - Create custom size presets for your application

### 🗜️ Image Compression
- **Quality Control** - Adjustable compression quality (0-100)
- **Dynamic Quality** - Automatic quality calculation based on file size
- **Format Optimization** - Optimize images for web delivery
- **Size Reduction** - Reduce file sizes while maintaining visual quality

### 🎨 Watermarking
- **Image Watermarking** - Add watermarks to images with full control
- **Uploaded Watermark Support** - Use uploaded watermark images (not just file paths)
- **Position Control** - Place watermarks in 5 positions: top-left, top-right, bottom-left, bottom-right, center
- **Opacity Control** - Adjust watermark opacity (0-100)
- **Offset Control** - Fine-tune watermark position with pixel offsets
- **Resizable Watermarks** - Resize watermark images dynamically
- **Multiple Watermark Sources** - Use file paths or uploaded files

### 💾 Storage Management
- **Multiple Storage Disks** - Support for local, public, S3, GCS, Azure, and custom disks
- **Dynamic Disk Switching** - Switch between storage disks on the fly
- **Custom Storage Paths** - Configure custom paths for different image types
- **Cloud Storage Ready** - Full support for cloud storage services
- **Storage Abstraction** - Works with any Laravel storage driver

### 📁 File Naming Strategies
- **Default Naming** - Timestamp-based naming with random strings
- **UUID Naming** - Generate UUID-based filenames
- **Hash Naming** - Use hash-based naming for consistent files
- **Timestamp Naming** - Simple timestamp-based naming
- **Custom Naming** - Create your own naming strategy with callable functions
- **Custom Names** - Specify exact filenames when needed

### 🖼️ Gallery Operations
- **Multiple Image Upload** - Upload and process multiple images at once
- **Batch Processing** - Apply same transformations to multiple images
- **Database Integration** - Generate database-ready arrays with metadata
- **Alt Text Support** - Add alt text (single or per-image) to gallery images
- **Foreign Key Support** - Automatically link gallery images to database records

### 🗑️ Image Deletion
- **Single Image Deletion** - Delete individual images with optional size variants
- **Gallery Deletion** - Delete multiple images at once
- **Multi-Size Cleanup** - Automatically delete all size variants (small, medium, large, etc.)
- **Safe Deletion** - Validation and error handling for deletion operations

### 🔍 Image Retrieval
- **Get Image Content** - Retrieve raw image data from storage
- **Display in Browser** - Serve images directly to browsers with proper headers
- **Download Images** - Force download images with custom filenames
- **Get Image URLs** - Generate public URLs for images
- **Get Image Paths** - Retrieve full filesystem paths
- **Check Existence** - Verify if images exist before operations
- **Temporary URLs** - Generate temporary URLs for cloud storage (S3, etc.)
- **Cache Control** - Set cache headers for optimal performance

### 🎯 Fluent API
- **Method Chaining** - Chain multiple operations together
- **Intuitive Methods** - Clean, readable method names
- **Multiple Aliases** - Choose the method name that fits your style
- **Type-Safe** - Full type hints for better IDE support

### 📢 Events System
- **ImageSaving Event** - Fired before image is saved (with all processing options)
- **ImageSaved Event** - Fired after successful save (with image details)
- **ImageDeleted Event** - Fired after deletion attempt (with success status)
- **Event Listeners** - Full Laravel event system integration
- **Custom Logic** - Hook into image processing workflow

### � Flexible Return Data
- **Configurable Return Keys** - Choose exactly what data to return after saving
- **Single or Multiple Keys** - Return string for one key, array for multiple
- **Image Metadata** - Get name, path, size, dimensions, URL, hash, and more
- **Size Tracking** - Track both original and final file sizes (in KB)
- **Compression Analysis** - Compare sizes before and after modifications

### �🛠️ Configuration & Customization
- **Comprehensive Config** - Extensive configuration file for all settings
- **Default Values** - Sensible defaults that work out of the box
- **Runtime Configuration** - Override defaults at runtime
- **Flexible Settings** - Configure paths, sizes, quality, and more

### ✅ Error Handling
- **Validation Errors** - Clear, descriptive validation error messages
- **Custom Exceptions** - Specific exception types for different errors
- **Graceful Failures** - Proper error handling throughout

### 🔄 State Management
- **Automatic Reset** - State automatically resets after operations
- **Manual Reset** - Reset state manually when needed
- **Fresh Instances** - Create new instances to avoid state pollution
- **Singleton Support** - Works perfectly with singleton pattern

---

## Installation

### Step 1: Install the Package

Install the package using Composer:

```bash
composer require devmahmoudmustafa/laravel-imagekit
```

### Step 2: Publish the Configuration File

Publish the configuration file to customize default settings:

```bash
php artisan vendor:publish --tag=imagekit-config
```

This command will create a `imagekit.php` file in the `config` directory.

---

## Configuration

### Default Configuration

The package comes with a comprehensive configuration file. Here's what you can configure:

```php
return [
    // Storage disk (local, public, s3, gcs, azure, etc.)
    'disk' => 'public',
    
    // Default image path (relative to disk root)
    'default_saved_path' => 'uploads/images',
    
    // Allowed file extensions
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp'],
    
    // Maximum file size in KB (null = no limit)
    'max_file_size' => null,
    
    // Maximum image dimensions in pixels (null = no limit)
    'max_dimensions' => [
        'width' => null,
        'height' => null,
    ],
    
    // File naming strategy (default, uuid, hash, timestamp, or callable)
    'naming_strategy' => 'default',
    
    // Enable multi-size resizing globally
    'enable_multi_size' => false,
    
    // Multi-size options (size names to use when enable_multi_size is true)
    'multi_size_options' => ['small', 'medium', 'large'],
    
    // Multi-size dimensions configuration (actual width and height for each size)
    'multi_size_dimensions' => [
        'small' => ['width' => 300, 'height' => 300],
        'medium' => ['width' => 600, 'height' => 600],
        'large' => ['width' => 1024, 'height' => 1024],
    ],
    
    // Default dimensions for single resize
    'dimensions' => [
        'width' => null,
        'height' => null,
    ],
    
    // Maintain aspect ratio when resizing
    'aspectRatio' => true,
    
    // Default compression quality (null = dynamic)
    'compression_quality' => null,
    
    // Enable watermark globally
    'enable_watermark' => false,
    
    // Watermark settings
    'watermark' => [
        'image' => 'watermark.png',
        'position' => 'bottom-right',
        'opacity' => 50,
        'x' => 10,
        'y' => 10,
        'width' => null,
        'height' => null,
    ],
    
    // Watermark storage path (for uploaded watermarks)
    'watermark_storage_path' => 'watermarks',
    
    // Return keys - specify which data to return after saving
    'return_keys' => ['name'],
];
```

### Configuration Options Explained

#### `disk`
The storage disk to use. Can be any disk defined in `config/filesystems.php`:
- `'local'` - Local storage
- `'public'` - Public storage
- `'s3'` - Amazon S3
- `'gcs'` - Google Cloud Storage
- `'azure'` - Azure Blob Storage
- Any custom disk

#### `default_saved_path`
Default path where images are saved (relative to disk root).

#### `allowed_extensions`
Allowed image file extensions for uploads.

#### `max_file_size`
Maximum file size in kilobytes. Set to `null` for no limit.

#### `max_dimensions`
Maximum image dimensions in pixels. Set to `null` for no limit.

#### `naming_strategy`
File naming strategy:
- `'default'` - `image_timestamp_random`
- `'uuid'` - UUID v4 (requires `ramsey/uuid` package)
- `'hash'` - Hash-based name
- `'timestamp'` - `timestamp_random`
- `callable` - Custom function

#### `enable_multi_size`
Enable global multi-size resizing. When enabled, images are automatically resized to multiple sizes.

#### `multi_size_dimensions`
Define custom size dimensions for multi-size resizing.

#### `dimensions`
Default dimensions for single image resize.

#### `aspectRatio`
Whether to maintain aspect ratio when resizing (default: `true`).

#### `compression_quality`
Default compression quality (0-100). Set to `null` for dynamic quality based on file size.

#### `enable_watermark`
Enable watermark globally for all images.

#### `watermark`
Default watermark settings:
- `image` - Path to watermark image (relative to disk or absolute path)
- `position` - Position: `top-left`, `top-right`, `bottom-left`, `bottom-right`, `center`
- `opacity` - Opacity (0-100)
- `x`, `y` - Offset in pixels
- `width` - Watermark width in pixels (null = use original size)
- `height` - Watermark height in pixels (null = use original size)

#### `watermark_storage_path`
Default path where uploaded watermark images are saved (relative to disk root). This path is used when you pass an `UploadedFile` as the watermark image.

#### `return_keys`
Specify which data to return after saving an image. Available keys:
- `name` - Image filename
- `path` - Directory path
- `full_path` - Full path (path + name)
- `size` - Final file size in KB (after all modifications)
- `original_size` - Original file size in KB (before modifications)
- `url` - Full URL to the image
- `extension` - File extension (jpg, png, webp, etc.)
- `mime_type` - MIME type (image/jpeg, image/png, etc.)
- `width` - Image width in pixels
- `height` - Image height in pixels
- `disk` - Storage disk name
- `hash` - MD5 hash of the file
- `created_at` - Timestamp when saved

**Behavior:**
- If one key is specified → returns a `string` (or appropriate type)
- If multiple keys are specified → returns an `array`

**Default:** `['name']` (returns only the image name as string)

---

## Quick Start

### Basic Usage

```php
use DevMahmoudMustafa\ImageKit\Facades\ImageKit;

// Simple upload
$imageName = ImageKit::image($request->file('image'))
    ->save();

// With resizing
$imageName = ImageKit::image($request->file('image'))
    ->resize(800, 600)
    ->save();

// With compression
$imageName = ImageKit::image($request->file('image'))
    ->compress(85)
    ->save();

// With watermark
$imageName = ImageKit::image($request->file('image'))
    ->watermark('watermark.png', 'bottom-right', 50)
    ->save();
```

### Using Cloud Storage

```php
// Save to S3
$imageName = ImageKit::setDisk('s3')
    ->image($request->file('image'))
    ->save();

// Save to Google Cloud Storage
$imageName = ImageKit::setDisk('gcs')
    ->image($request->file('image'))
    ->save();
```

---

## Storage Disks

The package fully supports Laravel's Storage abstraction, allowing you to use any storage driver.

### Setting Disk

```php
// Method 1: Set disk globally
ImageKit::setDisk('s3')
    ->image($image)
    ->save();

// Method 2: Using config
// Set in config/imagekit.php: 'disk' => 's3'

// Method 3: Dynamic switching
ImageKit::setDisk('public')->image($image1)->save();
ImageKit::setDisk('s3')->image($image2)->save();
```

### Default Storage Disks

#### Using the `public` Disk

When using the `public` disk (default), images are saved in `storage/app/public/` directory.

**Important:** To access images directly from URLs (e.g., `http://your-domain.com/storage/uploads/images/image.jpg`), you **must** create a symbolic link by running:

```bash
php artisan storage:link
```

This command creates a symbolic link from `public/storage` to `storage/app/public`, making stored files publicly accessible.

**Without running this command**, you won't be able to access images via direct URLs, and you'll need to use the package's `response()` or `getImageUrl()` methods to serve images.

**Example:**
```php
// Save to public disk
$imageName = ImageKit::setDisk('public')
    ->image($request->file('image'))
    ->save();

// Get URL (requires storage:link)
$url = ImageKit::getImageUrl('uploads/images/' . $imageName);
// Returns: http://your-domain.com/storage/uploads/images/image.jpg
```

### Creating Custom Storage Disks

You can create any custom disk configuration in `config/filesystems.php`. The package will work with any disk you define.

#### Example: Save Images Directly in `public/` Directory

If you want to save images directly in the `public/` folder (instead of `storage/app/public/`), create a custom disk:

1. Add the disk configuration in `config/filesystems.php`:

```php
'disks' => [
    // ... existing disks ...
    
    'public_dir' => [
        'driver' => 'local',
        'root' => public_path(),
        'url' => env('APP_URL'),
        'visibility' => 'public',
        'throw' => false,
        'report' => false,
    ],
],
```

2. Use the custom disk in your code:

```php
// Save directly to public/ directory
$imageName = ImageKit::setDisk('public_dir')
    ->image($request->file('image'))
    ->path('uploads/images')
    ->save();

// Images will be saved in: public/uploads/images/image.jpg
// Accessible via: http://your-domain.com/uploads/images/image.jpg
```

**Note:** Saving directly to `public/` folder doesn't require `storage:link` command, but it's generally not recommended for production as files in `public/` are directly accessible and harder to manage.

#### Example: Custom Local Disk

You can create disks pointing to any directory:

```php
'disks' => [
    'media' => [
        'driver' => 'local',
        'root' => storage_path('app/media'),
        'url' => env('APP_URL').'/media',
        'visibility' => 'public',
    ],
    
    'backups' => [
        'driver' => 'local',
        'root' => storage_path('backups'),
        'visibility' => 'private',
    ],
],
```

Then use them:

```php
// Use custom disk
ImageKit::setDisk('media')
    ->image($request->file('image'))
    ->save();
```

### Cloud Storage Setup

#### Amazon S3

1. Configure in `config/filesystems.php`:
```php
's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
],
```

2. Use in code:
```php
ImageKit::setDisk('s3')
    ->image($image)
    ->save();
```

#### Google Cloud Storage

Similar setup, use `'gcs'` as disk name.

#### Azure Blob Storage

Similar setup, use `'azure'` as disk name.

---

## Methods Reference

### Image Methods

#### `setImage($image)` / `image($image)`
Set the image file to be processed.

**Parameters:**
- `$image` - `Illuminate\Http\UploadedFile`

**Example:**
```php
ImageKit::image($request->file('image'))
```

#### `setImages(array $images)` / `images(array $images)`
Set multiple images for gallery processing.

**Example:**
```php
ImageKit::images($request->file('gallery'))
```

---

### Path & Name Methods

#### `setImagePath($path)` / `path($path)` / `saveTo($path)`
Set the path where images will be saved (relative to disk root).

**Example:**
```php
ImageKit::path('products/images')
    ->image($image)
    ->save();
```

#### `setImageName($name)` / `name($name)`
Set a custom name for the image file.

**Example:**
```php
ImageKit::image($image)
    ->name('my-image')
    ->save();
```

#### `setExtension($extension)` / `extension($extension)`
Set a custom file extension.

**Example:**
```php
ImageKit::image($image)
    ->extension('webp')
    ->save();
```

---

### Resize Methods

#### `setDimensions($width, $height)` / `resize($width, $height)` / `dimensions($width, $height)`
Resize image to specific dimensions.

**Parameters:**
- `$width` - Width in pixels (null to maintain aspect ratio)
- `$height` - Height in pixels (null to maintain aspect ratio)

**Example:**
```php
// Resize to exact dimensions
ImageKit::image($image)
    ->resize(800, 600)
    ->save();

// Resize width only (height auto)
ImageKit::image($image)
    ->resize(800, null)
    ->save();

// Resize height only (width auto)
ImageKit::image($image)
    ->resize(null, 600)
    ->save();
```

#### `setMultiSizeOptions($sizes)` / `sizes($sizes)`
Resize image to multiple sizes.

**Parameters:**
- `$sizes` - Array of size names (must exist in `multi_size_dimensions` config)

**Example:**
```php
ImageKit::image($image)
    ->sizes(['small', 'medium', 'large'])
    ->save();

// This creates:
// - small_image_name.jpg
// - medium_image_name.jpg
// - large_image_name.jpg
```

---

### Compression Methods

#### `compressImage($compress, $quality)` / `compress($compress, $quality)`
Compress the image.

**Parameters:**
- `$compress` - Enable/disable compression (default: `true`)
- `$quality` - Quality 0-100 (null = dynamic based on file size)

**Example:**
```php
// Enable compression with specific quality
ImageKit::image($image)
    ->compress(85)
    ->save();

// Disable compression
ImageKit::image($image)
    ->compress(false)
    ->save();
```

---

### Watermark Methods

#### `setWatermark(...)` / `watermark(...)`
Apply watermark to the image.

**Parameters:**
- `$imagePathOrFile` - Path to watermark image (relative to disk or absolute) OR `UploadedFile` instance
- `$position` - Position: `top-left`, `top-right`, `bottom-left`, `bottom-right`, `center`
- `$opacity` - Opacity 0-100
- `$offset` - Array with `x` and `y` keys
- `$x` - Horizontal offset
- `$y` - Vertical offset
- `$width` - Watermark width in pixels (null = use original size)
- `$height` - Watermark height in pixels (null = use original size)

**Example:**
```php
// Simple watermark with path
ImageKit::image($image)
    ->watermark('watermark.png', 'bottom-right', 50)
    ->save();

// Watermark with UploadedFile
ImageKit::image($image)
    ->watermark($request->file('watermark'), 'bottom-right', 50)
    ->save();

// With custom offset
ImageKit::image($image)
    ->watermark('watermark.png', 'bottom-right', 50, null, 20, 20)
    ->save();

// With offset array
ImageKit::image($image)
    ->watermark('watermark.png', 'center', 75, ['x' => 10, 'y' => 10])
    ->save();

// With watermark dimensions (resize watermark)
ImageKit::image($image)
    ->watermark('watermark.png', 'bottom-right', 50, null, null, null, 100, 100)
    ->save();

// Watermark with UploadedFile and dimensions
ImageKit::image($image)
    ->watermark($request->file('watermark'), 'bottom-right', 50, null, null, null, 150, 150)
    ->save();
```

---

### Storage Methods

#### `setDisk($disk)`
Set the storage disk to use.

**Parameters:**
- `$disk` - Disk name (e.g., `'public'`, `'s3'`, `'gcs'`)

**Example:**
```php
ImageKit::setDisk('s3')
    ->image($image)
    ->save();
```

#### `getDisk()`
Get the current storage disk being used.

**Returns:** `string` - Current disk name

**Example:**
```php
$currentDisk = ImageKit::getDisk();
// Returns: 'public' (or whatever disk is currently set)
```

---

### Save Methods

#### `saveImage()` / `save()`
Save the image after applying all modifications.

**Returns:** `string|array` - Based on `return_keys` config:
- Single key → `string` (or appropriate type)
- Multiple keys → `array` with requested data

**Example:**
```php
// Default (return_keys = ['name'])
$imageName = ImageKit::image($image)
    ->resize(800, 600)
    ->compress(85)
    ->save();
// Returns: "image_123456.jpg"

// With multiple keys (return_keys = ['name', 'size', 'url'])
$result = ImageKit::image($image)->save();
// Returns: ['name' => 'image_123456.jpg', 'size' => 150.25, 'url' => 'http://...']
```

#### `saveGallery($imageColumnName, $fkColumnName, $fkId, $altText)`
Save multiple images as a gallery.

**Parameters:**
- `$imageColumnName` - Database column name for image filename (optional)
- `$fkColumnName` - Foreign key column name (optional)
- `$fkId` - Foreign key value (optional)
- `$altText` - Alt text - can be string or array (optional)

**Returns:** `array` - Array of results based on `return_keys` config:
- Single key without metadata → array of strings
- Multiple keys or with metadata → array of arrays

**Example:**
```php
// Simple gallery (return_keys = ['name'])
$images = ImageKit::images($request->file('gallery'))
    ->saveGallery();
// Returns: ['image1.jpg', 'image2.jpg']

// Simple gallery (return_keys = ['name', 'size', 'url'])
$images = ImageKit::images($request->file('gallery'))
    ->saveGallery();
// Returns: [
//     ['name' => 'image1.jpg', 'size' => 150.25, 'url' => '...'],
//     ['name' => 'image2.jpg', 'size' => 200.50, 'url' => '...']
// ]

// With database metadata
$rows = ImageKit::images($request->file('gallery'))
    ->saveGallery('image_name', 'product_id', 123, 'Product Image');

// With array of alt texts
$rows = ImageKit::images($request->file('gallery'))
    ->saveGallery('image_name', 'product_id', 123, ['Alt 1', 'Alt 2', 'Alt 3']);
```

---

### Utility Methods

#### `make()`
Create a fresh instance of the service (useful for avoiding state pollution).

**Example:**
```php
$service1 = ImageKit::make()->image($image1)->save();
$service2 = ImageKit::make()->image($image2)->save();
```

#### `reset()`
Reset the service to initial state.

**Example:**
```php
ImageKit::reset()
    ->image($image)
    ->save();
```

---

### Delete Methods

#### `deleteImage($imageName, $path, $sizes)`
Delete a single image.

**Parameters:**
- `$imageName` - Image filename (can include full path if `$path` is null)
- `$path` - Path relative to disk root (optional). If null or empty, path will be extracted from `$imageName`
- `$sizes` - Array of sizes to delete (optional)

**Returns:** `bool`

**Throws:** `InvalidArgumentException` if image is in root directory when path is extracted from imageName

**Examples:**
```php
// With explicit path
$deleted = ImageKit::deleteImage('image.jpg', 'uploads/images', ['small', 'medium', 'large']);

// Path extracted from imageName
$deleted = ImageKit::deleteImage('uploads/images/image.jpg', null, ['small', 'medium', 'large']);

// Path can be empty string (same as null)
$deleted = ImageKit::deleteImage('uploads/images/image.jpg', '');
```

#### `deleteGallery($imagesNames, $path, $sizes)`
Delete multiple images.

**Parameters:**
- `$imagesNames` - Array of image filenames (can include full paths if `$path` is null)
- `$path` - Path relative to disk root (optional). If null or empty, path will be extracted from each `$imageName`
- `$sizes` - Array of sizes to delete (optional)

**Returns:** `int` - Number of successfully deleted images

**Throws:** `InvalidArgumentException` if any image is in root directory when path is extracted from imageName

**Examples:**
```php
// With explicit path
$count = ImageKit::deleteGallery(
    ['image1.jpg', 'image2.jpg'],
    'uploads/images',
    ['small', 'medium', 'large']
);

// Path extracted from imageNames
$count = ImageKit::deleteGallery(
    ['uploads/images/image1.jpg', 'uploads/images/image2.jpg'],
    null,
    ['small', 'medium', 'large']
);
```

---

## Examples

### Example 1: Basic Upload

```php
use DevMahmoudMustafa\ImageKit\Facades\ImageKit;

$imageName = ImageKit::image($request->file('image'))
    ->save();
```

### Example 2: Resize and Compress

```php
$imageName = ImageKit::image($request->file('image'))
    ->resize(800, 600)
    ->compress(85)
    ->save();
```

### Example 3: Multi-Size with Watermark

```php
$imageName = ImageKit::image($request->file('image'))
    ->path('products')
    ->sizes(['thumbnail', 'medium', 'large'])
    ->watermark('watermark.png', 'bottom-right', 50)
    ->save();
```

### Example 3.1: Watermark with UploadedFile

```php
// Upload watermark image from request
$imageName = ImageKit::image($request->file('image'))
    ->watermark($request->file('watermark'), 'bottom-right', 50)
    ->save();

// The watermark will be automatically saved to the watermark_storage_path configured in config
```

### Example 3.2: Watermark with Dimensions

```php
// Resize watermark to specific dimensions
$imageName = ImageKit::image($request->file('image'))
    ->watermark('watermark.png', 'bottom-right', 50, null, null, null, 100, 100)
    ->save();

// With UploadedFile and dimensions
$imageName = ImageKit::image($request->file('image'))
    ->watermark($request->file('watermark'), 'bottom-right', 50, null, null, null, 150, 150)
    ->save();
```

### Example 4: Using Cloud Storage (S3)

```php
// Configure S3 in config/filesystems.php first
$imageName = ImageKit::setDisk('s3')
    ->image($request->file('image'))
    ->resize(1024, 1024)
    ->compress(90)
    ->save();
```

### Example 5: Custom File Naming

```php
// Using UUID naming strategy
// First, set in config: 'naming_strategy' => 'uuid'
$imageName = ImageKit::image($request->file('image'))
    ->save();

// Or use custom name
$imageName = ImageKit::image($request->file('image'))
    ->name('product-123')
    ->save();
```

### Example 6: Gallery with Database Integration

```php
$rows = ImageKit::images($request->file('gallery'))
    ->path('products/gallery')
    ->resize(800, 600)
    ->compress(85)
    ->saveGallery('image_name', 'product_id', $productId, 'Product Gallery Image');

// Insert into database
DB::table('product_images')->insert($rows);
```

### Example 7: Fluent API Chain

```php
$imageName = ImageKit::make()
    ->setDisk('s3')
    ->image($request->file('image'))
    ->name('custom-name')
    ->extension('webp')
    ->path('uploads/images')
    ->resize(1024, 1024)
    ->compress(90)
    ->watermark('watermark.png', 'bottom-right', 75, null, 20, 20)
    ->save();
```

### Example 8: Dynamic Disk Switching

```php
// Save some images to local storage
ImageKit::setDisk('public')
    ->images($localImages)
    ->saveGallery();

// Save others to S3
ImageKit::setDisk('s3')
    ->images($cloudImages)
    ->saveGallery();
```

### Example 8.1: Custom Return Keys

```php
// Configure in config/imagekit.php:
// 'return_keys' => ['name', 'size', 'original_size', 'url']

$result = ImageKit::image($request->file('image'))
    ->resize(800, 600)
    ->compress(85)
    ->save();

// Returns:
// [
//     'name' => 'image_123456.jpg',
//     'size' => 45.50,           // KB (after compression)
//     'original_size' => 150.25, // KB (before compression)
//     'url' => 'http://example.com/storage/uploads/images/image_123456.jpg'
// ]
```

### Example 8.2: Get All Image Metadata

```php
// Configure in config/imagekit.php:
// 'return_keys' => ['name', 'path', 'full_path', 'size', 'original_size', 'url', 'extension', 'mime_type', 'width', 'height', 'disk', 'hash', 'created_at']

$result = ImageKit::image($request->file('image'))->save();

// Returns all available data:
// [
//     'name' => 'image_123456.jpg',
//     'path' => 'uploads/images',
//     'full_path' => 'uploads/images/image_123456.jpg',
//     'size' => 45.50,
//     'original_size' => 150.25,
//     'url' => 'http://example.com/storage/uploads/images/image_123456.jpg',
//     'extension' => 'jpg',
//     'mime_type' => 'image/jpeg',
//     'width' => 800,
//     'height' => 600,
//     'disk' => 'public',
//     'hash' => 'a1b2c3d4e5f6...',
//     'created_at' => '2024-01-15 10:30:00'
// ]
```

### Example 8.3: Gallery with Return Keys

```php
// Configure: 'return_keys' => ['name', 'size', 'width', 'height']

$results = ImageKit::images($request->file('gallery'))
    ->resize(800, 600)
    ->saveGallery();

// Returns:
// [
//     ['name' => 'img1.jpg', 'size' => 45.50, 'width' => 800, 'height' => 600],
//     ['name' => 'img2.jpg', 'size' => 52.30, 'width' => 800, 'height' => 600],
// ]

// With database metadata
$rows = ImageKit::images($request->file('gallery'))
    ->saveGallery('image_name', 'product_id', 123);

// Returns:
// [
//     ['image_name' => 'img1.jpg', 'product_id' => 123, 'name' => 'img1.jpg', 'size' => 45.50, ...],
//     ['image_name' => 'img2.jpg', 'product_id' => 123, 'name' => 'img2.jpg', 'size' => 52.30, ...],
// ]
```

### Example 9: Display Image in Browser

```php
// In your controller
public function showImage($path)
{
    return ImageKit::response('uploads/images/' . $path);
}

// With cache (cache for 1 hour)
public function showImage($path)
{
    return ImageKit::response('uploads/images/' . $path, null, [
        'cache' => 3600
    ]);
}

// From S3
public function showImage($path)
{
    return ImageKit::response('uploads/images/' . $path, 's3');
}
```

### Example 10: Download Image

```php
// Download image
public function downloadImage($path)
{
    return ImageKit::download('uploads/images/' . $path, 'my-image.jpg');
}
```

### Example 11: Get Image URL

```php
// Get URL for displaying in view
$imageUrl = ImageKit::getImageUrl('uploads/images/test.jpg');
// Use in blade: <img src="{{ $imageUrl }}" />

// Get URL from S3
$imageUrl = ImageKit::getImageUrl('uploads/images/test.jpg', 's3');
```

### Example 12: Temporary URL (Cloud Storage)

```php
// Get temporary URL that expires in 1 hour
$tempUrl = ImageKit::temporaryUrl('uploads/images/test.jpg', now()->addHour());

// Useful for private S3 files
$tempUrl = ImageKit::temporaryUrl('uploads/images/private.jpg', 3600, 's3');
```

---

## Image Retrieval Methods

All image retrieval methods support an optional `$disk` parameter. If not provided, the current disk from config or the last set disk will be used.

### Retrieval Methods

#### `getImage($path, $disk)`
Get image content from storage.

**Parameters:**
- `$path` - Relative path on the storage disk
- `$disk` - Optional disk name (uses config disk if not provided)

**Returns:** `string|null` - Image content or null if file doesn't exist

**Example:**
```php
$content = ImageKit::getImage('uploads/images/test.jpg');

// From specific disk
$content = ImageKit::getImage('uploads/images/test.jpg', 's3');
```

#### `response($path, $disk, $options)`
Get HTTP response for displaying image in browser.

**Parameters:**
- `$path` - Relative path on the storage disk
- `$disk` - Optional disk name
- `$options` - Array of options:
  - `contentType` - Custom MIME type (auto-detected if not provided)
  - `headers` - Custom headers array
  - `cache` - Cache control (false = no cache, or number = max-age in seconds)
  - `disposition` - 'inline' (default) or 'attachment'
  - `filename` - Custom filename for Content-Disposition

**Returns:** `\Illuminate\Http\Response`

**Example:**
```php
// Simple response
return ImageKit::response('uploads/images/test.jpg');

// With cache control
return ImageKit::response('uploads/images/test.jpg', null, [
    'cache' => 3600 // Cache for 1 hour
]);

// No cache
return ImageKit::response('uploads/images/test.jpg', null, [
    'cache' => false
]);

// From S3
return ImageKit::response('uploads/images/test.jpg', 's3');
```

#### `download($path, $name, $disk, $options)`
Get download response for image.

**Parameters:**
- `$path` - Relative path on the storage disk
- `$name` - Optional download filename
- `$disk` - Optional disk name
- `$options` - Additional options (headers, etc.)

**Returns:** `\Symfony\Component\HttpFoundation\StreamedResponse`

**Example:**
```php
return ImageKit::download('uploads/images/test.jpg', 'my-image.jpg');

// From specific disk
return ImageKit::download('uploads/images/test.jpg', 'my-image.jpg', 's3');
```

#### `getImageUrl($path, $disk)`
Get the full URL for an image.

**Parameters:**
- `$path` - Relative path on the storage disk
- `$disk` - Optional disk name

**Returns:** `string` - Full URL

**Example:**
```php
$url = ImageKit::getImageUrl('uploads/images/test.jpg');
// Returns: http://example.com/storage/uploads/images/test.jpg

// From S3
$url = ImageKit::getImageUrl('uploads/images/test.jpg', 's3');
```

#### `getImagePath($path, $disk)`
Get the full filesystem path for an image (for local disk operations).

**Parameters:**
- `$path` - Relative path on the storage disk
- `$disk` - Optional disk name

**Returns:** `string` - Full filesystem path

**Example:**
```php
$fullPath = ImageKit::getImagePath('uploads/images/test.jpg');
```

#### `imageExists($path, $disk)`
Check if image file exists.

**Parameters:**
- `$path` - Relative path on the storage disk
- `$disk` - Optional disk name

**Returns:** `bool`

**Example:**
```php
if (ImageKit::imageExists('uploads/images/test.jpg')) {
    // Image exists
}
```

#### `temporaryUrl($path, $expiration, $disk, $options)`
Get temporary URL for image (useful for cloud storage like S3).

**Parameters:**
- `$path` - Relative path on the storage disk
- `$expiration` - Expiration time (DateTimeInterface, DateInterval, or int seconds)
- `$disk` - Optional disk name
- `$options` - Additional options

**Returns:** `string` - Temporary URL

**Example:**
```php
// URL expires in 1 hour
$url = ImageKit::temporaryUrl('uploads/images/test.jpg', now()->addHour());

// URL expires in 30 minutes
$url = ImageKit::temporaryUrl('uploads/images/test.jpg', 1800, 's3');
```

---

## Events

The package fires events at key points during image processing. See [EVENTS.md](EVENTS.md) for complete documentation.

### Available Events

- **`ImageSaving`** - Fired before image is saved
- **`ImageSaved`** - Fired after image is successfully saved
- **`ImageDeleted`** - Fired after image deletion attempt

### Example: Listening to Events

```php
use DevMahmoudMustafa\ImageKit\Events\ImageSaved;
use Illuminate\Support\Facades\Event;

Event::listen(ImageSaved::class, function (ImageSaved $event) {
    \Log::info('Image saved', [
        'name' => $event->imageName,
        'path' => $event->path,
        'fullPath' => $event->fullPath,
    ]);
});
```

---

## Error Handling

The package throws `InvalidArgumentException` for validation errors. Always use try-catch blocks:

```php
try {
    $imageName = ImageKit::image($request->file('image'))
        ->resize(800, 600)
        ->save();
} catch (\InvalidArgumentException $e) {
    return back()->withErrors(['image' => $e->getMessage()]);
}
```

### Common Errors

- **Invalid file type** - File extension not in `allowed_extensions`
- **File too large** - Exceeds `max_file_size` limit
- **Image too large** - Exceeds `max_dimensions` limit
- **Watermark not found** - Watermark image file doesn't exist
- **Invalid dimensions** - Invalid width/height values

---

## Support and Contribution

For support or to contribute to this package, visit the [GitHub repository](https://github.com/devmahmoudmustafa/laravel-imagekit).

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
