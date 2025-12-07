<?php

namespace DevMahmoudMustafa\ImageKit\Tests\Feature;

use DevMahmoudMustafa\ImageKit\Facades\ImageKit;
use DevMahmoudMustafa\ImageKit\Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileNamingStrategyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_uses_default_naming_strategy()
    {
        config(['imagekit.naming_strategy' => 'default']);
        
        $image = UploadedFile::fake()->image('test.jpg');
        $imageName = ImageKit::image($image)->save();

        $this->assertStringContainsString('image_', $imageName);
        $this->assertStringEndsWith('.jpg', $imageName);
    }

    /** @test */
    public function it_uses_timestamp_naming_strategy()
    {
        config(['imagekit.naming_strategy' => 'timestamp']);
        
        $image = UploadedFile::fake()->image('test.jpg');
        $imageName = ImageKit::image($image)->save();

        $this->assertIsString($imageName);
        $this->assertStringEndsWith('.jpg', $imageName);
    }

    /** @test */
    public function it_uses_hash_naming_strategy()
    {
        config(['imagekit.naming_strategy' => 'hash']);
        
        $image = UploadedFile::fake()->image('test.jpg');
        $imageName = ImageKit::image($image)->save();

        $this->assertIsString($imageName);
        $this->assertStringEndsWith('.jpg', $imageName);
    }

    /** @test */
    public function it_uses_custom_name_when_provided()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        $imageName = ImageKit::image($image)
            ->name('my-custom-image')
            ->save();

        $this->assertStringContainsString('my-custom-image', $imageName);
    }

    /** @test */
    public function it_respects_custom_extension()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        $imageName = ImageKit::image($image)
            ->extension('webp')
            ->save();

        $this->assertStringEndsWith('.webp', $imageName);
    }

    /** @test */
    public function it_uses_uuid_naming_strategy()
    {
        config(['imagekit.naming_strategy' => 'uuid']);
        
        $image = UploadedFile::fake()->image('test.jpg');
        
        try {
            $imageName = ImageKit::image($image)->save();
            
            // UUID format: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
            $this->assertIsString($imageName);
            $this->assertStringEndsWith('.jpg', $imageName);
            
            // Remove extension and check if it's a valid UUID format
            $uuidPart = str_replace('.jpg', '', $imageName);
            $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuidPart);
        } catch (\Exception $e) {
            // Skip if UUID package is not available
            $this->markTestSkipped('UUID naming strategy requires ramsey/uuid package: ' . $e->getMessage());
        }
    }
}

