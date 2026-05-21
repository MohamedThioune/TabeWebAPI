<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Customer;
use App\Models\Enterprise;
use App\Models\File;
use App\Models\Partner;
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
        Partner::class => SensitiveDataPolicy::class,
        Customer::class => SensitiveDataPolicy::class,
        Enterprise::class => SensitiveDataPolicy::class,
        File::class => SensitiveDataPolicy::class,
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
