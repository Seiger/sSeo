<?php namespace Seiger\sSeo;

use EvolutionCMS\ServiceProvider;

class sSeoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Add custom routes for package
        $this->app->router->middlewareGroup('mgr', config('app.middleware.mgr', []));
        include(__DIR__.'/Http/routes.php');

        // MultiLang
        $this->loadTranslationsFrom(dirname(__DIR__) . '/lang', 'sSeo');

        // For use config
        $this->publishes([
            dirname(__DIR__) . '/config/sSeoAlias.php' => config_path('app/aliases/sSeo.php', true),
            dirname(__DIR__) . '/config/sSeoSettings.php' => config_path('seiger/settings/sSeo.php', true),
            dirname(__DIR__) . '/images/seigerit-yellow.svg' => public_path('assets/site/seigerit-yellow.svg'),
            dirname(__DIR__) . '/config/sSeoSitemapTemplate.blade.php' => public_path('assets/plugins/sseo/sitemapTemplate.blade.php'),
        ]);

        // Views
        $this->loadViewsFrom(dirname(__DIR__) . '/views', 'sSeo');
        $this->loadViewsFrom(evo()->resourcePath('plugins/sseo'), 'sSeoAssets');

        $this->app->singleton(\Seiger\sSeo\sSeo::class);
        $this->app->alias(\Seiger\sSeo\sSeo::class, 'sSeo');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Add plugins to Evo
        $this->loadPluginsFrom(dirname(__DIR__) . '/plugins/');
    }
}
