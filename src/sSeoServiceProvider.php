<?php namespace Seiger\sSeo;

use EvolutionCMS\ServiceProvider;
use Livewire\Livewire;
use Seiger\sSeo\Console\PublishAssets;

/**
 * Class sSeoServiceProvider
 *
 * Service provider for sSeo package. Handles registration,
 * publishing resources, and registering manager evo-ui surfaces.
 */
class sSeoServiceProvider extends ServiceProvider
{
    protected string $root;

    public function __construct($app)
    {
        parent::__construct($app);

        $this->root = dirname(__DIR__);
    }

    /**
     * Boot the application services.
     *
     * Loads migrations, translations, views, and custom routes.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom($this->root . '/config/sSeoSettings.php', 'seiger.settings.sSeo');

        $this->app->singleton(sSeo::class);
        $this->app->alias(sSeo::class, 'sSeo');

        if ($this->app->runningInConsole()) {
            $this->commands([
                PublishAssets::class,
            ]);
        }

        if (defined('IN_MANAGER_MODE') && IN_MANAGER_MODE) {
            $this->bootManager();
        }
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
        $this->loadPluginsFrom($this->root . '/plugins/');
    }

    protected function bootManager(): void
    {
        // Add custom routes for package
        $this->app->router->middlewareGroup('mgr', config('app.middleware.mgr', []));
        include($this->root . '/src/Http/routes.php');

        // Load migrations, translations, views
        $this->loadMigrationsFrom($this->root . '/database/migrations');
        $this->loadTranslationsFrom($this->root . '/lang', 'sSeo');
        $this->publishResources();
        $this->loadViewsFrom($this->root . '/views', 'sSeo');
        $this->loadViewsFrom(evo()->resourcePath('plugins/sseo'), 'sSeoAssets');
        $this->mergeConfigFrom($this->root . '/config/sSeoCheck.php', 'cms.settings');
        $this->mergeConfigFrom($this->root . '/config/module/tabs.php', 'sseo.module.tabs');
        $this->mergeConfigFrom($this->root . '/config/redirects/table.php', 'sseo.redirects.table');
        $this->mergeConfigFrom($this->root . '/config/activity/table.php', 'sseo.activity.table');
        $this->mergeConfigFrom($this->root . '/config/settings/form.php', 'evo-ui.forms.sseo.settings');
        $this->mergeConfigFrom($this->root . '/config/analytics/form.php', 'evo-ui.forms.sseo.analytics');
        app(\EvoUI\EvoUI::class)->registerFormField('sseo-server-protocol', 'sSeo::components.form.server-protocol');
        config()->set('evo-ui.forms.sseo.analytics', \Seiger\sSeo\Support\AnalyticsSettingsForm::make(config('evo-ui.forms.sseo.analytics', [])));

        Livewire::component('sseo.module-panel', \Seiger\sSeo\Livewire\ModulePanel::class);
        Livewire::component('sseo.meta-templates-editor', \Seiger\sSeo\Livewire\MetaTemplatesEditor::class);
        Livewire::component('sseo.robots-editor', \Seiger\sSeo\Livewire\RobotsEditor::class);
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
            $this->root . '/config/sSeoAlias.php' => config_path('app/aliases/sSeo.php', true),
            $this->root . '/config/sSeoSettings.php' => config_path('seiger/settings/sSeo.php', true),
            $this->root . '/images/seigerit.svg' => public_path('assets/site/seigerit.svg'),
            $this->root . '/images/logo.svg' => public_path('assets/site/sseo.svg'),
        ]);
    }

}
