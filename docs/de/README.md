# sSeo Dokumentation

sSeo ist ein Evolution CMS Modul zur Verwaltung technischer SEO-Regeln, Ressourcen-Metadaten, Weiterleitungen, robots.txt, sitemap.xml, Analytics-IDs und SEO-Templates im Manager.

Die aktuelle Manager-Oberflaeche basiert auf **evo-ui** und **Livewire**. Das Modul arbeitet als kompakter Workspace mit Tabs fuer Sitemap-Status, Weiterleitungen, robots.txt, Konfiguration und PRO Meta Templates. Analytics-IDs liegen im Konfiguration-Tab.

## Handbuecher

- [Benutzerhandbuch](user-guide.md)
- [Entwicklerhandbuch](developer-guide.md)

## Hauptfunktionen

- SEO-Felder fuer Evolution CMS Ressourcen und unterstuetzte Module.
- Meta title, description, keywords, canonical URL, robots mode, Sitemap-Ausschluss, priority und change frequency.
- Tabelle fuer 301- und 302-Weiterleitungen.
- robots.txt Editor mit sMultisite Unterstuetzung.
- sitemap.xml Generierung und Status-Dashboard.
- Noindex-Regeln fuer Paginierung und ausgewaehlte `$_GET` Parameter.
- HTTP/HTTPS und WWW canonical redirect Einstellungen.
- Google Tag Manager und Google Analytics 4 IDs.
- Integrationen mit sLang, sArticles, sCommerce, sMultisite und sApi.
- PRO Meta Templates fuer Dokumente, Produkte, Kategorien und Artikel.

## Runtime

sSeo verwendet:

- Evolution CMS service provider und plugin events;
- evo-ui forms und tables fuer Manager-Oberflaechen;
- Livewire module panels fuer Tab-Wechsel;
- Laravel-style config, migrations, translations, routes und views;
- Dateisystemzugriff fuer robots.txt und sitemap.xml;
- Datenbanktabellen fuer SEO records und redirects.

## Wichtige Dateien

- `config/sSeoSettings.php` - Standard-Einstellungen des Moduls.
- `config/settings/form.php` - Preset fuer die Konfigurationsform.
- `config/analytics/form.php` - Preset fuer die Analytics-Form.
- `config/redirects/table.php` - Preset fuer die Weiterleitungstabelle.
- `config/module/tabs.php` - Manager-Tab-Konfiguration.
- `src/Livewire/ModulePanel.php` - Hauptpanel im Manager.
- `src/Livewire/RobotsEditor.php` - robots.txt Editor.
- `src/Livewire/MetaTemplatesEditor.php` - PRO Meta Templates Editor.
- `src/Tables/RedirectsTableData.php` - Datenprovider fuer Weiterleitungen.
- `src/sSeo.php` - Runtime-Facade-Implementierung.
- `plugins/sSeoPlugin.php` - Frontend- und Manager-Events von Evolution.
