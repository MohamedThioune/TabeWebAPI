<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Policies\SensitiveDataPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Partner::class => \App\Policies\SensitiveDataPolicy::class,
        \App\Models\Customer::class => \App\Policies\SensitiveDataPolicy::class,
        \App\Models\Enterprise::class => \App\Policies\SensitiveDataPolicy::class,
        \App\Models\File::class => \App\Policies\SensitiveDataPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        Gate::define('seeSensitiveData', [SensitiveDataPolicy::class, 'seeSensitiveData']);
//        Gate::define('seeMySensitiveData', [SensitiveDataPolicy::class, 'seeMySensitiveData']);

        // Enable hashed storage of client secrets
        // Passport::hashClientSecrets();

        // Passport::tokensExpireIn(now()->addDays(15));
        // Passport::refreshTokensExpireIn(now()->addDays(30));
        // Passport::personalAccessTokensExpireIn(now()->addMonths(6));
    }
}
