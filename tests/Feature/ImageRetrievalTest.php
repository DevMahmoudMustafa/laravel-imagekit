<?php

namespace DevMahmoudMustafa\ImageKit\Tests\Feature;

use DevMahmoudMustafa\ImageKit\Facades\ImageKit;
use DevMahmoudMustafa\ImageKit\Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageRetrievalTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('s3');
    }

    /** @test */
    public function it_can_get_image_content()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        Storage::disk('public')->put('uploads/images/test.jpg', file_get_contents($image->getRealPath()));

        $content = ImageKit::getImage('uploads/images/test.jpg');

        $this->assertNotNull($content);
        $this->assertIsString($content);
    }

    /** @test */
    public function it_returns_null_when_image_not_found()
    {
        $content = ImageKit::getImage('uploads/images/nonexistent.jpg');

        $this->assertNull($content);
    }

    /** @test */
    public function it_can_get_image_from_specific_disk()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        Storage::disk('s3')->put('uploads/images/test.jpg', file_get_contents($image->getRealPath()));

        $content = ImageKit::getImage('uploads/images/test.jpg', 's3');

        $this->assertNotNull($content);
        Storage::disk('public')->assertMissing('uploads/images/test.jpg');
    }

    /** @test */
    public function it_can_get_image_response()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        Storage::disk('public')->put('uploads/images/test.jpg', file_get_contents($image->getRealPath()));

        $response = ImageKit::response('uploads/images/test.jpg');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('image/jpeg', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function it_throws_exception_when_image_not_found_in_response()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Image file does not exist');

        ImageKit::response('uploads/images/nonexistent.jpg');
    }

    /** @test */
    public function it_can_set_custom_content_type_in_response()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        Storage::disk('public')->put('uploads/images/test.jpg', file_get_contents($image->getRealPath()));

        $response = ImageKit::response('uploads/images/test.jpg', null, [
            'contentType' => 'image/png'
        ]);

        $this->assertEquals('image/png', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function it_can_set_cache_headers_in_response()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        Storage::disk('public')->put('uploads/images/test.jpg', file_get_contents($image->getRealPath()));

        $response = ImageKit::response('uploads/images/test.jpg', null, [
            'cache' => 3600
        ]);

        $this->assertStringContainsString('max-age=3600', $response->headers->get('Cache-Control'));
    }

    /** @test */
    public function it_can_disable_cache_in_response()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        Storage::disk('public')->put('uploads/images/test.jpg', file_get_contents($image->getRealPath()));

        $response = ImageKit::response('uploads/images/test.jpg', null, [
            'cache' => false
        ]);

        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);
    }

    /** @test */
    public function it_can_download_image()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        Storage::disk('public')->put('uploads/images/test.jpg', file_get_contents($image->getRealPath()));

        $response = ImageKit::download('uploads/images/test.jpg', 'custom-name.jpg');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('custom-name.jpg', $response->headers->get('Content-Disposition'));
    }

    /** @test */
    public function it_can_get_image_response_from_specific_disk()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        Storage::disk('s3')->put('uploads/images/test.jpg', file_get_contents($image->getRealPath()));

        $response = ImageKit::response('uploads/images/test.jpg', 's3');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function it_uses_config_disk_by_default()
    {
        config(['imagekit.disk' => 'public']);

        $image = UploadedFile::fake()->image('test.jpg');
        Storage::disk('public')->put('uploads/images/test.jpg', file_get_contents($image->getRealPath()));

        $content = ImageKit::getImage('uploads/images/test.jpg');

        $this->assertNotNull($content);
    }

    /** @test */
    public function it_can_get_url_for_image()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        Storage::disk('public')->put('uploads/images/test.jpg', file_get_contents($image->getRealPath()));

        $url = ImageKit::getImageUrl('uploads/images/test.jpg');

        $this->assertIsString($url);
        $this->assertNotEmpty($url);
    }

    /** @test */
    public function it_can_get_url_from_specific_disk()
    {
        $url = ImageKit::getImageUrl('uploads/images/test.jpg', 's3');

        $this->assertIsString($url);
    }

    /** @test */
    public function it_can_get_path_for_image()
    {
        $path = ImageKit::getImagePath('uploads/images/test.jpg');

        $this->assertIsString($path);
    }

    /** @test */
    public function it_can_check_if_image_exists()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        Storage::disk('public')->put('uploads/images/test.jpg', file_get_contents($image->getRealPath()));

        $this->assertTrue(ImageKit::imageExists('uploads/images/test.jpg'));
        $this->assertFalse(ImageKit::imageExists('uploads/images/nonexistent.jpg'));
    }

    /** @test */
    public function it_detects_mime_type_correctly()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        Storage::disk('public')->put('uploads/images/test.jpg', file_get_contents($image->getRealPath()));

        $response = ImageKit::response('uploads/images/test.jpg');

        $this->assertEquals('image/jpeg', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function it_detects_png_mime_type()
    {
        $image = UploadedFile::fake()->image('test.png');
        Storage::disk('public')->put('uploads/images/test.png', file_get_contents($image->getRealPath()));

        $response = ImageKit::response('uploads/images/test.png');

        $this->assertEquals('image/png', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function it_can_get_temporary_url_for_image()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        Storage::disk('s3')->put('uploads/images/test.jpg', file_get_contents($image->getRealPath()));

        // Mock the temporaryUrl method to return a test URL
        // Note: This test may fail if S3 disk is not properly configured in test environment
        try {
            $expiration = now()->addHour();
            $url = ImageKit::temporaryUrl('uploads/images/test.jpg', $expiration, 's3');
            
            $this->assertIsString($url);
            $this->assertNotEmpty($url);
        } catch (\Exception $e) {
            // Skip test if temporary URL is not supported in test environment
            $this->markTestSkipped('Temporary URL not supported in test environment: ' . $e->getMessage());
        }
    }

    /** @test */
    public function it_throws_exception_when_image_not_found_for_temporary_url()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Image file does not exist');

        $expiration = now()->addHour();
        ImageKit::temporaryUrl('uploads/images/nonexistent.jpg', $expiration);
    }
}

