<?php

namespace App\Providers\Filament;

use App\Http\Middleware\SetAdminLocale;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Inter Roll')
            ->colors([
                'primary' => Color::hex('#F28C00'),
            ])
            ->navigationGroups([
                NavigationGroup::make('CRM'),
                NavigationGroup::make('Каталог'),
                NavigationGroup::make('Контент'),
                NavigationGroup::make('Настройки')->collapsed(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            // «Добро пожаловать» in the top bar instead of a dashboard card.
            ->renderHook(PanelsRenderHook::USER_MENU_BEFORE, fn () => view('filament.topbar-greeting'))
            // Separate the sidebar from the page: own background + thin border (light and dark mode).
            ->renderHook(PanelsRenderHook::HEAD_END, fn (): string => '<style>
                @media (min-width: 64rem) {
                    .fi-sidebar { background-color: #fff; border-inline-end: 1px solid rgb(17 20 24 / .1); }
                    .dark .fi-sidebar { background-color: var(--gray-900, #18181b); border-inline-end-color: rgb(255 255 255 / .1); }
                }
            </style>')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                SetAdminLocale::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
