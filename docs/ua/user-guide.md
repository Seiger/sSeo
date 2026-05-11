# Гайд користувача

## Де відкрити sSeo

Відкрийте **Менеджер -> Інструменти -> sSeo**. Модуль працює всередині Evolution CMS manager і тримає всі свої вкладки в одному workspace.

Основні вкладки:

- **Інформаційна панель** - стан sitemap і остання SEO активність.
- **Редіректи** - керування 301/302 редіректами.
- **Роботс** - редактор robots.txt.
- **Конфігурація** - аналітика, індексація, функціональність, commerce і server settings.
- **Меташаблони** - PRO шаблони, якщо PRO surface доступний.

## Інформаційна панель

Dashboard показує поточний sitemap файл, кількість URL, дату останньої генерації та останню активність.

![Інформаційна панель](../static/img/admin/dashboard.jpg)

Перевіряйте цю вкладку після зміни контенту або налаштувань sitemap, щоб бачити, що генерація працює стабільно.

## Редіректи

Редіректи допомагають перенаправити старі або відсутні URL без редагування server config.

Типовий workflow:

1. Відкрийте **Редіректи**.
2. Додайте старий URL без домену сайту.
3. Додайте новий URL або абсолютний target.
4. Виберіть тип редіректу: `301` для постійного або `302` для тимчасового.
5. Збережіть і перевірте старий URL у браузері.

![Редіректи](../static/img/admin/redirects.jpg)

Якщо увімкнений sMultisite, редіректи можуть бути прив'язані до site key. Глобальні редіректи можна використовувати там, де це дозволяє конфігурація.

## Роботс

Вкладка Роботс редагує активний robots.txt. У multisite проєкті sSeo може працювати з окремими файлами сайтів і fallback до root file.

![Роботс](../static/img/admin/robots.jpg)

Використовуйте robots.txt для crawler-level правил. Для page-specific `index`, `noindex`, `follow`, `nofollow` використовуйте SEO поля ресурсу.

## Аналітика

Аналітика є першою секцією всередині вкладки Конфігурація. Вона зберігає Google IDs для runtime injection.

- **Google Tag Manager** приймає IDs через кому, наприклад `GTM-AAAAAAA`.
- **Google Analytics 4** приймає IDs через кому, наприклад `G-XXXXXXXXXX`.

sSeo перевіряє формат ID перед збереженням. Якщо інтеграція не використовується, залиште поле порожнім.

## Конфігурація

Конфігурація поділена на секції.

![Конфігурація](../static/img/admin/configure.jpg)

### Індексація

- **Сторінки пагінації** задає параметр, який не має індексуватися. Типово `page`.
- **Не індексувати `$_GET`** містить список query parameters через кому, які мають давати noindex.

### Функціональність

- **Режим метатегів** визначає, як sSeo працює з meta tags, які вже є у шаблоні.
  - **Замінити** перезаписує відповідні tags через sSeo output.
  - **Заповнити** додає лише відсутні tags.
- **Увімкнути редіректи** вмикає або вимикає redirect table.
- **Автоматичне генерування sitemap.xml** оновлює sitemap після зміни контенту.

### Commerce

Ключі атрибутів товару відкривають вибрані sCommerce attributes для SEO templates.

### Сервер

- **Тип сервера** показує активне protocol setting Evolution CMS.
- **Керування WWW** може ігнорувати WWW, редіректити на non-WWW або редіректити на WWW.

## Меташаблони

Meta Templates - це PRO surface. Вони задають fallback title, description і keywords для documents та інтегрованих модулів.

![Меташаблони](../static/img/admin/templates.png)

Шаблони можуть використовувати placeholders:

- `[*pagetitle*]`
- `[*longtitle*]`
- `[(site_name)]`

Використовуйте templates для стабільних defaults, а окремі сторінки перевизначайте через SEO поля ресурсу.

## SEO поля ресурсу

sSeo додає SEO поля до ресурсів і підтримуваних module editors.

Типові поля:

- robots behavior;
- meta title;
- meta description;
- meta keywords;
- canonical URL;
- виключення з sitemap;
- sitemap priority;
- change frequency.

Якщо увімкнений sLang, SEO data може зберігатися по мовах. Без sLang sSeo використовує базовий SEO запис.

## Multisite

Коли увімкнений sMultisite, sSeo використовує активний `site_key` для robots.txt, sitemap.xml, redirects і SEO records там, де runtime потребує розділення сайтів.

Після зміни canonical redirects, robots rules або sitemap behavior перевіряйте кожен сайт окремо.
