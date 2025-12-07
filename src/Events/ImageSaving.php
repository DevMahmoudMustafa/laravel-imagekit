<?php

namespace DevMahmoudMustafa\ImageKit\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImageSaving
{
    use Dispatchable, SerializesModels;

    public mixed $image;
    public string $path;
    public array $options;

    /**
     * Create a new event instance.
     *
     * @param mixed $image
     * @param string $path
     * @param array $options
     */
    public function __construct($image, string $path, array $options = [])
    {
        $this->image = $image;
        $this->path = $path;
        $this->options = $options;
    }
}

