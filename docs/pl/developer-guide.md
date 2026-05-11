# Przewodnik dewelopera

## Architektura

sSeo to pakiet Evolution CMS z manager UI, frontend SEO runtime i integracjami z innymi modulami Seiger.

Glowne elementy:

- `Seiger\sSeo\sSeoServiceProvider` rejestruje migrations, views, translations, config presets, custom evo-ui fields i Livewire components.
- `Seiger\sSeo\Livewire\ModulePanel` renderuje manager shell i przelacza zakladki bez reload calego manager frame.
- `evo-ui` renderuje forms, tables, field help, actions, choices i manager styling.
- `plugins/sSeoPlugin.php` laczy frontend canonical checks, redirects, manager menu, resource SEO tabs i module integration events.
- `src/sSeo.php` zawiera glowna implementacje runtime facade.

## Instalacja

W katalogu `core`:

```console
php artisan package:installrequire seiger/sseo "*"
php artisan vendor:publish --provider="Seiger\\sSeo\\sSeoServiceProvider"
php artisan migrate
```

Dla lokalnych srodowisk Extras:

```console
php artisan extras extras "sSeo"
```

## Pliki konfiguracyjne

Runtime settings:

```text
core/custom/config/seiger/settings/sSeo.php
```

Package defaults:

```text
config/sSeoSettings.php
```

Manager UI presets:

- `config/settings/form.php`
- `config/analytics/form.php`
- `config/redirects/table.php`
- `config/module/tabs.php`

## Tabele i modele

sSeo uzywa:

- `s_seo` dla SEO records zasobow i modulow;
- `s_redirects` dla redirect rules;
- `Seiger\sSeo\Models\sSeoModel`;
- `Seiger\sSeo\Models\sRedirect`.

Manager przekierowan jest oparty na `src/Tables/RedirectsTableData.php`.

## Runtime API

Przyklad facade:

```php
if (evo()->getConfig('check_sSeo', false)) {
    sSeo::generateSitemap((int) $resourceId);
}
```

Wazne metody:

- `sSeo::headInjection()`
- `sSeo::updateSeoFields($data)`
- `sSeo::generateSitemap($id = 0)`
- `sSeo::checkCanonical()`
- `sSeo::checkMetaTitle()`
- `sSeo::checkMetaDescription()`
- `sSeo::checkMetaKeywords()`
- `sSeo::checkRobots()`
- `sSeo::config($key, $default = null)`

## Frontend events

`plugins/sSeoPlugin.php` nasluchuje Evolution events:

- `evolution.OnLoadSettings` dla canonical URL redirects.
- `evolution.OnPageNotFound` dla redirect lookup i multisite robots/sitemap fallback.
- `evolution.OnWebPagePrerender` dla SEO head injection.
- `evolution.OnRenderSeoFields` dla reusable resource SEO fields.
- `evolution.OnDocFormRender` i `evolution.OnDocFormSave` dla resource editor integration.

## Integracje

### sArticles

sSeo nasluchuje article save events i zapisuje SEO data z `resource_type = article`.

### sCommerce

sSeo integruje sie z products i wspiera product attribute aliases dla template placeholders.

### sLang

Gdy sLang jest wlaczony, SEO fields sa zapisywane per jezyk. Bez sLang uzywany jest base SEO record.

### sMultisite

Gdy sMultisite jest wlaczony, robots.txt, sitemap.xml, redirects i SEO records moga uzywac aktywnego site key.

### sApi

sSeo pomija API prefixes podczas frontend canonical redirect checks, aby API requests nie byly przekierowywane jak public pages.

## Robots i Sitemap

Robots i sitemap to runtime files. Logika zapisu musi byc chroniona:

- nie zapisuj poza oczekiwanymi site/root paths;
- zachowaj jawny multisite fallback behavior;
- pokazuj manager warnings dla non-writable paths;
- nie generuj niepoprawnego XML po cichu.

## Testy

Smoke check:

```console
php scripts/demo-smoke.php
```

Pelny unit suite:

```console
/Users/dmi3yy/PhpstormProjects/Extras/sArticles/demo/core/vendor/bin/phpunit \
  --configuration /Users/dmi3yy/PhpstormProjects/Extras/sArticles/demo/core/phpunit.xml \
  /Users/dmi3yy/PhpstormProjects/Extras/sSeo/tests/Unit
```

PHP syntax checks:

```console
find config src lang plugins scripts tests -name "*.php" -print0 | xargs -0 -n1 php -l
```

## Zasady rozwoju

- Runtime SEO behavior powinien pozostac w sSeo, nie w evo-ui.
- evo-ui presets powinny byc deklaratywne.
- Manager-only Livewire components rejestruj tylko w manager mode.
- Zachowaj legacy route names, dopoki zalezy od nich manager compatibility.
- Dodawaj targeted tests przy zmianach redirects, settings, robots, templates, sitemap albo resource SEO persistence.
