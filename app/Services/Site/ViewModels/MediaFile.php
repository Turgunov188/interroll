<?php

namespace App\Services\Site\ViewModels;

/** A product file shown on its page: photo, drawing, 3D model or document. */
final readonly class MediaFile
{
    public function __construct(
        public string $url,
        public string $title,
        public bool $isImage = true,
        public string $meta = '',
    ) {}
}
