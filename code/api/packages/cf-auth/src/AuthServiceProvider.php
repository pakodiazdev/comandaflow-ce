<?php

namespace CF\CE\Auth;

use CF\CE\Auth\Commands\InstallAuthCommand;
use CF\CE\Auth\Commands\TestRolesCommand;
use CF\CE\Auth\Http\Middleware\PermissionMiddleware;
use CF\CE\Auth\Http\Middleware\RoleMiddleware;
use CF\CE\Auth\Models\User;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Passport configuration
        $this->app->register(\Laravel\Passport\PassportServiceProvider::class);
        $this->app->register(\Spatie\Permission\PermissionServiceProvider::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'cf-auth-migrations');

        // Publish seeders
        $this->publishes([
            __DIR__.'/../database/seeders' => database_path('seeders'),
        ], 'cf-auth-seeders');

        // Publish config
        $this->publishes([
            __DIR__.'/../config/cf-auth.php' => config_path('cf-auth.php'),
        ], 'cf-auth-config');

        // Load migrations (commented out to avoid conflicts with published migrations)
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        // Register middleware
        $this->registerMiddleware();

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallAuthCommand::class,
                TestRolesCommand::class,
                \CF\CE\Auth\Commands\TestUserRegistrationCommand::class,
            ]);
        }

        // Configure Passport
        $this->configurePassport();
    }

    /**
     * Configure Laravel Passport
     */
    private function configurePassport(): void
    {
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        // Note: useUserModel() has been deprecated in newer versions of Passport
        // The User model is automatically resolved through the auth configuration
    }

    /**
     * Register middleware
     */
    private function registerMiddleware(): void
    {
        $router = $this->app['router'];
        
        $router->aliasMiddleware('cf-role', RoleMiddleware::class);
        $router->aliasMiddleware('cf-permission', PermissionMiddleware::class);
    }
}
