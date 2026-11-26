<?php

namespace App\Providers;

use App\Models\Provider;
use App\ThirdParty\IdentityProviders\Contract\ProviderContract;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->bind(ProviderContract::class, function(Application $app) {
            $provider = Provider::where("is_active", 1)->first();
            return $app->make($provider->class_path);
        });
    }
}
