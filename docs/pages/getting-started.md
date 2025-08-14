---
id: getting-started
title: Getting started
slug: /getting-started/
---

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

## Configure layout via Blade

Check if the `OnHeadWebDocumentRender` event is registered in the `<head></head>` section of your Blade layout.

```html
<!DOCTYPE html>
<html lang="@evoConfig('lang', 'en')">
<head>
    <base href="@evoConfig('site_url', '/')"/>
    @if(is_array($evtHead = evo()->invokeEvent('OnHeadWebDocumentRender'))){!!implode('', $evtHead)!!}@endif
    ...
</head>
```
