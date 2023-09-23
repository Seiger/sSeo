---
layout: page
title: Getting started
description: Getting started with sSeo
permalink: /getting-started/
---

## Install by artisan package installer

Go to You /core/ folder

```console
cd core
```

Run php artisan commands

```console
php artisan package:installrequire seiger/sseo "*"
```

```console
php artisan vendor:publish --provider="Seiger\sSeo\sSeoServiceProvider"
```

## Configure layout via Blade

Put in Your `<head></head>` section this code

```php
@if(is_array($evtHead = evo()->invokeEvent('OnHeadWebDocumentRender'))){!!implode('', $evtHead)!!}@endif
```