<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Pages\Registration;
use Filament\Navigation\MenuItem;
use Illuminate\Support\Facades\Auth;
use Filament\FontProviders\GoogleFontProvider;

class AdminPanelProvider extends PanelProvider
{
    protected function getUserMenuItems(): array
    {
        if (auth()->check() && auth()->user()->is_admin) {
            return [];
        }

        return [
            MenuItem::make()
                ->label('Felhasználó meghívása')
                ->icon('heroicon-o-user-plus')
                ->url(fn () => route('filament.admin.pages.invite-user')),
        ];
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->font('Montserrat', provider: GoogleFontProvider::class)
            ->brandName('Technorgaz')
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('5rem')
            ->readOnlyRelationManagersOnResourceViewPagesByDefault(false)//can make any relation record on view page
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->registration(Registration::class)
            ->passwordReset()
            ->profile()
            ->emailVerification()
            ->userMenuItems($this->getUserMenuItems())
            ->colors([
                'primary' => Color::hex('#2A5325'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
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
