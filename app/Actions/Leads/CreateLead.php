<?php

namespace App\Actions\Leads;

use App\Data\LeadData;
use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/** Stores a lead from the public site together with its attachments and requested parts. */
final class CreateLead
{
    public const DISK = 'local';

    public const DIRECTORY = 'leads';

    public const ANONYMOUS_NAME = 'С сайта';

    public function __construct(private readonly AddLeadItems $addItems) {}

    public function handle(LeadData $data): Lead
    {
        $paths = array_map(
            fn (UploadedFile $file): string => $file->store(self::DIRECTORY, self::DISK),
            $data->attachments,
        );

        try {
            return DB::transaction(function () use ($data, $paths): Lead {
                $lead = Lead::create([
                    'type' => $data->type,
                    'status' => LeadStatus::New,
                    'name' => $data->name ?: ($data->company ?: self::ANONYMOUS_NAME),
                    'phone' => $data->phone,
                    'company' => $data->company,
                    'email' => $data->email,
                    'message' => $data->message,
                    'attachments' => $paths ?: null,
                    'payload' => $data->payload,
                    'locale' => $data->locale,
                    'source' => $data->source,
                ]);

                $this->addItems->handle($lead, $data->items);

                return $lead;
            });
        } catch (Throwable $e) {
            // Do not leave orphaned files when the insert fails.
            Storage::disk(self::DISK)->delete($paths);

            throw $e;
        }
    }
}
