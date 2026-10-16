<?php

namespace App\Data;

use App\Enums\LeadType;
use App\Enums\Locale;
use Illuminate\Http\UploadedFile;

/** A validated lead coming from the public site, independent of HTTP. */
final readonly class LeadData
{
    /**
     * @param  array<string, mixed>|null  $payload  component state: cart, configurator, wizard answers
     * @param  list<UploadedFile>  $attachments
     * @param  list<LeadItemData>  $items  parts from the product page or the catalog cart
     */
    public function __construct(
        public LeadType $type,
        public string $phone,
        public Locale $locale,
        public ?string $name = null,
        public ?string $company = null,
        public ?string $email = null,
        public ?string $message = null,
        public ?string $source = null,
        public ?array $payload = null,
        public array $attachments = [],
        public array $items = [],
    ) {}
}
