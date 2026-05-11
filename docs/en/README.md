# sSeo Documentation

sSeo is an Evolution CMS module for managing technical SEO rules, resource meta data, redirects, robots.txt, sitemap.xml, analytics IDs, and SEO templates from the manager.

The current manager interface is built with **evo-ui** and **Livewire**. The module opens as a compact manager workspace with tabs for dashboard status, redirects, robots.txt, configuration, and PRO meta templates. Analytics IDs live inside the Configuration tab.

## Guides

- [User Guide](user-guide.md)
- [Developer Guide](developer-guide.md)

## Main Capabilities

- SEO fields for Evolution resources and supported modules.
- Meta title, description, keywords, canonical URL, robots mode, sitemap exclusion, priority, and change frequency.
- Redirect table for 301 and 302 redirects.
- robots.txt editor with per-site support for sMultisite.
- sitemap.xml generation and status dashboard.
- Noindex rules for pagination and selected `$_GET` parameters.
- HTTP/HTTPS and WWW canonical redirect settings.
- Google Tag Manager and Google Analytics 4 IDs.
- Optional integrations with sLang, sArticles, sCommerce, sMultisite, and sApi.
- PRO meta templates for documents, products, categories, and articles.

## Runtime

sSeo uses:

- Evolution CMS service provider and plugin events.
- evo-ui forms and tables for manager screens.
- Livewire module panels for tab switching.
- Laravel-style config, migrations, translations, routes, and views.
- Filesystem access for robots.txt and sitemap.xml.
- Database tables for SEO records and redirects.

## Important Files

- `config/sSeoSettings.php` - default module settings.
- `config/settings/form.php` - configuration form preset.
- `config/analytics/form.php` - analytics form preset.
- `config/redirects/table.php` - redirects table preset.
- `config/module/tabs.php` - manager tab configuration.
- `src/Livewire/ModulePanel.php` - main manager panel.
- `src/Livewire/RobotsEditor.php` - robots.txt editor.
- `src/Livewire/MetaTemplatesEditor.php` - PRO meta templates editor.
- `src/Tables/RedirectsTableData.php` - redirects data provider.
- `src/sSeo.php` - runtime SEO facade implementation.
- `plugins/sSeoPlugin.php` - frontend and manager Evolution events.
