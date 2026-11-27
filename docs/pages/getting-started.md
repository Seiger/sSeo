---
title: Getting started
sidebar_label: Getting started
sidebar_position: 2
---

## Requirements
- Evolution CMS **3.2.7+**
- PHP **8.3+**
- Composer **2.2+**
- One of: **MySQL 8.0+** / **MariaDB 10.5+** / **PostgreSQL 10+** / **SQLite 3.25+**

## Install by artisan package

Go to You /core/ folder

```console
cd core
```

```console
composer update
```

Run php artisan commands

```console
php artisan package:installrequire seiger/sseo "*"
```

```console
php artisan vendor:publish --provider="Seiger\sSeo\sSeoServiceProvider"
```

```console
php artisan migrate
```

> The package automatically listens to Evolution CMS events (manager & frontend) and integrates with sCommerce/sArticles when available.

That's it — the title, description, keywords, canonical content, and works will be calculated for each page according to the established rules.

## Where to find the module
Manager → **Tools → sSeo**. You’ll see tabs for Dashboard, Redirects, Meta Templates _(PRO)_, Robots and Configure.

## Extra

If you write your own code that can integrate with the sSeo plugin, you can check the presence of this module in the system through a configuration variable.

```php
if (evo()->getConfig('check_sSeo', false)) {
    // You code
}
```

If the plugin is installed, the result of ```evo()->getConfig('check_sSeo', false)``` will always be ```true```. Otherwise, you will get an ```false```.

### Adding your own block under the SEO fields

The SEO fields of a document, a product or any other resource fire the `sSeoFieldsRender` event. Every listener may return an HTML string. It is shown under the meta description, in the same tab, for every language. Use it for keyword data, suggested titles, checks and the like, without changing sSeo's form.

```php
use Illuminate\Support\Facades\Event;

Event::listen('evolution.sSeoFieldsRender', function (array $params) {
    // $params = [
    //     'resource_id' => 42,
    //     'resource_type' => 'document',   // or 'product', ...
    //     'lang' => 'base',                // or the sLang language of this tab
    //     'domain_key' => 'default',       // the sMultisite domain
    //     'fields' => ['meta_title' => '...', 'meta_description' => '...', 'meta_keywords' => '...', 'canonical_url' => '...', 'robots' => '...'],
    // ];
    return '<div class="my-seo-hint">' . e('Searched 880 times a month') . '</div>';
});
```

- Escape what you output: the string is printed as HTML.
- Return `null` or an empty string to show nothing. A listener that throws is skipped and logged, and the fields still render.
- The fields of one language are named `sseo[<lang>][meta_title]`, `sseo[<lang>][meta_description]` and so on. The block sits inside a `div.sseo-extensions` with `data-sseo-lang="<lang>"`. A script can fill a field this way; set `documentDirty = true` so the manager is asked to save.
- Values are saved by the form's own Save button, as if the manager had typed them.
