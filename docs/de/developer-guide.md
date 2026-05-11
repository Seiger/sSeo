# Entwicklerhandbuch

## Architektur

sSeo ist ein Evolution CMS Paket mit Manager UI, Frontend SEO Runtime und Integrationen mit anderen Seiger Modulen.

Kernteile:

- `Seiger\sSeo\sSeoServiceProvider` registriert migrations, views, translations, config presets, custom evo-ui fields und Livewire components.
- `Seiger\sSeo\Livewire\ModulePanel` rendert die Manager-Shell und wechselt interne Tabs ohne Reload des gesamten Manager-Frames.
- `evo-ui` rendert forms, tables, field help, actions, choices und manager styling.
- `plugins/sSeoPlugin.php` verbindet frontend canonical checks, redirects, manager menu, resource SEO tabs und module integration events.
- `src/sSeo.php` enthaelt die zentrale Runtime-Facade-Implementierung.

## Installation

Im Verzeichnis `core`:

```console
php artisan package:installrequire seiger/sseo "*"
php artisan vendor:publish --provider="Seiger\\sSeo\\sSeoServiceProvider"
php artisan migrate
```

Fuer lokale Extras-Umgebungen:

```console
php artisan extras extras "sSeo"
```

## Konfigurationsdateien

Runtime settings liegen hier:

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

## Tabellen und Modelle

sSeo verwendet:

- `s_seo` fuer SEO records von Ressourcen und Modulen;
- `s_redirects` fuer redirect rules;
- `Seiger\sSeo\Models\sSeoModel`;
- `Seiger\sSeo\Models\sRedirect`.

Der Redirect Manager basiert auf `src/Tables/RedirectsTableData.php`.

## Runtime API

Facade-Beispiel:

```php
if (evo()->getConfig('check_sSeo', false)) {
    sSeo::generateSitemap((int) $resourceId);
}
```

Wichtige Methoden:

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

`plugins/sSeoPlugin.php` hoert auf Evolution events:

- `evolution.OnLoadSettings` fuer canonical URL redirects.
- `evolution.OnPageNotFound` fuer redirect lookup und multisite robots/sitemap fallback.
- `evolution.OnWebPagePrerender` fuer SEO head injection.
- `evolution.OnRenderSeoFields` fuer reusable resource SEO fields.
- `evolution.OnDocFormRender` und `evolution.OnDocFormSave` fuer resource editor integration.

## Integrationen

### sArticles

sSeo hoert article save events und speichert SEO data mit `resource_type = article`.

### sCommerce

sSeo integriert products und unterstuetzt product attribute aliases fuer template placeholders.

### sLang

Wenn sLang aktiviert ist, werden SEO fields pro Sprache gespeichert. Ohne sLang wird der base SEO record genutzt.

### sMultisite

Wenn sMultisite aktiviert ist, koennen robots.txt, sitemap.xml, redirects und SEO records den aktiven site key nutzen.

### sApi

sSeo ueberspringt API prefixes bei frontend canonical redirect checks, damit API requests nicht wie public pages umgeleitet werden.

## Robots und Sitemap

Robots und Sitemap sind runtime files. Schreiblogik muss geschuetzt bleiben:

- nicht ausserhalb erwarteter site/root paths schreiben;
- multisite fallback behavior explizit halten;
- manager warnings fuer non-writable paths anzeigen;
- kein ungueltiges XML still generieren.

## Tests

Smoke check:

```console
php scripts/demo-smoke.php
```

Komplette Unit-Suite:

```console
/Users/dmi3yy/PhpstormProjects/Extras/sArticles/demo/core/vendor/bin/phpunit \
  --configuration /Users/dmi3yy/PhpstormProjects/Extras/sArticles/demo/core/phpunit.xml \
  /Users/dmi3yy/PhpstormProjects/Extras/sSeo/tests/Unit
```

PHP syntax checks:

```console
find config src lang plugins scripts tests -name "*.php" -print0 | xargs -0 -n1 php -l
```

## Entwicklungsregeln

- Runtime SEO behavior bleibt in sSeo, nicht in evo-ui.
- evo-ui presets bleiben deklarativ.
- Manager-only Livewire components nur im manager mode registrieren.
- Legacy route names erhalten, solange Manager-Kompatibilitaet sie braucht.
- Targeted tests ergaenzen, wenn redirects, settings, robots, templates, sitemap oder resource SEO persistence geaendert werden.
