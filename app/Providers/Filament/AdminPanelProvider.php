<?php

namespace App\Providers\Filament;

use Althinect\FilamentSpatieRolesPermissions\Commands\Permission;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationItem;
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
use Althinect\FilamentSpatieRolesPermissions\FilamentSpatieRolesPermissionsPlugin;
use App\Filament\Resources\BrandResource;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CustomerResource;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\SectionResource;
use App\Filament\Resources\UserResource;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('/')
            ->path('/')
            ->login()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Settings')
                    ->url('')
                    ->icon('heroicon-o-cog-6-tooth'),
                'logout' => MenuItem::make()
                    ->label('Log Out')
            ])
            ->sidebarFullyCollapsibleOnDesktop()
            ->font('Poppins')
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
            ])
            ->plugin(FilamentSpatieRolesPermissionsPlugin::make())
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder->groups([
                    NavigationGroup::make()
                        ->items([
                            NavigationItem::make('Dashboard')
                                ->icon('heroicon-o-home')
                                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.pages.dashboard'))
                                ->url(fn (): string => Dashboard::getUrl()),
                        ]),
                    NavigationGroup::make('Website')
                        ->items([
                            ...UserResource::getNavigationItems(),
                            ...CustomerResource::getNavigationItems(),
                            ...BrandResource::getNavigationItems(),
                        ]),
                    NavigationGroup::make('shop')
                        ->items([
                            ...OrderResource::getNavigationItems(),
                            ...ProductResource::getNavigationItems(),
                            ...SectionResource::getNavigationItems(),
                            ...CategoryResource::getNavigationItems(),
                        ]),
                    NavigationGroup::make('Settings')
                        ->items([
                            NavigationItem::make('Roles')
                                ->icon('heroicon-o-lock-closed')
                                ->isActiveWhen(fn (): bool => request()->routeIs([
                                    'filament.admin.resources.roles.index',
                                    'filament.admin.resources.roles.create',
                                    'filament.admin.resources.roles.edit',
                                    'filament.admin.resources.roles.view',
                                ]))
                                ->url(fn (): string => '/roles'),
                            NavigationItem::make('Permissions')
                                ->icon('heroicon-o-lock-closed')
                                ->isActiveWhen(fn (): bool => request()->routeIs([
                                    'filament.admin.permissions.index',
                                    'filament.admin.permissions.create',
                                    'filament.admin.permissions.edit',
                                    'filament.admin.permissions.view',
                                ]))
                                ->url(fn (): string => '/permissions'),
                        ]),
                    NavigationGroup::make('External')
                        ->items([
                            NavigationItem::make('Application')
                                ->url('https://blog.codewithdary.com', shouldOpenInNewTab:true)
                                ->icon('heroicon-o-pencil-square')
                                ->sort(2),
                    ]),
                ]);
            });
    }
}