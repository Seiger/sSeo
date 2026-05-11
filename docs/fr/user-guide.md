# Guide utilisateur

## Ou ouvrir sSeo

Ouvrez **Manager -> Tools -> sSeo**. Le module fonctionne dans le manager Evolution CMS et conserve ses onglets dans un seul workspace.

Les onglets principaux:

- **Dashboard** - statut sitemap et activite SEO recente.
- **Redirects** - gestion des redirections 301/302.
- **Robots** - editeur robots.txt.
- **Configuration** - analytics IDs, indexation, fonctionnalites, commerce et reglages serveur.
- **Meta Templates** - templates PRO si la surface PRO est disponible.

## Dashboard

Le dashboard affiche le fichier sitemap actuel, le nombre d'URL, la derniere date de generation et l'activite recente.

![Dashboard](../static/img/admin/dashboard.jpg)

Utilisez cet onglet apres une modification de contenu ou de configuration sitemap pour verifier que la generation reste saine.

## Redirects

Les redirections permettent de traiter les anciennes URL ou les URL manquantes sans modifier la configuration serveur.

Workflow typique:

1. Ouvrez **Redirects**.
2. Ajoutez l'ancienne URL sans le domaine.
3. Ajoutez la nouvelle URL ou une cible absolue.
4. Choisissez `301` pour une redirection permanente ou `302` pour une redirection temporaire.
5. Enregistrez et testez l'ancienne URL dans le navigateur.

![Redirects](../static/img/admin/redirects.jpg)

Quand sMultisite est active, les redirections peuvent etre associees a un site key. Les redirections globales peuvent encore etre partagees si la configuration le permet.

## Robots

L'onglet Robots edite le contenu robots.txt actif. Dans un projet multisite, sSeo peut utiliser des fichiers par site et un fallback vers le fichier racine.

![Robots](../static/img/admin/robots.jpg)

Utilisez robots.txt pour les regles crawler-level. Pour les decisions page-specific `index`, `noindex`, `follow` et `nofollow`, utilisez les champs SEO de la ressource.

## Analytics

Analytics est la premiere section dans l'onglet Configuration. Elle stocke les Google IDs utilises par la couche runtime injection.

- **Google Tag Manager** accepte des IDs separes par des virgules, par exemple `GTM-AAAAAAA`.
- **Google Analytics 4** accepte des IDs separes par des virgules, par exemple `G-XXXXXXXXXX`.

sSeo valide le format avant enregistrement. Laissez le champ vide si l'integration n'est pas utilisee.

## Configuration

La configuration est separee en sections.

![Configuration](../static/img/admin/configure.jpg)

### Indexing

- **Pagination parameter** marque les pages paginees qui ne doivent pas etre indexees. La valeur par defaut est `page`.
- **Noindex `$_GET`** stocke les query parameters separes par des virgules qui doivent produire noindex.

### Functionality

- **Meta tags mode** controle comment sSeo gere les meta tags deja presents dans les templates.
  - **Replace** remplace les tags correspondants par la sortie sSeo.
  - **Fill** ajoute seulement les tags manquants.
- **Enable redirects** active ou desactive la table des redirections.
- **Automatic sitemap generation** met a jour sitemap.xml apres les changements de contenu.

### Commerce

Les product attribute aliases exposent certains attributs sCommerce aux SEO templates.

### Server

- **Server type** affiche le reglage de protocole actif dans Evolution CMS.
- **WWW management** peut ignorer WWW, rediriger vers non-WWW ou rediriger vers WWW.

## Meta Templates

Meta Templates est une surface PRO. Elle definit les fallback title, description et keywords pour les documents et modules integres.

![Templates](../static/img/admin/templates.png)

Les templates peuvent utiliser des placeholders:

- `[*pagetitle*]`
- `[*longtitle*]`
- `[(site_name)]`

Utilisez les templates pour des valeurs par defaut coherentes, puis remplacez les pages individuelles avec les champs SEO de ressource.

## Champs SEO de ressource

sSeo ajoute des champs SEO aux ressources et aux editeurs de modules supportes.

Champs frequents:

- robots behavior;
- meta title;
- meta description;
- meta keywords;
- canonical URL;
- exclusion sitemap;
- sitemap priority;
- change frequency.

Si sLang est active, les donnees SEO peuvent etre stockees par langue. Sans sLang, sSeo utilise le base SEO record.

## Multisite

Quand sMultisite est active, sSeo utilise le `site_key` actif pour robots.txt, sitemap.xml, redirects et SEO records lorsque la runtime a besoin de separer les sites.

Verifiez chaque site apres une modification des canonical redirects, robots rules ou sitemap behavior.
