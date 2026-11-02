{{--
    A form that creates a lead in the CRM (POST /leads → App\Http\Controllers\LeadController).
    Fields are named like the request expects (name, phone, company, email, message, attachments[], items…).
    Works without JavaScript (normal POST + redirect back); with it the leadForm component
    (resources/js/site/components/lead-form.js) sends it in the background.

    <x-site.lead-form type="recoating" class="lead-form">
        <x-site.field name="phone" … />
        <x-slot:success>…shown instead of the form after sending…</x-slot:success>
    </x-site.lead-form>
--}}
@props(['type' => 'quote', 'formClass' => 'lead-form'])
@php($hasSuccess = isset($success) && $success->isNotEmpty())
<div {{ $attributes }} x-data="leadForm({ success: @js($hasSuccess) })" @lead:reset="again()">
    <form method="post" action="{{ route('leads.store') }}" enctype="multipart/form-data" class="{{ $formClass }}"
          @submit.prevent="submit($event)" @if ($hasSuccess) x-show="!sent" @endif>
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">
        <input type="hidden" name="locale" value="{{ $site->locale->value }}">
        <input type="hidden" name="source" value="{{ request()->getRequestUri() }}">
        <div class="lead-form__trap" aria-hidden="true">
            <label>Website <input type="text" name="{{ \App\Http\Middleware\HoneypotTrap::FIELD }}" tabindex="-1" autocomplete="off"></label>
        </div>

        {{ $slot }}
    </form>

    @if ($hasSuccess)
        <div class="success pop" x-show="sent" x-cloak role="status">
            <span class="success__mark" aria-hidden="true">✓</span>
            {{ $success }}
        </div>
    @endif
</div>
