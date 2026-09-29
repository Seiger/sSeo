---
title: Admin UI
sidebar_label: Admin UI
sidebar_position: 4
---

# Admin UI

## Dashboard
- Last **sitemap generation** info (from Event Log)
- Quick links to other tabs
![Dashboard](/img/admin/dashboard.jpg)

## Redirects
- Add/edit simple redirects: **Old URL** → **New URL** (301/302), per‑site (*sMultisite*).
- Sort by fields.
- Check for duplicates/empty files.
![Redirects](/img/admin/redirects.jpg)

## Meta Templates *(PRO)*
> Currently, the *(PRO)* version is only available if the website is developed by **[Seiger IT](https://seigerit.com/)**.

Define fallback templates for meta **Title / Description / Keywords** per language and entity:
- Documents (EVO Resources)
- sCommerce **Categories** and **Products**
- sArticles **Publications**

Use placeholders like `[*pagetitle*]`, `[*longtitle*]`, `[(site_name)]`.
![Templates](/img/admin/templates.png)

## Robots.txt
- Edit robots for root or per site key, with **writable** check + warnings.
![Robots](/img/admin/robots.jpg)

## Configure
Toggle functions: WWW control, redirection, sitemap creation, GET name for pagination, list of $_GET parameters prohibited from indexing, elements on the page in the interface, etc.
![Configure](/img/admin/configure.jpg)

### URL canonicalization
sSeo sends a 301 to the canonical URL (protocol, WWW, lowercase path, single slashes, friendly URL suffix) only for `GET` and `HEAD` requests. `POST`, `PUT`, `PATCH`, `DELETE` and other methods are never redirected, because a 301 turns them into a `GET` without a body.

Paths under `/api` and the sApi prefix (`SAPI_BASE_PATH`) are skipped. Add other endpoint prefixes with `redirect_skip_prefixes` in `core/custom/config/seiger/settings/sSeo.php`:

```php
"redirect_skip_prefixes" => ["mcp", "webhooks"],
```
