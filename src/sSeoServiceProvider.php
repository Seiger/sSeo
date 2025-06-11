<?php namespace Seiger\sSeo;

use EvolutionCMS\ServiceProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

/**
 * Class sSeoServiceProvider
 *
 * Service provider for sSeo package. Handles registration,
 * publishing resources, and managing subscriptions for PRO features.
 */
class sSeoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * Loads migrations, translations, views, and custom routes.
     * Optional checks for PRO subscription to enable additional features.
     *
     * @return void
     */
    public function boot()
    {
        // Check subscription status when loading the module
        // $subscription = $this->checkSubscription();

        // If the subscription is PRO - load additional functionality
        /* if ($subscription['plan'] === 'pro' || $subscription['plan'] === 'enterprise') {
            $this->loadProFeatures();
        } */

        // Add custom routes for package
        $this->app->router->middlewareGroup('mgr', config('app.middleware.mgr', []));
        include(__DIR__.'/Http/routes.php');

        // Load migrations, translations, views
        $this->loadMigrationsFrom(dirname(__DIR__) . '/database/migrations');
        $this->loadTranslationsFrom(dirname(__DIR__) . '/lang', 'sSeo');
        $this->publishResources();
        $this->loadViewsFrom(dirname(__DIR__) . '/views', 'sSeo');
        $this->loadViewsFrom(evo()->resourcePath('plugins/sseo'), 'sSeoAssets');
        $this->mergeConfigFrom(dirname(__DIR__) . '/config/sSeoCheck.php', 'cms.settings');

        $this->app->singleton(\Seiger\sSeo\sSeo::class);
        $this->app->alias(\Seiger\sSeo\sSeo::class, 'sSeo');
    }

    /**
     * Register the service provider.
     *
     * Registers the necessary parts and plugins for Evolution CMS.
     *
     * @return void
     */
    public function register()
    {
        // Add plugins to Evo
        $this->loadPluginsFrom(dirname(__DIR__) . '/plugins/');
    }

    /**
     * Publish the necessary resources for the package.
     *
     * This includes configuration files, images, and view templates.
     *
     * @return void
     */
    protected function publishResources()
    {
        $this->publishes([
            dirname(__DIR__) . '/config/sSeoAlias.php' => config_path('app/aliases/sSeo.php', true),
            dirname(__DIR__) . '/config/sSeoSettings.php' => config_path('seiger/settings/sSeo.php', true),
            dirname(__DIR__) . '/images/seigerit-blue.svg' => public_path('assets/site/seigerit-blue.svg'),
        ]);
    }

    /**
     * Subscription verification via API.
     *
     * Checks if the user has an active PRO subscription and caches the result.
     *
     * @return array Subscription details including plan and status.
     */
    private function checkSubscription(): array
    {
        return Cache::remember('sseo_subscription', 3600, function () {
            return Http::get('https://api.seigerit.com/verify', [
                'domain' => evo()->getConfig('site_url')
            ])->json();
        });
    }

    /**
     * Load PRO features for users with an active subscription.
     *
     * Includes additional routes, Blade templates, and plugins.
     *
     * @return void
     */
    private function loadProFeatures(): void
    {
        // Additional routes or PRO functionality
        include(__DIR__ . '/Http/pro_routes.php');

        // Load additional Blade templates or plugins
        $this->loadViewsFrom(dirname(__DIR__) . '/views/pro', 'sSeoPro');
    }
}
