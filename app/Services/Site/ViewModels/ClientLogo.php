<?php

namespace App\Services\Site\ViewModels;

final readonly class ClientLogo
{
    public function __construct(
        public string $name,
        public ?string $logo,
    ) {}
}
