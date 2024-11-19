---
layout: page
title: Getting started
description: Getting started with sSeo
permalink: /getting-started/
---

## Install by artisan package

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

```console
php artisan migrate
```

## Configure layout via Blade

Check if the `OnHeadWebDocumentRender` event is registered in the `<head></head>` section of your Blade layout.

```html
<!DOCTYPE html>
<html lang="{% raw %}{{evo()->getConfig('lang', 'en')}}{% endraw %}">
<head>
    <base href="{% raw %}{{evo()->getConfig('site_url', '/')}}{% endraw %}"/>
    @if(is_array($evtHead = evo()->invokeEvent('OnHeadWebDocumentRender')))
        {!!implode('', $evtHead)!!}
    @endif
    ...
</head>
```

## Configure sitemap template

Configure the output template for `sitemap.xml` by path `assets/plugins/sseo/sitemapTemplate.blade.php`
