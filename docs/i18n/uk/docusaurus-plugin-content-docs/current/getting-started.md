---
title: Початок роботи
sidebar_label: Початок роботи
sidebar_position: 2
---

## Вимоги
- Evolution CMS **3.2.7+**
- PHP **8.3+**
- Composer **2.2+**
- Одна з: **MySQL 8.0+** / **MariaDB 10.5+** / **PostgreSQL 10+** / **SQLite 3.25+**

## Встановлення за допомогою пакета artisan

Перейдіть до директорії /core/

```console
cd core
```

```console
composer update
```

Виконайте команди php artisan

```console
php artisan package:installrequire seiger/sseo "*"
```

```console
php artisan vendor:publish --provider="Seiger\sSeo\sSeoServiceProvider"
```

```console
php artisan migrate
```

> Пакет автоматично прослуховує події Evolution CMS (менеджер та фронтенд) та інтегрується з sCommerce/sArticles, коли вони доступні.

Ось і все — заголовок, опис, ключові слова, канонічний контент та роботи будуть обчислюватися для кожної сторінки за встановленими правилами.

## Де знайти модуль
Менеджер → **Інструменти → sSeo**. Ви побачите вкладки для панелі інструментів, перенаправлення, меташаблонів _(PRO)_, роботів та налаштування.

## Додавання власного блоку під SEO-полями

SEO-поля документа, товару чи будь-якого іншого ресурсу викликають подію `sSeoFieldsRender`. Кожен слухач може повернути HTML-рядок. Він показується під мета-описом, у тій самій вкладці, для кожної мови. Використовуйте це для даних про ключові слова, запропонованих заголовків, перевірок тощо, не змінюючи форму sSeo.

```php
use Illuminate\Support\Facades\Event;

Event::listen('evolution.sSeoFieldsRender', function (array $params) {
    // $params = [
    //     'resource_id' => 42,
    //     'resource_type' => 'document',   // або 'product', ...
    //     'lang' => 'base',                // або мова sLang цієї вкладки
    //     'domain_key' => 'default',       // домен sMultisite
    //     'fields' => ['meta_title' => '...', 'meta_description' => '...', 'meta_keywords' => '...', 'canonical_url' => '...', 'robots' => '...'],
    // ];
    return '<div class="my-seo-hint">' . e('Шукають 880 разів на місяць') . '</div>';
});
```

- Екрануйте те, що виводите: рядок друкується як HTML.
- Поверніть `null` або порожній рядок, щоб нічого не показувати. Слухач, який кидає виняток, пропускається й записується в лог, а поля все одно відображаються.
- Поля однієї мови називаються `sseo[<lang>][meta_title]`, `sseo[<lang>][meta_description]` і так далі. Блок розміщується всередині `div.sseo-extensions` з `data-sseo-lang="<lang>"`. Скрипт може заповнити поле таким чином; встановіть `documentDirty = true`, щоб менеджер отримав запит на збереження.
- Значення зберігаються власною кнопкою «Зберегти» форми, так ніби менеджер ввів їх сам.
