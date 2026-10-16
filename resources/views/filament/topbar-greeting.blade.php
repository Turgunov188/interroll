{{-- Top bar, left of the user menu: "Добро пожаловать, Имя · Роль" --}}
@php($user = auth()->user())
@if ($user)
    <div style="display: flex; flex-direction: column; align-items: flex-end; line-height: 1.2; margin-inline-end: 0.5rem;">
        <span style="font-size: 0.875rem; font-weight: 600;">Добро пожаловать, {{ $user->name }}</span>
        @if ($user->role)
            <span style="font-size: 0.75rem; opacity: 0.6;">{{ $user->role->getLabel() }}</span>
        @endif
    </div>
@endif
