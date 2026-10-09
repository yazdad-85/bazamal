<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Password::defaults(function () {
            $rule = Password::min(12)->mixedCase()->numbers();

            return $this->app->isProduction() ? $rule->symbols() : $rule;
        });

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        View::composer(['layouts.store', 'layouts.admin', 'layouts.guest'], function ($view) {
            try {
                if (! Schema::hasTable('settings')) {
                    $view->with([
                        'siteName' => config('app.name', 'Bazar Amal'),
                        'siteLogoUrl' => null,
                    ]);

                    return;
                }

                $view->with([
                    'siteName' => Setting::siteName(),
                    'siteLogoUrl' => Setting::siteLogoUrl(),
                ]);
            } catch (\Throwable) {
                $view->with([
                    'siteName' => config('app.name', 'Bazar Amal'),
                    'siteLogoUrl' => null,
                ]);
            }
        });
    }
}
