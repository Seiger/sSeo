# sSeo for Evolution CMS 3
![sSeo](https://repository-images.githubusercontent.com/675386929/349d7568-33f6-487d-8b87-367c13b35c4d)
[![Latest Stable Version](https://img.shields.io/packagist/v/seiger/sSeo?label=version)](https://packagist.org/packages/seiger/sseo)
[![CMS Evolution](https://img.shields.io/badge/CMS-Evolution-brightgreen.svg)](https://github.com/evolution-cms/evolution)
![PHP version](https://img.shields.io/packagist/php-v/seiger/sseo)
[![License](https://img.shields.io/packagist/l/seiger/sseo)](https://packagist.org/packages/seiger/sseo)
[![Issues](https://img.shields.io/github/issues/Seiger/sseo)](https://github.com/Seiger/sseo/issues)
[![Stars](https://img.shields.io/packagist/stars/Seiger/sseo)](https://packagist.org/packages/seiger/sseo)
[![Total Downloads](https://img.shields.io/packagist/dt/seiger/sseo)](https://packagist.org/packages/seiger/sseo)

# Welcome to sSeo!

**sSeo** - SEO Tools for Evolution CMS.
The sSeo package contains the best snippets and plugins for SEO optimization on websites built by Evolution CMS
and Blade templates.

## Features

- [x] Generates META tags automatically.
- [x] Install XML Sistemap.
- [x] Include or exclude documents from xml sitemap (via Searchable).
- [x] On page Robots index/follow settings.
- [x] Custom Seo Title.
- [x] Custom Seo Description.
- [x] Canonical Url to avoid duplicated contents.
- [x] Noindex pagination page.
- [x] Noindex custom $_GET parameters.
- [x] http(s) and WWW redirects.
- [ ] Open Graph Protocol.
- [ ] 301 Redirects to solve 404 errors in webmaster tools.

## Install by artisan package installer

Go to You /core/ folder:

```console
cd core
```

Run php artisan command

```console
php artisan package:installrequire seiger/sseo "*"
```

```console
php artisan vendor:publish --provider="Seiger\sSeo\sSeoServiceProvider"
```

[See full documentation here](https://seiger.github.io/sSeo/)