# Revue SEO & performance : lot « Fondations »

Date : 2026-10-15. Méthode : `php artisan serve --port=8124`, HTML récupéré avec curl (fr/nl/en/de), Lighthouse 13.5.0 lancé avec Edge headless sur `/fr` et `/nl`, en mobile et en desktop.

## Verdict : À CORRIGER

Les fondations sont saines. Aucun défaut BLOQUANT : title et description uniques dans les 4 langues, un seul h1, canonical et hreflang réciproques avec x-default, vrai statut 404, noindex sur toutes les pages privées ou pauvres, polices auto-hébergées en `swap` avec preload, CLS = 0. Restent 4 défauts MAJEURS, dont deux empêchent d'atteindre l'objectif Lighthouse mobile ≥ 95, ou faussent les URL canoniques en production.

## Scores Lighthouse

| Page | Perf | Access. | Bonnes prat. | SEO | FCP | LCP | TBT | CLS |
|---|---|---|---|---|---|---|---|---|
| /fr mobile | **88** | 100 | 100 | 100 | 2,4 s | 3,3 s | 100 ms | 0 |
| /fr desktop | 100 | 100 | 100 | 100 | 0,5 s | 0,7 s | 0 ms | 0 |
| /nl mobile | **92** | 100 | 100 | 100 | 2,1 s | 3,1 s | 50 ms | 0 |
| /nl desktop | 100 | 100 | 100 | 100 | 0,5 s | 0,7 s | 0 ms | 0 |

Opportunités relevées par Lighthouse :
- **Render-blocking** : le CSS fait 74 Ko non compressé, soit 750–900 ms de retard estimé sur mobile. L'élément LCP est le paragraphe `hero_subtitle`, et son délai de rendu vient du CSS.
- **Compression** : aucune (« No compression applied »). Gain estimé : 69 Ko sur le document, et CSS 74 → 14 Ko, JS 71 → 23 Ko en gzip.
- **Cache** : aucune durée de cache efficace sur les ressources statiques (gain estimé : 266 Ko).
- **Image** : `logo-wordmark.webp` est en 400×104 mais affiché en 215×56 (8 Ko).
- **JS** : environ 36 Ko inutilisés (Alpine), acceptable. Le « JS non minifié » de 4 Ko vient du logger de Laravel Boost, présent seulement en local (dépendance dev) : ce n'est pas un défaut.

Note : `artisan serve` ne compresse rien et n'envoie aucun en-tête de cache. Le serveur de production (o2switch, Apache) appliquera `public/.htaccess`, qui ne prévoit rien non plus pour l'instant (voir le défaut 1).

## Défauts

### MAJEUR

**1. Pas de compression ni de cache long pour les ressources statiques en production**
- Fichier : `public/.htaccess` (fichier Laravel par défaut, 740 octets).
- Problème : rien n'est prévu pour `mod_deflate`/`mod_brotli` ni pour `mod_expires`/`Cache-Control`. Sur o2switch, le CSS bloquant partira donc à 74 Ko au lieu de 14 Ko, et les fichiers hashés de `/build/assets`, les polices et les images seront retéléchargés à chaque visite. C'est la cause principale du score Performance mobile à 88/92, sous l'objectif de 95.
- Correction attendue : ajouter dans `public/.htaccess` :
  - `<IfModule mod_deflate.c> AddOutputFilterByType DEFLATE text/html text/css application/javascript application/json image/svg+xml application/manifest+json </IfModule>` (et brotli si le module est disponible) ;
  - pour `^build/assets/` : `Cache-Control: public, max-age=31536000, immutable` (via `<If "%{REQUEST_URI} =~ m#^/build/assets/#">` + `Header set`) ;
  - pour `fonts/*.woff2`, `images/*`, les favicons et le manifest : `max-age` d'au moins 30 jours (ces fichiers ne sont pas hashés : changer leur nom quand ils changent) ;
  - `AddType application/manifest+json .webmanifest` et `AddType font/woff2 .woff2`.

  Relancer ensuite Lighthouse mobile derrière Apache, ou tout serveur qui compresse, et viser ≥ 95.

**2. Canonical, hreflang et og:url construits à partir de l'en-tête Host de la requête**
- Fichiers : `resources/views/components/layouts/app.blade.php:24-27, 41-45, 52` (via `localized_alternates()` dans `app/helpers.php:37-55`, qui appelle `route()`).
- Problème vérifié :
  - `curl -H "Host: evil.example" /fr` renvoie `<link rel="canonical" href="http://evil.example/fr">`, et toutes les balises hreflang, og:url et og:image suivent.
  - `/index.php/fr` répond 200 avec un canonical qui pointe sur lui-même (`…/index.php/fr`) : contenu dupliqué.
  - Le même mécanisme produira des canonicals en `http://` derrière un proxy, ou en `www.` si les deux hôtes répondent.
  - `APP_URL` n'est jamais utilisé.
- Correction attendue :
  - dans `AppServiceProvider::boot()`, hors environnement local : `URL::forceRootUrl(config('app.url'))` et `URL::forceScheme('https')` ;
  - en production, `APP_URL=https://doggys.be` ;
  - dans `.htaccess` : redirection 301 de `www.` vers l'apex et de http vers https, plus une règle 301 `^index\.php/(.*)$ → /$1`.
- Ajouter un test de fonctionnalité qui vérifie qu'un Host arbitraire ne change pas le canonical.

**3. JSON-LD absent sur l'accueil (Organization + WebSite)**
- Fichier : `resources/views/home.blade.php`, aucun slot `head` ; 0 occurrence de `application/ld+json` dans les 4 langues.
- Problème : la grille (GARDE-FOUS §3) exige Organization et WebSite (avec SearchAction) sur l'accueil.
- Correction attendue : dans `<x-slot:head>` de `home.blade.php`, un `<script type="application/ld+json" nonce="{{ Vite::cspNonce() }}">` généré avec `json_encode(..., JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)`, contenant :
  - `Organization` : `name` Doggys, `url` = accueil de la langue, `logo` absolu (≥ 112 px, par ex. `icon-512.png`), `email` contact@doggys.be ;
  - `WebSite` : `name`, `url`, `inLanguage` (fr-BE/nl-BE/en/de-BE), `publisher` → `@id` de l'Organization.

  Ajouter `potentialAction` SearchAction (`target` = `localized_route('search')` + `?location={search_term_string}`, `query-input` = `required name=search_term_string`) dès que la recherche réelle existe : aujourd'hui, `/fr/recherche` est un placeholder en noindex.

**4. Title et h1 de l'accueil faibles en mots-clés**
- Fichiers : `lang/{fr,nl,en,de}/home.php:4` (meta_title) et `:8-9` (hero_title + highlight, rendus dans `home.blade.php:14`).
- Problème : le h1 (« Tout pour le bonheur de votre chien, près de chez vous », « Alles voor het geluk van uw hond, dicht bij huis »…) ne contient aucune requête cible : ni « professionnels canins » / « hondenprofessionals » / « Hundeprofis », ni « Belgique ». Le title s'appuie sur « pros », une abréviation que personne ne tape, et pas sur « annuaire » / « gids » / « Verzeichnis ». La page la plus forte du site ne vise donc que la requête de marque.
- Correction attendue (≤ 60 caractères, texte naturel et propre à chaque langue) :
  - meta_title, par exemple :
    - FR « Doggys · Annuaire des professionnels canins en Belgique » (55)
    - NL « Doggys · Alle hondenprofessionals in België » (44)
    - EN « Doggys · Directory of dog professionals in Belgium » (51)
    - DE « Doggys · Verzeichnis der Hundeprofis in Belgien » (48)
  - h1 : garder le ton chaleureux mais y placer le mot-clé, par exemple FR « Les meilleurs professionnels canins, près de chez vous » ; NL « De beste hondenprofessionals, dicht bij huis » ; EN « The best dog professionals, close to home » ; DE « Die besten Hundeprofis, ganz in Ihrer Nähe ».
  - Le slogan émotionnel peut passer dans l'eyebrow ou le sous-titre.

### MINEUR

**5. Liens de catégories de l'accueil vers des URL à paramètres en noindex**
- Fichier : `resources/views/home.blade.php:74` et `:119`.
- Problème : les 18 + 12 liens internes les plus importants de l'accueil pointent vers `/fr/recherche?category=toiletteur-salon` (paramètre, page placeholder en noindex). Le PageRank interne part vers des URL non indexables. La grille demande « pas de paramètres pour les pages indexables ».
- Correction attendue : à corriger au plus tard avec le lot recherche. Les faire pointer vers les futures pages propres de catégorie, par exemple `localized_route('category', $slug)` → `/fr/toiletteur-salon`, `/nl/hondentrimsalon` (avec des slugs traduits par langue).

**6. Image du hero en `fetchpriority="high"` alors qu'elle est masquée sur mobile, et agrandie au-delà de sa taille sur desktop**
- Fichier : `resources/views/home.blade.php:46`.
- Problème :
  - Le conteneur est `hidden lg:block`, mais l'image est quand même téléchargée en priorité haute sur mobile, où elle concurrence le CSS et les polices du chemin critique (le LCP mobile est un texte).
  - Sur desktop, l'image fait 240 px mais s'affiche à 288 px (`size-72`) : rendu flou, a fortiori en retina.
- Correction attendue :
  - retirer `fetchpriority="high"`, ou passer par `<picture><source media="(min-width:1024px)" …>` avec un `src` transparent sur mobile ;
  - fournir une variante de 576 px avec `srcset="logo-mark-240.webp 240w, logo-mark-576.webp 576w" sizes="288px"`.

**7. Logo wordmark surdimensionné**
- Fichier : `resources/views/components/site/header.blade.php:16`.
- Problème : le fichier est en 400×104, alors qu'il s'affiche en 123×32 (mobile) ou 154×40 (desktop). Il est téléchargé sur toutes les pages (8 Ko perdus selon Lighthouse).
- Correction attendue : générer des variantes 160 et 320 px et ajouter `srcset` / `sizes="(min-width:640px) 154px, 123px"`.

**8. Pas de `og:image:alt`, et `twitter:card` sans image dédiée**
- Fichier : `app.blade.php:53-60`.
- Correction attendue : ajouter `<meta property="og:image:alt" content="{{ __('ui.og_image_alt') }}">` (traduit) et `<meta name="twitter:image" …>`, identique à og:image, pour plus de robustesse.

**9. Marque en double dans certains titres**
- Exemple : `/en/register` → « Join Doggys | Doggys ».
- Fichier : `lang/en/auth.php` (register_title), à vérifier aussi dans les autres langues.
- Les pages sont en noindex, donc l'impact est faible (onglet et partage seulement). Correction : utiliser `full-title` sur ces pages, ou reformuler le title sans la marque.

**10. Le manifest n'est qu'en français**
- Fichier : `public/manifest.webmanifest`.
- Problème : `lang: "fr"` et une description en français, servis aux 4 langues.
- Correction attendue : ajouter `"id": "/"`. Si un manifest par langue est souhaité, un `/{locale}/manifest.webmanifest` traduit, avec `start_url: "/{locale}"` ; sinon, une description neutre ou bilingue. Faible priorité.

**11. Terminologie néerlandaise (Flandre)**
- Fichiers : `database/seeders/CategorySeeder.php:78` (« Erkende fokker ») et `lang/nl/home.php:47` (« fokkers »).
- Problème : le résumé de la même catégorie dit « Kwekers ». En Flandre, le terme réglementaire et le plus recherché est « erkende (honden)kweker ». Ailleurs, les termes NL sont bons (« Hondentrimsalon », « Hondenpension », « Hondenuitlaatdienst »).
- Correction attendue : harmoniser sur « Erkende kweker » / « kwekers ».

## Points vérifiés et conformes

- Redirection de `/` : **302** vers `/{locale}` selon Accept-Language, avec `Vary: Accept-Language` et `/fr` par défaut, donc par défaut pour Googlebot. Correct pour une négociation de langue.
- Titles de l'accueil uniques : fr 56, nl 46, en 46, de 52 caractères. Descriptions uniques : 148 / 141 / 118 / 143 caractères (≤ 155).
- `<html lang>` est correct dans les 4 langues, y compris sur une 404 hors préfixe.
- Un seul h1 sur l'accueil, les pages auth, le placeholder et la 404. La hiérarchie h1 → h2 → h3 de l'accueil est propre.
- Canonical absolu, query string retirée (`/fr?utm=1` → canonical `/fr`). Hreflang fr/nl/en/de + x-default (→ /fr) réciproques sur les 4 accueils.
- URL traduites cohérentes, en ASCII, sans conflit entre les langues (`/fr/connexion`, `/nl/inloggen`, `/en/login`, `/de/anmelden`). Un segment d'une autre langue renvoie 404 (`/fr/inloggen`), comme `/FR`.
- Vrai statut **404** pour `/fr/nexistepas`, `/nl/bestaatniet` et `/nexistepas`, avec une page traduite en noindex.
- `noindex, follow` sans canonical sur : auth (login, register, forgot, reset, verify, confirm), compte, chiens, placeholders, erreurs 4xx/5xx. Les pages privées redirigent en 302 vers la connexion.
- og:image en 1200×630 (61 Ko), og:locale + alternates, `twitter:card summary_large_image`.
- Polices auto-hébergées en woff2, `font-display: swap`, découpées par `unicode-range`, preload des 2 sous-ensembles latin avec `crossorigin`.
- Images en WebP avec `width`/`height` explicites : CLS = 0.
- JS en `type=module` (non bloquant), aucun Leaflet chargé sur l'accueil.
- Favicons ico/32/180, icônes 192/512 et maskable présentes, theme-color défini.
- Accessibilité, bonnes pratiques et SEO à 100 dans les 4 tirs Lighthouse.

## À prévoir (lots ultérieurs, pas des défauts)

- `sitemap.xml` (index + un par langue) et ligne `Sitemap:` dans `robots.txt` (actuellement `Disallow:` vide, et `/sitemap.xml` répond 404).
- SearchAction du JSON-LD WebSite une fois la recherche livrée ; BreadcrumbList sur les pages internes ; LocalBusiness/VeterinaryCare… sur les fiches ; Article sur les guides.
- Pages catégorie × commune avec **slugs traduits** par langue (le cahier des charges cite `/fr/toiletteur-salon/liege` ; prévoir `/nl/hondentrimsalon/luik`, `/de/hundesalon/luettich`…). Ajouter sur l'accueil un bloc « Recherches populaires » vers ces pages : aujourd'hui, l'accueil ne contient aucun nom de ville, donc rien pour « toiletteur chien Liège » ou « hondentrimsalon Gent ».
- Le sélecteur de langue devra conserver les paramètres et les slugs traduits sur la recherche et les fiches (`alternates` passés au layout).
- Maillage interne entre guides et catégories.
- Optionnel : remplacer les 72 SVG inline de l'accueil (28 Ko de HTML) par un sprite `<use>`, et limiter le `@source` de la pagination Laravel, qui injecte des classes `dark:`/`gray-*` inutilisées dans le CSS.
