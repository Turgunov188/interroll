<?php

namespace App\Http\Requests;

use App\Data\LeadData;
use App\Data\LeadItemData;
use App\Enums\LeadType;
use App\Enums\Locale;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Every form on the public site posts here (resources/views/components/site/lead-form.blade.php). */
class StoreLeadRequest extends FormRequest
{
    public const MAX_FILES = 5;

    public const MAX_FILE_KB = 20480;

    public const MAX_ITEMS = 50;

    public const MAX_QUANTITY = 100000;

    public const MAX_DETAILS = 10;

    public const FILE_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'heic', 'dwg', 'dxf', 'step', 'stp', 'zip', 'rar'];

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'min:7', 'max:32'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
            'type' => ['nullable', Rule::enum(LeadType::class)],
            'locale' => ['nullable', Rule::enum(Locale::class)],
            'source' => ['nullable', 'string', 'max:255'],
            'payload' => ['nullable', 'json', 'max:20000'],
            'items' => ['nullable', 'json', 'max:20000'],
            'details' => ['nullable', 'array', 'max:'.self::MAX_DETAILS],
            'details.*' => ['nullable', 'string', 'max:300'],
            'attachments' => ['nullable', 'array', 'max:'.self::MAX_FILES],
            'attachments.*' => ['file', 'max:'.self::MAX_FILE_KB, 'extensions:'.implode(',', self::FILE_EXTENSIONS)],
        ];
    }

    /**
     * The page sends [{code, qty, options}]. Anything malformed is skipped rather than
     * rejecting the whole request: the contact details matter more than one broken item.
     *
     * @return list<LeadItemData>
     */
    private function items(): array
    {
        $raw = json_decode((string) $this->validated('items'), true);

        if (! is_array($raw)) {
            return [];
        }

        $items = [];

        foreach (array_slice(array_values($raw), 0, self::MAX_ITEMS) as $item) {
            $code = is_array($item) && is_string($item['code'] ?? null) ? trim($item['code']) : '';

            if ($code === '' || mb_strlen($code) > 64) {
                continue;
            }

            $options = array_filter(
                is_array($item['options'] ?? null) ? $item['options'] : [],
                fn ($value, $key): bool => is_string($key) && strlen($key) <= 20 && is_scalar($value) && mb_strlen((string) $value) <= 50,
                ARRAY_FILTER_USE_BOTH,
            );

            $items[] = new LeadItemData(
                code: $code,
                quantity: max(1, min(self::MAX_QUANTITY, (int) ($item['qty'] ?? 1))),
                options: array_slice($options, 0, 20, true),
            );
        }

        return $items;
    }

    /**
     * Answers of the form beyond the contact fields (contact topic, part finder answers) as
     * labelled lines for the manager, followed by what the customer wrote. Only keys listed in
     * lang/ru/leads.php are kept; known values get their Russian name.
     */
    private function message(): ?string
    {
        $lines = [];

        foreach ((array) $this->validated('details', []) as $key => $value) {
            $label = trans("leads.details.{$key}.label", [], 'ru');

            if (! is_string($key) || $label === "leads.details.{$key}.label" || blank($value)) {
                continue;
            }

            $name = trans("leads.details.{$key}.values.{$value}", [], 'ru');
            $lines[] = $label.': '.(str_starts_with($name, 'leads.details.') ? trim($value) : $name);
        }

        $text = trim((string) $this->validated('message'));

        return implode("\n", array_filter([implode("\n", $lines), $text])) ?: null;
    }

    public function toData(): LeadData
    {
        return new LeadData(
            type: $this->enum('type', LeadType::class) ?? LeadType::Quote,
            phone: $this->string('phone')->trim()->value(),
            locale: $this->enum('locale', Locale::class) ?? Locale::main(),
            name: $this->filled('name') ? $this->string('name')->trim()->value() : null,
            company: $this->validated('company'),
            email: $this->validated('email'),
            message: $this->message(),
            source: $this->validated('source'),
            payload: $this->filled('payload') ? json_decode($this->validated('payload'), true) : null,
            attachments: array_values($this->file('attachments', [])),
            items: $this->items(),
        );
    }
}
