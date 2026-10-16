<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * What a product file is. Each type feeds one place on the product page:
 * Photo → «Фото» view and catalog card, Drawing → «Чертёж» view,
 * Model3d → «3D» view, Document → «Документы» tab (passport, certificate).
 */
enum MediaType: string implements HasColor, HasIcon, HasLabel
{
    case Photo = 'photo';
    case Drawing = 'drawing';
    case Model3d = 'model3d';
    case Document = 'document';

    public const DISK = 'public';

    public function directory(): string
    {
        return match ($this) {
            self::Photo => 'products/photos',
            self::Drawing => 'products/drawings',
            self::Model3d => 'products/models',
            self::Document => 'products/documents',
        };
    }

    /** @return list<string> MIME types allowed in the admin upload */
    public function acceptedMimeTypes(): array
    {
        return match ($this) {
            self::Photo => ['image/jpeg', 'image/png', 'image/webp'],
            self::Drawing => ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml', 'application/pdf'],
            // Browsers report .glb inconsistently, so octet-stream is allowed too (admin-only upload).
            self::Model3d => ['model/gltf-binary', 'application/octet-stream'],
            self::Document => ['application/pdf'],
        };
    }

    public function maxKilobytes(): int
    {
        return match ($this) {
            self::Photo => 4096,
            self::Drawing, self::Document => 10240,
            self::Model3d => 20480,
        };
    }

    public function hint(): string
    {
        return match ($this) {
            self::Photo => 'JPG, PNG или WebP, до 4 МБ. Первое фото — главное (карточка в каталоге).',
            self::Drawing => 'Изображение или PDF, до 10 МБ. DWG сначала сохраните в PDF.',
            self::Model3d => '3D-модель в формате .glb, до 20 МБ (экспорт из SolidWorks / Fusion / Blender).',
            self::Document => 'PDF, до 10 МБ: технический паспорт, сертификат, инструкция.',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Photo => 'Фото',
            self::Drawing => 'Чертёж',
            self::Model3d => '3D-модель',
            self::Document => 'Документ',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Photo => 'success',
            self::Drawing => 'info',
            self::Model3d => 'warning',
            self::Document => 'gray',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Photo => 'heroicon-o-photo',
            self::Drawing => 'heroicon-o-pencil-square',
            self::Model3d => 'heroicon-o-cube-transparent',
            self::Document => 'heroicon-o-document-text',
        };
    }
}
