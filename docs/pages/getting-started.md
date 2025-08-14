---
id: getting-started
title: Getting started
slug: /getting-started/
---

## Requirements
- Evolution CMS **3.2.0+**
- PHP **8.2+**
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

## Add SEO meta to your theme

Place this in your Blade layout `<head>`:

```html
<!DOCTYPE html>
<html lang="@evoConfig('lang', 'en')">
<head>
    <base href="@evoConfig('site_url', '/')"/>
    @if(is_array($evtHead = evo()->invokeEvent('OnHeadWebDocumentRender'))){!!implode('', $evtHead)!!}@endif
    ...
</head>
```

That's it — the title, description, keywords, canonical content, and works will be calculated for each page according to the established rules.

## Where to find the module
Manager → **Tools → sSeo**. You’ll see tabs for Dashboard, Redirects, Meta Templates _(PRO)_, Robots and Configure.
