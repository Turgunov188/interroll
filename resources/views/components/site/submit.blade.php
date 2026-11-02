{{-- Submit button of a lead form: disabled while sending. --}}
@props(['label'])
<button type="submit" {{ $attributes->class(['btn btn--dark btn--lg btn--block field--full']) }} :disabled="sending">
    <span x-show="!sending">{{ $label }}</span>
    <span x-show="sending" x-cloak>{{ $site->t('common.form.sending') }}</span>
</button>
