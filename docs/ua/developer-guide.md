# Гайд розробника

## Архітектура

sSeo - це пакет Evolution CMS з manager UI, frontend SEO runtime та інтеграціями з іншими Seiger modules.

Основні частини:

- `Seiger\sSeo\sSeoServiceProvider` реєструє migrations, views, translations, config presets, custom evo-ui fields і Livewire components.
- `Seiger\sSeo\Livewire\ModulePanel` рендерить manager shell і перемикає внутрішні вкладки без reload усього manager frame.
- `evo-ui` рендерить forms, tables, field help, actions, choices і manager styling.
- `plugins/sSeoPlugin.php` підключає frontend canonical checks, redirects, manager menu, resource SEO tabs і module integration events.
- `src/sSeo.php` містить основну runtime facade реалізацію.

## Встановлення

У директорії `core`:

```console
php artisan package:installrequire seiger/sseo "*"
php artisan vendor:publish --provider="Seiger\\sSeo\\sSeoServiceProvider"
php artisan migrate
```

Для локальних Extras environments:

```console
php artisan extras extras "sSeo"
```

## Конфігураційні файли

Runtime settings зберігаються тут:

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

## Таблиці і моделі

sSeo використовує:

- `s_seo` для SEO records ресурсів і модулів;
- `s_redirects` для redirect rules;
- `Seiger\sSeo\Models\sSeoModel`;
- `Seiger\sSeo\Models\sRedirect`.

Redirect manager працює через `src/Tables/RedirectsTableData.php`.

## Runtime API

Приклад facade:

```php
if (evo()->getConfig('check_sSeo', false)) {
    sSeo::generateSitemap((int) $resourceId);
}
```

Важливі методи:

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

`plugins/sSeoPlugin.php` слухає Evolution events:

- `evolution.OnLoadSettings` для canonical URL redirects.
- `evolution.OnPageNotFound` для redirect lookup і multisite robots/sitemap fallback.
- `evolution.OnWebPagePrerender` для SEO head injection.
- `evolution.OnRenderSeoFields` для reusable resource SEO fields.
- `evolution.OnDocFormRender` і `evolution.OnDocFormSave` для resource editor integration.

## Інтеграції

### sArticles

sSeo слухає article save events і зберігає SEO data з `resource_type = article`.

### sCommerce

sSeo інтегрується з products і підтримує product attribute aliases для template placeholders.

### sLang

Коли увімкнений sLang, SEO fields зберігаються по мовах. Без sLang використовується base SEO record.

### sMultisite

Коли увімкнений sMultisite, robots.txt, sitemap.xml, redirects і SEO records можуть використовувати активний site key.

### sApi

sSeo пропускає API prefixes під час frontend canonical redirect checks, щоб API requests не редіректились як public pages.

## Robots і Sitemap файли

Robots і sitemap - це runtime files. Логіку запису треба тримати guarded:

- не писати за межі очікуваних site/root paths;
- явно зберігати multisite fallback behavior;
- показувати manager warnings для non-writable paths;
- не генерувати невалідний XML мовчки.

## Тести

Smoke check пакета:

```console
php scripts/demo-smoke.php
```

Повний unit suite з кореня пакета:

```console
/Users/dmi3yy/PhpstormProjects/Extras/sArticles/demo/core/vendor/bin/phpunit \
  --configuration /Users/dmi3yy/PhpstormProjects/Extras/sArticles/demo/core/phpunit.xml \
  /Users/dmi3yy/PhpstormProjects/Extras/sSeo/tests/Unit
```

PHP syntax checks для змінених PHP файлів:

```console
find config src lang plugins scripts tests -name "*.php" -print0 | xargs -0 -n1 php -l
```

## Правила розробки

- Runtime SEO behavior має лишатися в sSeo, не в evo-ui.
- evo-ui presets мають бути декларативними.
- Manager-only Livewire components реєструються лише в manager mode.
- Legacy route names зберігаються, поки на них спирається manager compatibility.
- Для redirects, settings, robots, templates, sitemap або resource SEO persistence додавайте targeted tests.
