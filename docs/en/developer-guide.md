# Developer Guide

## Architecture

sSeo is an Evolution CMS package with manager UI, frontend SEO runtime, and integrations with other Seiger modules.

Core pieces:

- `Seiger\sSeo\sSeoServiceProvider` registers migrations, views, translations, config presets, custom evo-ui fields, and Livewire components.
- `Seiger\sSeo\Livewire\ModulePanel` renders the manager shell and switches tabs without reloading the whole manager frame.
- `evo-ui` renders forms, tables, field help, actions, choices, and manager styling.
- `plugins/sSeoPlugin.php` connects frontend canonical checks, redirects, manager menu registration, resource SEO tabs, and module integration events.
- `src/sSeo.php` contains the main runtime facade implementation.

## Installation

Inside `core`:

```console
php artisan package:installrequire seiger/sseo "*"
php artisan vendor:publish --provider="Seiger\\sSeo\\sSeoServiceProvider"
php artisan migrate
```

For Extras-based local environments:

```console
php artisan extras extras "sSeo"
```

## Configuration Files

Runtime settings are stored in:

```text
core/custom/config/seiger/settings/sSeo.php
```

Package defaults live in:

```text
config/sSeoSettings.php
```

Manager UI presets:

- `config/settings/form.php`
- `config/analytics/form.php`
- `config/redirects/table.php`
- `config/module/tabs.php`

## Tables And Models

sSeo uses:

- `s_seo` for resource and module SEO records.
- `s_redirects` for redirect rules.
- `Seiger\sSeo\Models\sSeoModel`
- `Seiger\sSeo\Models\sRedirect`

The redirect manager is backed by `src/Tables/RedirectsTableData.php`.

## Runtime API

Facade examples:

```php
if (evo()->getConfig('check_sSeo', false)) {
    sSeo::generateSitemap((int) $resourceId);
}
```

Important methods include:

- `sSeo::headInjection()`
- `sSeo::updateSeoFields($data)`
- `sSeo::generateSitemap($id = 0)`
- `sSeo::checkCanonical()`
- `sSeo::checkMetaTitle()`
- `sSeo::checkMetaDescription()`
- `sSeo::checkMetaKeywords()`
- `sSeo::checkRobots()`
- `sSeo::config($key, $default = null)`

## Frontend Events

`plugins/sSeoPlugin.php` listens to Evolution events:

- `evolution.OnLoadSettings` for canonical URL redirects.
- `evolution.OnPageNotFound` for redirect lookup and multisite robots/sitemap fallback.
- `evolution.OnWebPagePrerender` for SEO head injection.
- `evolution.OnRenderSeoFields` for reusable resource SEO fields.
- `evolution.OnDocFormRender` and `evolution.OnDocFormSave` for resource editor integration.

## Module Integrations

### sArticles

sSeo listens to article save events and stores SEO data with `resource_type = article`.

### sCommerce

sSeo integrates with products and supports product attribute aliases for template placeholders.

### sLang

When sLang is enabled, SEO fields are stored per language. Without sLang, the base SEO record is used.

### sMultisite

When sMultisite is enabled, robots.txt, sitemap.xml, redirects, and SEO records can use the active site key.

### sApi

sSeo skips API prefixes during frontend canonical redirect checks so API requests are not redirected like public pages.

## Robots And Sitemap Files

Robots and sitemap files are runtime files. Keep write logic guarded:

- never write outside the expected site/root paths;
- keep multisite fallback behavior explicit;
- show non-writable paths as manager warnings;
- do not silently generate invalid XML.

## Testing

Run the package smoke check:

```console
php scripts/demo-smoke.php
```

Run the full unit suite from the package root:

```console
/Users/dmi3yy/PhpstormProjects/Extras/sArticles/demo/core/vendor/bin/phpunit \
  --configuration /Users/dmi3yy/PhpstormProjects/Extras/sArticles/demo/core/phpunit.xml \
  /Users/dmi3yy/PhpstormProjects/Extras/sSeo/tests/Unit
```

Run PHP syntax checks for changed PHP files:

```console
find config src lang plugins scripts tests -name "*.php" -print0 | xargs -0 -n1 php -l
```

## Development Rules

- Keep runtime SEO behavior in sSeo, not evo-ui.
- Keep evo-ui presets declarative.
- Keep manager-only Livewire components registered only in manager mode.
- Preserve legacy route names while manager compatibility depends on them.
- Add targeted tests when changing redirects, settings, robots, templates, sitemap, or resource SEO persistence.
