<?php

namespace DevMahmoudMustafa\ImageKit\Tests\Feature;

use DevMahmoudMustafa\ImageKit\Events\ImageDeleted;
use DevMahmoudMustafa\ImageKit\Events\ImageSaved;
use DevMahmoudMustafa\ImageKit\Events\ImageSaving;
use DevMahmoudMustafa\ImageKit\Facades\ImageKit;
use DevMahmoudMustafa\ImageKit\Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

class EventsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Event::fake();
    }

    /** @test */
    public function it_fires_image_saving_event_before_saving()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        
        ImageKit::image($image)->save();

        Event::assertDispatched(ImageSaving::class, function ($event) {
            return $event->image !== null 
                && $event->path === 'uploads/images'
                && is_array($event->options);
        });
    }

    /** @test */
    public function it_fires_image_saved_event_after_saving()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        
        $imageName = ImageKit::image($image)->save();

        Event::assertDispatched(ImageSaved::class, function ($event) use ($imageName) {
            return $event->imageName === $imageName
                && $event->path === 'uploads/images'
                && !empty($event->fullPath);
        });
    }

    /** @test */
    public function it_fires_image_deleted_event_when_deleting()
    {
        Storage::disk('public')->put('uploads/images/test.jpg', 'fake content');
        
        ImageKit::deleteImage('test.jpg', 'uploads/images');

        Event::assertDispatched(ImageDeleted::class, function ($event) {
            return $event->imageName === 'test.jpg'
                && $event->path === 'uploads/images'
                && $event->success === true;
        });
    }

    /** @test */
    public function it_fires_image_deleted_event_for_each_image_in_gallery()
    {
        Storage::disk('public')->put('uploads/images/img1.jpg', 'fake content');
        Storage::disk('public')->put('uploads/images/img2.jpg', 'fake content');
        
        ImageKit::deleteGallery(['img1.jpg', 'img2.jpg'], 'uploads/images');

        Event::assertDispatched(ImageDeleted::class, 2);
    }

    /** @test */
    public function image_saved_event_contains_correct_options()
    {
        $image = UploadedFile::fake()->image('test.jpg');
        $watermark = UploadedFile::fake()->image('watermark.png');
        
        // Save actual image content instead of 'fake content'
        Storage::disk('public')->put('watermark.png', file_get_contents($watermark->getRealPath()));

        ImageKit::image($image)
            ->resize(800, 600)
            ->compress(85)
            ->watermark('watermark.png', 'bottom-right', 50)
            ->save();

        Event::assertDispatched(ImageSaving::class, function ($event) {
            $options = $event->options;
            return isset($options['dimensions'])
                && isset($options['watermark'])
                && isset($options['compress'])
                && $options['compress'] === true;
        });
    }
}

