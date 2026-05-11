# User Guide

## Where To Open sSeo

Open **Manager -> Tools -> sSeo**. The module opens inside the Evolution CMS manager and keeps its own tabs in one workspace.

The main tabs are:

- **Dashboard** - sitemap status and recent SEO activity.
- **Redirects** - 301/302 redirect management.
- **Robots** - robots.txt editor.
- **Configuration** - analytics IDs, indexing, feature, commerce, and server settings.
- **Meta Templates** - PRO templates when the PRO surface is available.

## Dashboard

The dashboard shows the current sitemap file, URL count, last generation date, and recent activity.

![Dashboard](../static/img/admin/dashboard.jpg)

Use this tab after changing content or sitemap settings to confirm that sitemap generation is still healthy.

## Redirects

Redirects help resolve old or missing URLs without editing server configuration.

Typical workflow:

1. Open **Redirects**.
2. Add the old URL without the site domain.
3. Add the new URL or absolute target.
4. Choose the redirect type, usually `301` for permanent redirects or `302` for temporary redirects.
5. Save and test the old URL in a browser.

![Redirects](../static/img/admin/redirects.jpg)

When sMultisite is enabled, redirects can be scoped by site key. Global redirects can still be shared where the configuration allows it.

## Robots

The Robots tab edits the active robots.txt content. In a multisite project, sSeo can work with per-site files and fall back to the root file when needed.

![Robots](../static/img/admin/robots.jpg)

Use robots.txt for crawler-level rules only. Use resource SEO fields for page-specific `index`, `noindex`, `follow`, and `nofollow` decisions.

## Analytics

Analytics settings are the first section inside the Configuration tab. They store Google IDs used by the runtime injection layer.

- **Google Tag Manager** accepts comma-separated IDs such as `GTM-AAAAAAA`.
- **Google Analytics 4** accepts comma-separated IDs such as `G-XXXXXXXXXX`.

sSeo validates the ID format before saving. Leave a field empty when the site does not use that integration.

## Configuration

Configuration is split into sections.

![Configuration](../static/img/admin/configure.jpg)

### Indexing

- **Pagination parameter** marks paginated pages that should not be indexed. The default is `page`.
- **Noindex `$_GET`** stores comma-separated query parameters that should produce noindex behavior.

### Functionality

- **Meta tags mode** controls how sSeo handles meta tags already present in templates.
  - **Replace** overwrites matching tags with sSeo output.
  - **Fill** adds only missing tags.
- **Enable redirects** turns the redirect table on or off.
- **Automatic sitemap generation** updates sitemap.xml when content changes.

### Commerce

Product attribute aliases expose selected sCommerce product attributes to SEO templates.

### Server

- **Server type** shows the active Evolution CMS protocol setting.
- **WWW management** can ignore WWW, redirect to non-WWW, or redirect to WWW.

## Meta Templates

Meta Templates are a PRO surface. They define fallback title, description, and keyword patterns for documents and integrated modules.

![Templates](../static/img/admin/templates.png)

Templates can use placeholders such as:

- `[*pagetitle*]`
- `[*longtitle*]`
- `[(site_name)]`

Use templates for consistent defaults, then override individual pages with resource SEO fields when needed.

## Resource SEO Fields

sSeo adds SEO fields to resources and supported module editors.

Common fields include:

- robots behavior;
- meta title;
- meta description;
- meta keywords;
- canonical URL;
- sitemap exclusion;
- sitemap priority;
- change frequency.

If sLang is enabled, SEO data can be stored per language. Without sLang, sSeo uses the base SEO record.

## Multisite Notes

When sMultisite is enabled, sSeo uses the active `site_key` for robots.txt, sitemap.xml, redirects, and SEO records where the runtime requires site separation.

Check each site after changing canonical redirects, robots rules, or sitemap behavior.
