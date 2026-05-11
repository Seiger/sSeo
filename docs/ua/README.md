# Документація sSeo

sSeo - це модуль Evolution CMS для керування технічними SEO правилами, метаданими ресурсів, редіректами, robots.txt, sitemap.xml, аналітикою та SEO шаблонами з менеджера.

Поточний інтерфейс менеджера побудований на **evo-ui** і **Livewire**. Модуль відкривається як компактний workspace з вкладками для стану sitemap, редіректів, robots.txt, конфігурації та PRO меташаблонів. Аналітика живе всередині вкладки Конфігурація.

## Гайди

- [Гайд користувача](user-guide.md)
- [Гайд розробника](developer-guide.md)

## Основні можливості

- SEO поля для ресурсів Evolution CMS і підтримуваних модулів.
- Meta title, description, keywords, canonical URL, robots mode, виключення з sitemap, priority і change frequency.
- Таблиця 301 і 302 редіректів.
- Редактор robots.txt з підтримкою окремих сайтів у sMultisite.
- Генерація sitemap.xml і dashboard стану.
- Noindex правила для пагінації та вибраних `$_GET` параметрів.
- HTTP/HTTPS і WWW canonical redirect налаштування.
- Google Tag Manager і Google Analytics 4 IDs.
- Інтеграції з sLang, sArticles, sCommerce, sMultisite і sApi.
- PRO меташаблони для документів, товарів, категорій і статей.

## Runtime

sSeo використовує:

- service provider і plugin events Evolution CMS;
- evo-ui форми і таблиці для manager screens;
- Livewire module panels для перемикання вкладок;
- Laravel-style config, migrations, translations, routes і views;
- файлову систему для robots.txt і sitemap.xml;
- таблиці бази даних для SEO записів і редіректів.

## Важливі файли

- `config/sSeoSettings.php` - стандартні налаштування модуля.
- `config/settings/form.php` - preset форми конфігурації.
- `config/analytics/form.php` - preset форми аналітики.
- `config/redirects/table.php` - preset таблиці редіректів.
- `config/module/tabs.php` - конфігурація вкладок модуля.
- `src/Livewire/ModulePanel.php` - головна manager panel.
- `src/Livewire/RobotsEditor.php` - редактор robots.txt.
- `src/Livewire/MetaTemplatesEditor.php` - редактор PRO меташаблонів.
- `src/Tables/RedirectsTableData.php` - provider даних редіректів.
- `src/sSeo.php` - runtime реалізація facade.
- `plugins/sSeoPlugin.php` - frontend і manager Evolution events.
