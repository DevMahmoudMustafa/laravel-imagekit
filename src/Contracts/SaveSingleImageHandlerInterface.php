<?php

namespace DevMahmoudMustafa\ImageKit\Contracts;

interface SaveSingleImageHandlerInterface extends ImageHandlerInterface
{
    public function saveImage(): string|array;
}
