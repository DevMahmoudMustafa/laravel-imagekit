# Test Suite Documentation

## Overview

This test suite provides comprehensive coverage for the ImageKit package, including unit tests for individual services and feature tests for end-to-end functionality.

## Running Tests

```bash
# From the package directory
cd packages/DevMahmoudMustafa/ImageKit

# Install dependencies (if not already installed)
composer install

# Run all tests
vendor/bin/phpunit

# Run with coverage
vendor/bin/phpunit --coverage-html coverage

# Run specific test suite
vendor/bin/phpunit tests/Unit
vendor/bin/phpunit tests/Feature

# Run specific test file
vendor/bin/phpunit tests/Unit/StorageServiceTest.php

# Run with testdox (readable output)
vendor/bin/phpunit --testdox
```

## Test Structure

### Unit Tests (`tests/Unit/`)

Tests for individual service classes:

- **ValidationServiceTest.php** - Tests for image validation, extension validation, dimension validation, watermark validation
- **StorageServiceTest.php** - Tests for saving/deleting images, disk management
- **ResizeServiceTest.php** - Tests for image resizing (single and multiple sizes)
- **CompressionServiceTest.php** - Tests for image compression
- **WatermarkServiceTest.php** - Tests for watermark application

### Feature Tests (`tests/Feature/`)

End-to-end tests for complete functionality:

- **ImageKitServiceTest.php** - Main service tests, fluent API, gallery, events
- **StorageDiskTest.php** - Tests for different storage disks (local, S3, etc.)
- **EventsTest.php** - Tests for event dispatching (ImageSaving, ImageSaved, ImageDeleted)
- **FluentAPITest.php** - Tests for fluent API aliases
- **FileNamingStrategyTest.php** - Tests for different file naming strategies
- **ImageRetrievalTest.php** - Tests for image retrieval methods (getImage, response, download, URL, etc.)

## Test Coverage

### ✅ Validation Service
- Image file validation
- Extension validation
- File size validation
- Image dimensions validation
- Image name validation
- Path validation
- Resize options validation
- Watermark validation

### ✅ Storage Service
- Saving images
- Deleting images
- Disk management
- Custom names and extensions
- File existence checks
- URL and path generation

### ✅ Resize Service
- Single size resizing
- Multi-size resizing
- Aspect ratio handling
- Disk support

### ✅ Compression Service
- Image compression
- Quality calculation
- Disk support

### ✅ Watermark Service
- Watermark application
- Position handling
- Opacity support
- Disk support (local and storage)

### ✅ ImageKitService (Feature)
- Image saving
- Gallery saving
- Fluent API
- State management
- Event dispatching
- Error handling
- Method chaining

### ✅ Storage Disks
- Public disk
- S3 disk
- Dynamic disk switching
- Disk propagation to all services

### ✅ Events
- ImageSaving event
- ImageSaved event
- ImageDeleted event
- Event data validation

### ✅ Fluent API
- All method aliases
- Method chaining
- Gallery operations

### ✅ File Naming
- Default strategy
- Timestamp strategy
- Hash strategy
- Custom names

### ✅ Image Retrieval
- Get image content
- Get image response (display in browser)
- Download image
- Get image URL
- Get image path
- Check if image exists
- Temporary URL for cloud storage
- Custom disk support

## Writing New Tests

### Example Unit Test

```php
/** @test */
public function it_can_do_something()
{
    // Arrange
    $service = new SomeService();
    
    // Act
    $result = $service->doSomething();
    
    // Assert
    $this->assertTrue($result);
}
```

### Example Feature Test

```php
/** @test */
public function it_can_save_image_with_watermark()
{
    Storage::fake('public');
    $image = UploadedFile::fake()->image('test.jpg');
    $watermark = UploadedFile::fake()->image('watermark.png');
    
    Storage::disk('public')->put('watermark.png', file_get_contents($watermark->getRealPath()));
    
    $imageName = ImageKit::image($image)
        ->watermark('watermark.png', 'bottom-right', 50)
        ->save();
    
    $this->assertNotNull($imageName);
    Storage::disk('public')->assertExists('uploads/images/' . $imageName);
}
```

## Test Helpers

The `TestCase` class provides:
- Laravel application setup with Orchestra Testbench
- Storage fakes for testing
- Database setup (SQLite in-memory)
- Package service provider registration

## Notes

- All tests use `Storage::fake()` to avoid touching real files
- Events are faked using `Event::fake()` to verify dispatching
- Images are created using `UploadedFile::fake()` for consistent testing
- Tests are isolated and can run in any order

## Continuous Integration

These tests are designed to run in CI/CD pipelines:
- No external dependencies required
- Fast execution time
- Deterministic results
- Good coverage percentage

