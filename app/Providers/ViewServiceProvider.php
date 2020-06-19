<?php

namespace App\Providers;

use App\Http\View\Composers;
use App\Services\Webpack;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use View;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(Webpack $webpack)
    {
        View::composer([
            'home',
            '*.base',
            '*.master',
            'shared.header',
        ], function ($view) {
            $lightColors = ['light', 'warning', 'white', 'orange'];
            $color = option('navbar_color');
            $view->with([
                'site_name' => option_localized('site_name'),
                'navbar_color' => $color,
                'color_mode' => in_array($color, $lightColors) ? 'light' : 'dark',
                'locale' => str_replace('_', '-', app()->getLocale()),
            ]);
        });

        View::composer('shared.head', Composers\HeadComposer::class);

        View::composer('shared.notifications', function ($view) {
            $notifications = auth()->user()->unreadNotifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->data['title'],
                ];
            });
            $view->with(['notifications' => $notifications]);
        });

        View::composer(
            ['shared.languages', 'errors.*'],
            Composers\LanguagesMenuComposer::class
        );

        View::composer('shared.user-menu', Composers\UserMenuComposer::class);

        View::composer('shared.sidebar', function ($view) {
            $view->with('sidebar_color', option('sidebar_color'));
        });

        View::composer('shared.side-menu', Composers\SideMenuComposer::class);

        View::composer('shared.user-panel', Composers\UserPanelComposer::class);

        View::composer('shared.copyright', function ($view) {
            $view->with([
                'copyright' => option_localized('copyright_prefer', 0),
                'custom_copyright' => option_localized('copyright_text'),
                'site_name' => option_localized('site_name'),
                'site_url' => option('site_url'),
            ]);
        });

        View::composer('shared.foot', Composers\FootComposer::class);

        View::composer(['errors.*', 'setup.*'], function ($view) use ($webpack) {
            // @codeCoverageIgnoreStart
            if (Str::startsWith(config('app.asset.env'), 'dev')) {
                $view->with(['scripts' => [$webpack->url('spectre.js')]]);
            } else {
                $view->with('styles', [$webpack->url('spectre.css')]);
            }
            // @codeCoverageIgnoreEnd
        });

        View::composer('errors.503', function ($view) {
            $view->with(
                'show_login_button',
                !auth()->check() && request()->is('setup/*')
            );
        });
    }
}
