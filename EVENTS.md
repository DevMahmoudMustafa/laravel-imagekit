# Events Documentation

The ImageKit package fires events at key points during image processing. You can listen to these events to perform additional actions such as logging, database updates, notifications, or any custom logic.

## Namespace

All events are located in the `DevMahmoudMustafa\ImageKit\Events` namespace:

```php
use DevMahmoudMustafa\ImageKit\Events\ImageSaving;
use DevMahmoudMustafa\ImageKit\Events\ImageSaved;
use DevMahmoudMustafa\ImageKit\Events\ImageDeleted;
```

## Available Events

### ImageSaving

**Fired:** Before an image is saved (before any processing like resizing, watermarking, etc.)

**Location:** Fired in `ImageHandler::processingImage()` method before saving the image.

**When:** This event is fired every time `save()` or `saveImage()` is called, including when processing gallery images (once per image).

**Event Properties:**

| Property | Type | Description |
|----------|------|-------------|
| `$image` | `mixed` | The uploaded file (`Illuminate\Http\UploadedFile` instance) |
| `$path` | `string` | The destination path where the image will be saved (relative to disk root) |
| `$options` | `array` | Array containing all processing options that will be applied |

**Options Array Structure:**

The `$options` array contains the following keys:

- `dimensions` - Array with `width` and `height` keys (or null if no resize is configured)
- `watermark` - Array with watermark settings (image path, position, opacity, x, y, width, height) or null
- `compress` - Boolean indicating if compression is enabled
- `resize` - Array of size names for multi-size resizing (or null)

**Example:**
```php
use DevMahmoudMustafa\ImageKit\Events\ImageSaving;
use Illuminate\Support\Facades\Event;

Event::listen(ImageSaving::class, function (ImageSaving $event) {
    \Log::info('Saving image', [
        'path' => $event->path,
        'has_resize' => !empty($event->options['dimensions']),
        'has_watermark' => !empty($event->options['watermark']),
        'will_compress' => $event->options['compress'] ?? false,
        'multi_sizes' => $event->options['resize'] ?? null,
    ]);
    
    // You can access the uploaded file if needed
    $fileSize = $event->image->getSize();
    $fileName = $event->image->getClientOriginalName();
});
```

### ImageSaved

**Fired:** After an image is successfully saved and all processing (resize, watermark, compress) is complete.

**Location:** Fired in `ImageHandler::processingImage()` method after the image has been saved and processed.

**When:** This event is fired every time an image is successfully saved, including when processing gallery images (once per image).

**Event Properties:**

| Property | Type | Description |
|----------|------|-------------|
| `$imageName` | `string` | The saved image filename (e.g., `"image_1234567890_abc123.jpg"`) |
| `$path` | `string` | The path where the image was saved (relative to disk root, e.g., `"uploads/images"`) |
| `$fullPath` | `string` | The full path including filename (e.g., `"uploads/images/image_1234567890_abc123.jpg"`) |

**Example:**
```php
use DevMahmoudMustafa\ImageKit\Events\ImageSaved;
use Illuminate\Support\Facades\Event;

Event::listen(ImageSaved::class, function (ImageSaved $event) {
    \Log::info('Image saved successfully', [
        'name' => $event->imageName,
        'path' => $event->path,
        'fullPath' => $event->fullPath,
    ]);
    
    // Example: Update database
    DB::table('images')->insert([
        'filename' => $event->imageName,
        'path' => $event->path,
        'full_path' => $event->fullPath,
        'created_at' => now(),
    ]);
    
    // Example: Send notification
    Notification::send($user, new ImageUploadedNotification($event->imageName));
    
    // Example: Create additional thumbnails
    // ... your custom logic
});
```

### ImageDeleted

**Fired:** After an image deletion attempt (whether successful or not).

**Location:** 
- Fired in `ImageKitService::deleteImage()` after deletion attempt
- Fired in `ImageKitService::deleteGallery()` after each image deletion attempt (once per image)

**When:** This event is fired every time `deleteImage()` or `deleteGallery()` is called.

**Event Properties:**

| Property | Type | Description |
|----------|------|-------------|
| `$imageName` | `string` | The deleted image filename |
| `$path` | `string` | The path where the image was located (relative to disk root) |
| `$success` | `bool` | Whether the deletion was successful (`true` if deleted, `false` if not found or error occurred) |

**Important Notes:**

- For `deleteGallery()`, this event is fired **once for each image** in the gallery.
- The `$success` property indicates if the specific image was successfully deleted.
- If multi-size images exist (small, medium, large versions), all sizes are deleted, and the event is fired once with the base image name.

**Example:**
```php
use DevMahmoudMustafa\ImageKit\Events\ImageDeleted;
use Illuminate\Support\Facades\Event;

Event::listen(ImageDeleted::class, function (ImageDeleted $event) {
    if ($event->success) {
        \Log::info('Image deleted successfully', [
            'name' => $event->imageName,
            'path' => $event->path,
        ]);
        
        // Example: Update database
        DB::table('images')
            ->where('filename', $event->imageName)
            ->where('path', $event->path)
            ->delete();
    } else {
        \Log::warning('Image deletion failed', [
            'name' => $event->imageName,
            'path' => $event->path,
        ]);
    }
});
```

## Events and Gallery Operations

When using `saveGallery()`, the events are fired **once for each image** in the gallery:

- `ImageSaving` is fired before each image is saved
- `ImageSaved` is fired after each image is successfully saved

When using `deleteGallery()`, the `ImageDeleted` event is fired **once for each image** in the deletion array.

**Example:**
```php
use DevMahmoudMustafa\ImageKit\Facades\ImageKit;
use DevMahmoudMustafa\ImageKit\Events\ImageSaved;
use Illuminate\Support\Facades\Event;

// This will fire ImageSaving and ImageSaved events 3 times (once per image)
$images = [$image1, $image2, $image3];
ImageKit::images($images)
    ->resize(800, 600)
    ->saveGallery();

// Listen to all ImageSaved events (will be called 3 times)
Event::listen(ImageSaved::class, function (ImageSaved $event) {
    \Log::info('Gallery image saved', [
        'name' => $event->imageName,
        'path' => $event->path,
    ]);
});
```

## Usage in Listeners

### Method 1: Closures (Simple Use Cases)

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

### Method 2: Listener Classes (Recommended for Complex Logic)

Create a listener class:

```php
namespace App\Listeners;

use DevMahmoudMustafa\ImageKit\Events\ImageSaved;

class ImageSavedListener
{
    /**
     * Handle the event.
     *
     * @param ImageSaved $event
     * @return void
     */
    public function handle(ImageSaved $event)
    {
        // Update database
        DB::table('images')->insert([
            'filename' => $event->imageName,
            'path' => $event->path,
            'full_path' => $event->fullPath,
            'created_at' => now(),
        ]);
        
        // Send notification
        // Notification::send($user, new ImageUploadedNotification($event->imageName));
        
        // Create additional processing
        // $this->createThumbnail($event->fullPath);
    }
}
```

Register in `EventServiceProvider` (`app/Providers/EventServiceProvider.php`):

```php
use App\Listeners\ImageSavedListener;
use DevMahmoudMustafa\ImageKit\Events\ImageSaved;

protected $listen = [
    ImageSaved::class => [
        ImageSavedListener::class,
    ],
];
```

### Method 3: Multiple Listeners

You can register multiple listeners for the same event:

```php
use App\Listeners\ImageSavedListener;
use App\Listeners\ImageSavedNotificationListener;
use App\Listeners\ImageSavedCacheListener;
use DevMahmoudMustafa\ImageKit\Events\ImageSaved;

protected $listen = [
    ImageSaved::class => [
        ImageSavedListener::class,
        ImageSavedNotificationListener::class,
        ImageSavedCacheListener::class,
    ],
];
```

## Complete Example: Real-World Usage

Here's a complete example showing how to use events in a real-world scenario:

```php
namespace App\Listeners;

use DevMahmoudMustafa\ImageKit\Events\ImageSaved;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HandleImageSaved
{
    /**
     * Handle the ImageSaved event.
     *
     * @param ImageSaved $event
     * @return void
     */
    public function handle(ImageSaved $event)
    {
        // 1. Save to database
        DB::table('media_files')->insert([
            'filename' => $event->imageName,
            'path' => $event->path,
            'full_path' => $event->fullPath,
            'disk' => 'public',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // 2. Create additional thumbnail
        $this->createThumbnail($event->fullPath);
        
        // 3. Send notification to admin
        // Notification::send(Admin::first(), new NewImageUploaded($event));
        
        // 4. Update cache
        Cache::forget('images_count');
    }
    
    /**
     * Create a small thumbnail for the image.
     *
     * @param string $fullPath
     * @return void
     */
    protected function createThumbnail(string $fullPath)
    {
        // Your thumbnail creation logic here
        // ...
    }
}
```

## Event Priority and Order

Events are fired in the following order:

1. **ImageSaving** - Before any processing
2. Image processing (resize, watermark, compress)
3. **ImageSaved** - After successful save

If an error occurs during processing, `ImageSaved` will **not** be fired.

## Best Practices

1. **Use Listener Classes** for complex logic instead of closures
2. **Handle Errors** - Events can fail, so wrap listener logic in try-catch blocks
3. **Don't Block** - Keep event listeners fast, or use queues for heavy operations
4. **Log Everything** - Always log important operations in event listeners
5. **Test Events** - Use `Event::fake()` in tests to verify events are fired correctly

## Testing Events

You can test that events are fired correctly:

```php
use DevMahmoudMustafa\ImageKit\Events\ImageSaved;
use DevMahmoudMustafa\ImageKit\Events\ImageSaving;
use DevMahmoudMustafa\ImageKit\Events\ImageDeleted;
use DevMahmoudMustafa\ImageKit\Facades\ImageKit;
use Illuminate\Support\Facades\Event;

public function test_image_saved_event_is_fired()
{
    Event::fake();
    
    $image = UploadedFile::fake()->image('test.jpg');
    ImageKit::image($image)->save();
    
    Event::assertDispatched(ImageSaved::class, function ($event) {
        return !empty($event->imageName) && !empty($event->path);
    });
}

public function test_image_saving_event_contains_options()
{
    Event::fake();
    
    $image = UploadedFile::fake()->image('test.jpg');
    ImageKit::image($image)
        ->resize(800, 600)
        ->compress(85)
        ->save();
    
    Event::assertDispatched(ImageSaving::class, function ($event) {
        return isset($event->options['dimensions'])
            && isset($event->options['compress'])
            && $event->options['compress'] === true;
    });
}

public function test_image_deleted_event_is_fired()
{
    Event::fake();
    
    Storage::disk('public')->put('uploads/images/test.jpg', 'fake content');
    ImageKit::deleteImage('test.jpg', 'uploads/images');
    
    Event::assertDispatched(ImageDeleted::class, function ($event) {
        return $event->imageName === 'test.jpg' && $event->success === true;
    });
}
```

## Event Files Location

All event classes are located in:
```
packages/DevMahmoudMustafa/ImageKit/src/Events/
├── ImageSaving.php
├── ImageSaved.php
└── ImageDeleted.php
```

## Summary

The ImageKit package provides three events for monitoring image operations:

1. **ImageSaving** - Fired before saving (with all processing options)
2. **ImageSaved** - Fired after successful save (with image details)
3. **ImageDeleted** - Fired after deletion attempt (with success status)

All events are compatible with Laravel's event system and can be:
- Listened to using closures or listener classes
- Queued for async processing
- Used in tests with `Event::fake()`
- Subscribed to in `EventServiceProvider`

For more information about Laravel events, see the [Laravel Events Documentation](https://laravel.com/docs/events).

