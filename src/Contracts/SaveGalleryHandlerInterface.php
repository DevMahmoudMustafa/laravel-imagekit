<?php

namespace DevMahmoudMustafa\ImageKit\Contracts;


interface SaveGalleryHandlerInterface extends ImageHandlerInterface
{
    public function saveGallery(
        ?string $imageColumnName = null,
        ?string $fkColumnName = null,
        ?int $fkId = null,
        string|array|null $altText = null
    ): array;
}
