<?php

namespace DevMahmoudMustafa\ImageKit\Processors;

use DevMahmoudMustafa\ImageKit\Contracts\SaveSingleImageHandlerInterface;

class SaveSingleImageHandler extends ImageHandler implements SaveSingleImageHandlerInterface
{


    /**
     * Save the image after applying all modifications.
     * Automatically resets state after saving to prevent state pollution.
     *
     * @return string|array
     */
    public function saveImage(): string|array
    {
        try {
            return $this->processingImage();
        } finally {
            // Reset state after processing to avoid state pollution
            $this->resetSingleImageState();
        }
    }

    /**
     * Reset state after processing single image.
     *
     * @return void
     */
    protected function resetSingleImageState(): void
    {
        $this->image = null;
        $this->imageName = null;
        $this->extension = null;
    }
}
