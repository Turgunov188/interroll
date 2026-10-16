<?php

namespace App\Http\Controllers;

use App\Actions\Leads\CreateLead;
use App\Http\Requests\StoreLeadRequest;
use Illuminate\Http\JsonResponse;

/** Every form on the public site → a lead in the CRM. */
class LeadController extends Controller
{
    public function store(StoreLeadRequest $request, CreateLead $createLead): JsonResponse
    {
        $lead = $createLead->handle($request->toData());

        return response()->json(['ok' => true, 'number' => $lead->number], JsonResponse::HTTP_CREATED);
    }
}
