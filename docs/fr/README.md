# Documentation sSeo

sSeo est un module Evolution CMS pour gerer les regles SEO techniques, les metadonnees des ressources, les redirections, robots.txt, sitemap.xml, les identifiants analytics et les templates SEO depuis le manager.

L'interface actuelle du manager est construite avec **evo-ui** et **Livewire**. Le module s'ouvre comme un workspace compact avec des onglets pour le statut sitemap, les redirections, robots.txt, la configuration et les meta templates PRO. Les IDs analytics vivent dans l'onglet Configuration.

## Guides

- [Guide utilisateur](user-guide.md)
- [Guide developpeur](developer-guide.md)

## Fonctionnalites principales

- Champs SEO pour les ressources Evolution CMS et les modules supportes.
- Meta title, description, keywords, canonical URL, robots mode, exclusion sitemap, priority et change frequency.
- Table de redirections 301 et 302.
- Editeur robots.txt avec support par site pour sMultisite.
- Generation de sitemap.xml et dashboard de statut.
- Regles noindex pour la pagination et certains parametres `$_GET`.
- Reglages canonical redirect pour HTTP/HTTPS et WWW.
- Google Tag Manager et Google Analytics 4 IDs.
- Integrations avec sLang, sArticles, sCommerce, sMultisite et sApi.
- Meta templates PRO pour documents, produits, categories et articles.

## Runtime

sSeo utilise:

- service provider et plugin events Evolution CMS;
- formulaires et tables evo-ui pour les ecrans manager;
- Livewire module panels pour changer d'onglet;
- Laravel-style config, migrations, translations, routes et views;
- acces fichier pour robots.txt et sitemap.xml;
- tables de base de donnees pour SEO records et redirects.

## Fichiers importants

- `config/sSeoSettings.php` - reglages par defaut du module.
- `config/settings/form.php` - preset du formulaire de configuration.
- `config/analytics/form.php` - preset du formulaire analytics.
- `config/redirects/table.php` - preset de la table des redirections.
- `config/module/tabs.php` - configuration des onglets manager.
- `src/Livewire/ModulePanel.php` - panneau manager principal.
- `src/Livewire/RobotsEditor.php` - editeur robots.txt.
- `src/Livewire/MetaTemplatesEditor.php` - editeur des meta templates PRO.
- `src/Tables/RedirectsTableData.php` - provider des donnees de redirection.
- `src/sSeo.php` - implementation runtime facade.
- `plugins/sSeoPlugin.php` - events Evolution frontend et manager.
