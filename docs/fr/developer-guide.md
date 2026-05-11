# Guide developpeur

## Architecture

sSeo est un package Evolution CMS avec manager UI, runtime SEO frontend et integrations avec d'autres modules Seiger.

Pieces principales:

- `Seiger\sSeo\sSeoServiceProvider` enregistre migrations, views, translations, config presets, custom evo-ui fields et Livewire components.
- `Seiger\sSeo\Livewire\ModulePanel` rend le manager shell et change les onglets internes sans recharger tout le manager frame.
- `evo-ui` rend forms, tables, field help, actions, choices et manager styling.
- `plugins/sSeoPlugin.php` connecte frontend canonical checks, redirects, manager menu, resource SEO tabs et module integration events.
- `src/sSeo.php` contient l'implementation principale de la runtime facade.

## Installation

Dans `core`:

```console
php artisan package:installrequire seiger/sseo "*"
php artisan vendor:publish --provider="Seiger\\sSeo\\sSeoServiceProvider"
php artisan migrate
```

Pour les environnements Extras locaux:

```console
php artisan extras extras "sSeo"
```

## Fichiers de configuration

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

## Tables et modeles

sSeo utilise:

- `s_seo` pour les SEO records des ressources et modules;
- `s_redirects` pour les redirect rules;
- `Seiger\sSeo\Models\sSeoModel`;
- `Seiger\sSeo\Models\sRedirect`.

Le manager des redirections utilise `src/Tables/RedirectsTableData.php`.

## Runtime API

Exemple facade:

```php
if (evo()->getConfig('check_sSeo', false)) {
    sSeo::generateSitemap((int) $resourceId);
}
```

Methodes importantes:

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

`plugins/sSeoPlugin.php` ecoute les events Evolution:

- `evolution.OnLoadSettings` pour canonical URL redirects.
- `evolution.OnPageNotFound` pour redirect lookup et multisite robots/sitemap fallback.
- `evolution.OnWebPagePrerender` pour SEO head injection.
- `evolution.OnRenderSeoFields` pour reusable resource SEO fields.
- `evolution.OnDocFormRender` et `evolution.OnDocFormSave` pour resource editor integration.

## Integrations

### sArticles

sSeo ecoute les article save events et stocke les SEO data avec `resource_type = article`.

### sCommerce

sSeo s'integre avec products et supporte product attribute aliases pour les template placeholders.

### sLang

Quand sLang est active, les SEO fields sont stockes par langue. Sans sLang, le base SEO record est utilise.

### sMultisite

Quand sMultisite est active, robots.txt, sitemap.xml, redirects et SEO records peuvent utiliser le site key actif.

### sApi

sSeo ignore les API prefixes pendant les frontend canonical redirect checks pour eviter de rediriger les API requests comme des public pages.

## Robots et Sitemap

Robots et sitemap sont des runtime files. La logique d'ecriture doit rester protegee:

- ne jamais ecrire hors des site/root paths attendus;
- garder le multisite fallback behavior explicite;
- afficher des manager warnings pour les non-writable paths;
- ne pas generer silencieusement du XML invalide.

## Tests

Smoke check:

```console
php scripts/demo-smoke.php
```

Suite unit complete:

```console
/Users/dmi3yy/PhpstormProjects/Extras/sArticles/demo/core/vendor/bin/phpunit \
  --configuration /Users/dmi3yy/PhpstormProjects/Extras/sArticles/demo/core/phpunit.xml \
  /Users/dmi3yy/PhpstormProjects/Extras/sSeo/tests/Unit
```

PHP syntax checks:

```console
find config src lang plugins scripts tests -name "*.php" -print0 | xargs -0 -n1 php -l
```

## Regles de developpement

- Le runtime SEO behavior reste dans sSeo, pas dans evo-ui.
- Les evo-ui presets restent declaratifs.
- Les manager-only Livewire components sont enregistres seulement en manager mode.
- Les legacy route names restent disponibles tant que la compatibilite manager en depend.
- Ajoutez des targeted tests pour les changements redirects, settings, robots, templates, sitemap ou resource SEO persistence.
