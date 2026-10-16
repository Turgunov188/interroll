<?php

namespace App\Services\Leads;

use App\Actions\Leads\CreateLead;
use App\Models\Lead;
use Illuminate\Contracts\Filesystem\Factory as Storage;

/**
 * Files a customer attached to a lead, ready for the admin panel: name, size, whether it
 * can be previewed, and a short-lived signed link (the files live on the private disk).
 */
final class LeadAttachments
{
    public const LINK_MINUTES = 30;

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'heic'];

    public function __construct(private readonly Storage $storage) {}

    /** @return list<array{name: string, extension: string, size: string, isImage: bool, url: string|null}> */
    public function for(Lead $lead): array
    {
        $disk = $this->storage->disk(CreateLead::DISK);

        return collect($lead->attachments ?? [])
            ->filter(fn ($path): bool => is_string($path) && $path !== '')
            ->map(function (string $path) use ($disk): array {
                $exists = $disk->exists($path);
                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

                return [
                    'name' => basename($path),
                    'extension' => strtoupper($extension ?: 'file'),
                    'size' => $exists ? $this->size($disk->size($path)) : 'файл не найден',
                    // HEIC does not render in most browsers, so it is offered as a download.
                    'isImage' => $exists && in_array($extension, self::IMAGE_EXTENSIONS, true) && $extension !== 'heic',
                    'url' => $exists ? $disk->temporaryUrl($path, now()->addMinutes(self::LINK_MINUTES)) : null,
                ];
            })
            ->values()
            ->all();
    }

    private function size(int $bytes): string
    {
        return $bytes >= 1048576 ? round($bytes / 1048576, 1).' MB' : max(1, (int) round($bytes / 1024)).' KB';
    }
}
