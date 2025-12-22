<?php

namespace DevMahmoudMustafa\ImageKit\Tests\Unit;

use DevMahmoudMustafa\ImageKit\Services\StorageService;
use DevMahmoudMustafa\ImageKit\Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StorageServiceTest extends TestCase
{
    protected StorageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('s3');
        $this->service = new StorageService();
    }

    /** @test */
    public function it_can_save_an_image()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        $result = $this->service->saveOriginal($image, 'uploads/images', null, null);

        $this->assertArrayHasKey('imageName', $result);
        $this->assertArrayHasKey('imagePath', $result);
        $this->assertArrayHasKey('fullPath', $result);
        $this->assertArrayHasKey('image', $result);
        Storage::disk('public')->assertExists($result['fullPath']);
    }

    /** @test */
    public function it_can_save_image_with_custom_name()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        $result = $this->service->saveOriginal($image, 'uploads/images', 'custom-name', null);

        $this->assertEquals('custom-name.jpg', $result['imageName']);
        Storage::disk('public')->assertExists('uploads/images/custom-name.jpg');
    }

    /** @test */
    public function it_can_save_image_with_custom_extension()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        $result = $this->service->saveOriginal($image, 'uploads/images', 'custom-name', 'webp');

        $this->assertEquals('custom-name.webp', $result['imageName']);
        Storage::disk('public')->assertExists('uploads/images/custom-name.webp');
    }

    /** @test */
    public function it_generates_default_filename()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        $result = $this->service->saveOriginal($image, 'uploads/images', null, null);

        $this->assertNotEmpty($result['imageName']);
        $this->assertStringContainsString('.jpg', $result['imageName']);
    }

    /** @test */
    public function it_can_set_and_get_disk()
    {
        $this->service->setDisk('s3');
        
        $this->assertEquals('s3', $this->service->getDisk());
    }

    /** @test */
    public function it_can_delete_an_image()
    {
        Storage::disk('public')->put('uploads/images/test.jpg', 'fake content');
        
        $result = $this->service->deleteImage('test.jpg', 'uploads/images', []);
        
        $this->assertTrue($result);
        Storage::disk('public')->assertMissing('uploads/images/test.jpg');
    }

    /** @test */
    public function it_can_delete_resized_versions()
    {
        Storage::disk('public')->put('uploads/images/test.jpg', 'fake content');
        Storage::disk('public')->put('uploads/images/small_test.jpg', 'fake content');
        Storage::disk('public')->put('uploads/images/medium_test.jpg', 'fake content');
        
        $result = $this->service->deleteImage('test.jpg', 'uploads/images', ['small', 'medium']);
        
        $this->assertTrue($result);
        Storage::disk('public')->assertMissing('uploads/images/test.jpg');
        Storage::disk('public')->assertMissing('uploads/images/small_test.jpg');
        Storage::disk('public')->assertMissing('uploads/images/medium_test.jpg');
    }

    /** @test */
    public function it_returns_false_when_deleting_nonexistent_image()
    {
        $result = $this->service->deleteImage('nonexistent.jpg', 'uploads/images', []);
        
        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_delete_image_without_path_extracting_from_image_name()
    {
        Storage::disk('public')->put('uploads/images/test.jpg', 'fake content');
        
        $result = $this->service->deleteImage('uploads/images/test.jpg', null);
        
        $this->assertTrue($result);
        Storage::disk('public')->assertMissing('uploads/images/test.jpg');
    }

    /** @test */
    public function it_can_delete_image_with_empty_path_extracting_from_image_name()
    {
        Storage::disk('public')->put('uploads/images/test.jpg', 'fake content');
        
        $result = $this->service->deleteImage('uploads/images/test.jpg', '');
        
        $this->assertTrue($result);
        Storage::disk('public')->assertMissing('uploads/images/test.jpg');
    }

    /** @test */
    public function it_can_delete_resized_versions_when_path_extracted_from_image_name()
    {
        Storage::disk('public')->put('uploads/images/test.jpg', 'fake content');
        Storage::disk('public')->put('uploads/images/small_test.jpg', 'fake content');
        Storage::disk('public')->put('uploads/images/medium_test.jpg', 'fake content');
        
        $result = $this->service->deleteImage('uploads/images/test.jpg', null, ['small', 'medium']);
        
        $this->assertTrue($result);
        Storage::disk('public')->assertMissing('uploads/images/test.jpg');
        Storage::disk('public')->assertMissing('uploads/images/small_test.jpg');
        Storage::disk('public')->assertMissing('uploads/images/medium_test.jpg');
    }

    /** @test */
    public function it_throws_exception_when_image_is_in_root_directory()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Image name is in root directory: test.jpg');
        
        $this->service->deleteImage('test.jpg', null);
    }

    /** @test */
    public function it_throws_exception_when_image_is_in_root_directory_with_empty_path()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Image name is in root directory: test.jpg');
        
        $this->service->deleteImage('test.jpg', '');
    }

    /** @test */
    public function it_can_get_url_for_image()
    {
        Storage::disk('public')->put('uploads/images/test.jpg', 'fake content');
        
        $url = $this->service->url('uploads/images/test.jpg');
        
        $this->assertIsString($url);
    }

    /** @test */
    public function it_can_get_path_for_image()
    {
        $path = $this->service->path('uploads/images/test.jpg');
        
        $this->assertIsString($path);
    }

    /** @test */
    public function it_can_check_if_file_exists()
    {
        Storage::disk('public')->put('uploads/images/test.jpg', 'fake content');
        
        $this->assertTrue($this->service->exists('uploads/images/test.jpg'));
        $this->assertFalse($this->service->exists('uploads/images/nonexistent.jpg'));
    }

    /** @test */
    public function it_saves_to_different_disk()
    {
        $this->service->setDisk('s3');
        
        $image = UploadedFile::fake()->image('test.jpg');
        $result = $this->service->saveOriginal($image, 'uploads/images', null, null);

        Storage::disk('s3')->assertExists($result['fullPath']);
        Storage::disk('public')->assertMissing($result['fullPath']);
    }

    /** @test */
    public function it_can_save_watermark_image()
    {
        $watermark = UploadedFile::fake()->image('watermark.png');
        
        $path = $this->service->saveWatermark($watermark);

        $this->assertIsString($path);
        $this->assertStringContainsString('watermark', $path);
        Storage::disk('public')->assertExists($path);
    }

    /** @test */
    public function it_can_save_watermark_with_custom_name()
    {
        $watermark = UploadedFile::fake()->image('watermark.png');
        
        $path = $this->service->saveWatermark($watermark, 'my-watermark');

        $this->assertStringContainsString('my-watermark', $path);
        Storage::disk('public')->assertExists($path);
    }
}
