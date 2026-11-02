/**
 * Sends a site form to POST /leads (App\Http\Controllers\LeadController).
 * Resolves with the request number; rejects with LeadError (validation errors by field, if any).
 */
export class LeadError extends Error {
    constructor(message, fields = {}) {
        super(message);
        this.fields = fields;
    }
}

export async function sendLead(url, formData) {
    let response;

    try {
        response = await fetch(url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
    } catch {
        throw new LeadError('network');
    }

    const body = await response.json().catch(() => ({}));

    if (response.status === 422) {
        throw new LeadError('invalid', body.errors || {});
    }

    if (!response.ok) {
        throw new LeadError(response.status === 429 ? 'throttled' : 'server');
    }

    return body.number || null;
}
