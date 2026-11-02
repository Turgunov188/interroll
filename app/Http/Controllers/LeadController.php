<?php

namespace App\Http\Controllers;

use App\Actions\Leads\CreateLead;
use App\Http\Requests\StoreLeadRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * Every form on the public site → a lead in the CRM. The site sends forms in the background
 * (JSON answer); without JavaScript the browser posts them and comes back to the page.
 */
class LeadController extends Controller
{
    public function store(StoreLeadRequest $request, CreateLead $createLead): JsonResponse|RedirectResponse
    {
        $lead = $createLead->handle($request->toData());

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'number' => $lead->number], JsonResponse::HTTP_CREATED);
        }

        return back()->with('lead_number', $lead->number);
    }
}
