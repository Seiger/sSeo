# Benutzerhandbuch

## Wo sSeo geoeffnet wird

Oeffne **Manager -> Tools -> sSeo**. Das Modul laeuft innerhalb des Evolution CMS Managers und behaelt seine Tabs in einem Workspace.

Die Haupt-Tabs:

- **Dashboard** - Sitemap-Status und letzte SEO-Aktivitaet.
- **Redirects** - Verwaltung von 301/302 Weiterleitungen.
- **Robots** - robots.txt Editor.
- **Analytics** - Google Tag Manager und Google Analytics 4 IDs.
- **Configuration** - Indexierung, Funktionen, Commerce und Servereinstellungen.
- **Meta Templates** - PRO Templates, wenn die PRO-Oberflaeche verfuegbar ist.

## Dashboard

Das Dashboard zeigt die aktuelle Sitemap-Datei, URL-Anzahl, das letzte Generierungsdatum und letzte Aktivitaeten.

![Dashboard](../static/img/admin/dashboard.jpg)

Pruefe diesen Tab nach Aenderungen an Content oder Sitemap-Einstellungen, um sicherzustellen, dass die Generierung stabil bleibt.

## Redirects

Weiterleitungen helfen, alte oder fehlende URLs ohne Server-Konfiguration umzuleiten.

Typischer Ablauf:

1. Oeffne **Redirects**.
2. Fuege die alte URL ohne Domain hinzu.
3. Fuege die neue URL oder ein absolutes Ziel hinzu.
4. Waehle `301` fuer dauerhaft oder `302` fuer temporaer.
5. Speichere und teste die alte URL im Browser.

![Redirects](../static/img/admin/redirects.jpg)

Wenn sMultisite aktiviert ist, koennen Redirects an einen site key gebunden werden. Globale Redirects koennen dort geteilt werden, wo die Konfiguration es erlaubt.

## Robots

Der Robots-Tab bearbeitet den aktiven robots.txt Inhalt. In Multisite-Projekten kann sSeo mit Dateien pro Site und einem Fallback zur Root-Datei arbeiten.

![Robots](../static/img/admin/robots.jpg)

Nutze robots.txt fuer crawler-level Regeln. Fuer page-specific `index`, `noindex`, `follow` und `nofollow` nutze die SEO-Felder der Ressource.

## Analytics

Der Analytics-Tab speichert Google IDs fuer die Runtime-Injection.

- **Google Tag Manager** akzeptiert IDs mit Komma, z. B. `GTM-AAAAAAA`.
- **Google Analytics 4** akzeptiert IDs mit Komma, z. B. `G-XXXXXXXXXX`.

sSeo validiert das ID-Format vor dem Speichern. Lasse das Feld leer, wenn die Integration nicht verwendet wird.

## Configuration

Die Konfiguration ist in Sektionen gegliedert.

![Configuration](../static/img/admin/configure.jpg)

### Indexing

- **Pagination parameter** markiert paginierte Seiten, die nicht indexiert werden sollen. Standard ist `page`.
- **Noindex `$_GET`** speichert Query-Parameter mit Komma, die noindex ausloesen sollen.

### Functionality

- **Meta tags mode** steuert, wie sSeo mit bereits vorhandenen Meta Tags im Template arbeitet.
  - **Replace** ersetzt passende Tags durch sSeo output.
  - **Fill** fuegt nur fehlende Tags hinzu.
- **Enable redirects** aktiviert oder deaktiviert die Redirect-Tabelle.
- **Automatic sitemap generation** aktualisiert sitemap.xml bei Content-Aenderungen.

### Commerce

Product attribute aliases stellen ausgewaehlte sCommerce Attribute fuer SEO Templates bereit.

### Server

- **Server type** zeigt das aktive Protocol Setting von Evolution CMS.
- **WWW management** kann WWW ignorieren, auf non-WWW oder auf WWW weiterleiten.

## Meta Templates

Meta Templates sind eine PRO-Oberflaeche. Sie definieren fallback title, description und keywords fuer Dokumente und integrierte Module.

![Templates](../static/img/admin/templates.png)

Templates koennen Platzhalter nutzen:

- `[*pagetitle*]`
- `[*longtitle*]`
- `[(site_name)]`

Nutze Templates fuer konsistente Defaults und ueberschreibe einzelne Seiten ueber Ressourcen-SEO-Felder.

## Ressourcen-SEO-Felder

sSeo fuegt SEO-Felder zu Ressourcen und unterstuetzten Modul-Editoren hinzu.

Typische Felder:

- robots behavior;
- meta title;
- meta description;
- meta keywords;
- canonical URL;
- Sitemap-Ausschluss;
- sitemap priority;
- change frequency.

Wenn sLang aktiviert ist, koennen SEO-Daten pro Sprache gespeichert werden. Ohne sLang nutzt sSeo den base SEO record.

## Multisite

Wenn sMultisite aktiviert ist, nutzt sSeo den aktiven `site_key` fuer robots.txt, sitemap.xml, redirects und SEO records, wenn die Runtime Site-Trennung braucht.

Pruefe jede Site separat nach Aenderungen an canonical redirects, robots rules oder sitemap behavior.
