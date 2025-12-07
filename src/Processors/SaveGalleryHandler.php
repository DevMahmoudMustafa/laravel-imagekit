<?php

namespace DevMahmoudMustafa\ImageKit\Processors;

use DevMahmoudMustafa\ImageKit\Contracts\SaveGalleryHandlerInterface;

class SaveGalleryHandler extends ImageHandler implements SaveGalleryHandlerInterface
{

    /**
     * Save multiple images as a gallery.
     * 
     * @param string|null $imageColumnName
     * @param string|null $fkColumnName
     * @param int|null $fkId
     * @param string|array|null $altText Can be a single string for all images, or array of strings for each image
     * @return array
     */
    public function saveGallery(
        ?string $imageColumnName = null,
        ?string $fkColumnName = null,
        ?int $fkId = null,
        string|array|null $altText = null
    ): array {
        if (empty($this->images)) {
            throw new \InvalidArgumentException('No images provided. Use setImages() first.');
        }

        $data = [];
        $altTexts = is_array($altText) ? $altText : null;
        
        try {
            foreach ($this->images as $index => $image) {
                $this->image = $image;
                $this->imageName = null; // Reset name for each image

                $imageName = $this->processingImage();

                if ($imageColumnName) {
                    $row = [
                        $imageColumnName => $imageName
                    ];

                    if ($fkColumnName && $fkId) {
                        $row[$fkColumnName] = $fkId;
                    }

                    // Support both single altText and array of altTexts
                    $currentAltText = $altTexts ? ($altTexts[$index] ?? null) : $altText;
                    if ($currentAltText) {
                        $row['alt'] = $currentAltText;
                    }

                    $data[] = $row;
                } else {
                    $data[] = $imageName;
                }
            }
        } finally {
            // Reset state after processing
            $this->resetGalleryState();
        }

        return $data;
    }

    /**
     * Reset state after processing gallery.
     *
     * @return void
     */
    protected function resetGalleryState(): void
    {
        $this->images = [];
        $this->image = null;
        $this->imageName = null;
        $this->extension = null;
    }
}
