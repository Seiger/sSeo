# sSeo for Evolution CMS
![sSeo](https://repository-images.githubusercontent.com/675386929/349d7568-33f6-487d-8b87-367c13b35c4d)
[![Latest Stable Version](https://img.shields.io/packagist/v/seiger/sSeo?label=version)](https://packagist.org/packages/seiger/sseo)
[![CMS Evolution](https://img.shields.io/badge/CMS-Evolution-brightgreen.svg)](https://github.com/evolution-cms/evolution)
![PHP version](https://img.shields.io/packagist/php-v/seiger/sseo)
[![License](https://img.shields.io/packagist/l/seiger/sseo)](https://packagist.org/packages/seiger/sseo)
[![Issues](https://img.shields.io/github/issues/Seiger/sseo)](https://github.com/Seiger/sseo/issues)
[![Stars](https://img.shields.io/packagist/stars/Seiger/sseo)](https://packagist.org/packages/seiger/sseo)
[![Total Downloads](https://img.shields.io/packagist/dt/seiger/sseo)](https://packagist.org/packages/seiger/sseo)

# Welcome to sSeo!

Elevate your website's visibility and performance on search engines with **sSeo**, 
a robust SEO tools package meticulously crafted for Evolution CMS and Blade templates. 
Designed to empower website administrators and developers, this feature-rich plugin is
your go-to solution for comprehensive SEO optimization.

Unleash the full potential of your website's SEO strategy with **sSeo**.
Whether you are fine-tuning existing content or embarking on new web projects,
this plugin provides the tools you need for a competitive edge in the digital landscape.

## Features

- [x] Integration with:
  - [x] Evolution CMS Resources.
  - [x] **[sCommerce](https://github.com/Seiger/sCommerce)** Products.
  - [x] **[sArticles](https://github.com/Seiger/sArticles)** Publications.
  - [x] **[sMultisite](https://github.com/Seiger/sMultisite)** robots.txt and sitemap.xml.
- [x] Custom SEO Title, Description and Keywords.
- [x] SEO Meta Templates Title, Description and Keywords (pro).
- [x] Canonical URL Implementation.
- [x] Automatic META Tags Generation.
- [x] XML Sitemap Generation.
- [x] Include or exclude documents from xml sitemap.
- [ ] On page Robots index/follow settings.
- [x] Noindex for Pagination and Custom $_GET Parameters.
- [x] 30x Redirects for Resolving 404 Errors.
- [x] HTTP(S) and WWW Redirects.
- [x] Manage robots.txt file via Admin Panel.
- [ ] Open Graph Protocol Integration.
- [ ] SEO Dashboard and Reporting.
- [ ] Social Media Integration Metrics.
- [ ] AI-Powered SEO Recommendations.
- [ ] Featured Snippets Optimization.
- [ ] Local Business Schema Markup.
- [ ] SEO Health Check
- [ ] Rich Snippets (Schema Markup) Support
- [ ] Performance Optimization Recommendations
- [ ] Real-time SEO Analytics

## Install by artisan package installer

Go to You /core/ folder:

```console
cd core
```

```console
composer update
```

Run php artisan command

```console
php artisan package:installrequire seiger/sseo "*"
```

```console
php artisan vendor:publish --provider="Seiger\sSeo\sSeoServiceProvider"
```

```console
php artisan migrate
```

[See full documentation here](https://seiger.github.io/sSeo/)