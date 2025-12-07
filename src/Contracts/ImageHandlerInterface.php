<?php

namespace DevMahmoudMustafa\ImageKit\Contracts;

interface ImageHandlerInterface
{
    public function setImageName(string $name): self;
    public function setImagePath(string $path): self;
    public function setExtension(string $extension): self;
    public function compressImage(bool $compress = true, ?int $compressionRatio = null): self;
    public function setDimensions(?int $width = null, ?int $height = null): self;
    public function setWatermark(?string $imagePath = null, ?string $position = null, ?int $opacity = null, ?array $offset = null, ?int $x = null, ?int $y = null): self;
    public function setMultiSizeOptions(?array $options = null): self;
    public function setDisk(string $disk): self;
    public function reset(): self;
}
