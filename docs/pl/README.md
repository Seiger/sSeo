# Dokumentacja sSeo

sSeo to modul Evolution CMS do zarzadzania technicznym SEO, metadanymi zasobow, przekierowaniami, robots.txt, sitemap.xml, identyfikatorami analityki i szablonami SEO z poziomu managera.

Aktualny interfejs managera jest zbudowany na **evo-ui** i **Livewire**. Modul dziala jako kompaktowy workspace z zakladkami dla statusu sitemap, przekierowan, robots.txt, konfiguracji i szablonow meta PRO. Identyfikatory analityki znajduja sie w zakladce Konfiguracja.

## Przewodniki

- [Przewodnik uzytkownika](user-guide.md)
- [Przewodnik dewelopera](developer-guide.md)

## Glowne mozliwosci

- Pola SEO dla zasobow Evolution CMS i wspieranych modulow.
- Meta title, description, keywords, canonical URL, robots mode, wykluczenie z sitemap, priority i change frequency.
- Tabela przekierowan 301 i 302.
- Edytor robots.txt z obsluga oddzielnych stron w sMultisite.
- Generowanie sitemap.xml i dashboard statusu.
- Reguly noindex dla paginacji i wybranych parametrow `$_GET`.
- Ustawienia canonical redirect dla HTTP/HTTPS i WWW.
- Google Tag Manager i Google Analytics 4 IDs.
- Integracje z sLang, sArticles, sCommerce, sMultisite i sApi.
- Szablony meta PRO dla dokumentow, produktow, kategorii i artykulow.

## Runtime

sSeo uzywa:

- service providera i plugin events Evolution CMS;
- formularzy i tabel evo-ui dla ekranow managera;
- Livewire module panels dla przelaczania zakladek;
- Laravel-style config, migrations, translations, routes i views;
- systemu plikow dla robots.txt i sitemap.xml;
- tabel bazy danych dla rekordow SEO i przekierowan.

## Wazne pliki

- `config/sSeoSettings.php` - domyslne ustawienia modulu.
- `config/settings/form.php` - preset formularza konfiguracji.
- `config/analytics/form.php` - preset formularza analityki.
- `config/redirects/table.php` - preset tabeli przekierowan.
- `config/module/tabs.php` - konfiguracja zakladek modulu.
- `src/Livewire/ModulePanel.php` - glowny panel managera.
- `src/Livewire/RobotsEditor.php` - edytor robots.txt.
- `src/Livewire/MetaTemplatesEditor.php` - edytor szablonow meta PRO.
- `src/Tables/RedirectsTableData.php` - provider danych przekierowan.
- `src/sSeo.php` - implementacja runtime facade.
- `plugins/sSeoPlugin.php` - frontend i manager Evolution events.
