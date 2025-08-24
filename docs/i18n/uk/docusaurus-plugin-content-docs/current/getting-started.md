---
title: Початок роботи
sidebar_label: Початок роботи
sidebar_position: 2
---

## Вимоги
- Evolution CMS **3.2.0+**
- PHP **8.2+**
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