---
title: Getting started
sidebar_label: Getting started
sidebar_position: 2
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
