<?php

namespace DevMahmoudMustafa\ImageKit\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImageSaved
{
    use Dispatchable, SerializesModels;

    public string $imageName;
    public string $path;
    public string $fullPath;

    /**
     * Create a new event instance.
     *
     * @param string $imageName
     * @param string $path
     * @param string $fullPath
     */
    public function __construct(string $imageName, string $path, string $fullPath)
    {
        $this->imageName = $imageName;
        $this->path = $path;
        $this->fullPath = $fullPath;
    }
}

