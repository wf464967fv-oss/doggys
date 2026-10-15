# Tour 4 - Garde-fou SEO & Performance

**Verdict : À CORRIGER** (1 bloquant, 3 majeurs, 3 mineurs). Serveur de test : copie de la base, port 8170, arrêté par PID.

## Scores Lighthouse mobile (Edge headless, `artisan serve`)
| Page | Perf | A11y | BP | SEO |
|---|---|---|---|---|
| /fr | 90 | 100 | 100 | 100 |
| /fr/pro/furfection-essen (2 avis créés) | 85 | 100 | 96 | 100 |
| /fr/confidentialite | 94 | 100 | 100 | 100 |

Les audits « cache », « latence du document » et la compression gzip dépendent d'Apache (`Cache-Control`, `mod_deflate`) :
ce serveur de test ne les reproduit pas, ils ne sont donc pas comptés comme défauts. À revérifier en préproduction.

## Ce qui est conforme
- 4 langues : title (16-41 car.) et description (92-151 car.) uniques, un seul h1, JSON-LD BreadcrumbList valide, indexables, sur `reviews.policy` et sur les 4 pages légales.
- Noindex présent : hors-ligne, recherche, connexion, inscription, mot de passe oublié, pages 2+ des catégories, `?page=2` d'une fiche (sans canonical).
- Pages protégées (compte, espace pro, réclamation, ajout d'entreprise, /admin) : 302 vers la connexion pour un visiteur. Les vues connectées portent `noindex`, y compris `admin-layout`.
- JSON-LD fiche : LocalBusiness + AggregateRating (4,5 / 2 avis, bestRating 5), uniquement si `reviews_count > 0`, note visible dans la page. Pas de Review, donc pas de risque de balisage auto-promotionnel.
- Auto-évaluation bloquée : `Review::blockReason` renvoie `own_listing` si le propriétaire note sa propre fiche (`app/Models/Review.php:41`).
- Sitemap : index + 4 sitemaps de 1943 URL. Échantillon de 30 URL tirées au hasard : 30 × 200, aucune en noindex.
- Mini-crawl FR depuis /fr : 300 URL, 300 × 200, 0 lien cassé, 0 redirection. Les seuls liens vers des noindex sont la navigation (recherche, connexion, inscription) et la pagination, qui est en `noindex, follow`.

## BLOQUANT
1. **Canonical et hreflang pollués sur `Route::view`** (`reviews.policy`, 4 pages légales, hors-ligne). Exemple : `/fr/avis-verifies` a pour canonical `https://…/fr/avis-verifies?view=pages.reviews-policy&status=200`, et les hreflang et le sélecteur de langue ont le même défaut. Google verrait des URL à paramètres, les hreflang ne seraient plus réciproques avec les URL propres, et le canonical ne correspondrait pas au sitemap.
   Cause : `app/helpers.php:51` passe `$route->parameters()` à `route()`, et ce tableau contient les defaults `view`, `data` et `status` d'une `Route::view`.
   Correction : `Arr::only($route->parameters(), $route->parameterNames())`, ou `originalParameters()` filtré. Ajouter un test de non-régression sur le canonical des pages légales.

## MAJEUR
2. **`reviews.policy` et les 4 pages légales sont absentes des 4 sitemaps** (`routes/web.php:68,87-90`, le générateur de sitemap ne les liste pas). Ajouter ces 5 routes × 4 langues, une fois le défaut 1 corrigé.
3. **`/fr/contact` (4 langues) est indexable**, alors que l'objectif est le noindex. Titre générique, aucun contenu propre. `resources/views/pages/contact.blade.php:1` : ajouter `noindex`. Le lien dans le pied de page peut rester.
4. **Performance < 95 sur l'accueil (90) et surtout sur la fiche (85).** Sur la fiche, le LCP (3,8 s) est une tuile OpenStreetMap Leaflet visible dès l'ouverture sur mobile : `image-size-responsive`, `lcp-discovery` (ressource non découvrable dans le HTML), et le score Best Practices tombe à 96. Corriger : carte différée (clic ou IntersectionObserver) ou placée sous la ligne de flottaison, avec une hauteur réservée. Sur l'accueil et les autres pages : `render-blocking` (CSS, estimé 600-900 ms) et 39 Ko de JS inutilisé à réduire (précharger la police, inliner le CSS critique, scinder le JS).

## MINEUR
5. `/admin` : ni `X-Robots-Tag` ni ligne dans `public/robots.txt` (seul `/api/` est exclu). Un visiteur est redirigé vers la connexion et les vues portent `noindex`, donc le risque est faible, mais un `X-Robots-Tag: noindex, nofollow` pour `admin/*` dans `app/Http/Middleware/SecurityHeaders.php:39` (comme pour `api/*`) complèterait la protection.
6. Les sitemaps n'ont pas d'alternates `xhtml:link` ni de `lastmod`. L'hreflang HTML suffit pour Google, mais l'ajout accélère la découverte. Attention : les URL ne passeront en `alternate` qu'après la correction du défaut 1.
7. Le limiteur `throttle:search` (120 req/min/IP, `AppServiceProvider.php:97`) renvoie 429 sur les catégories et les villes. Un crawl rapide, ou un robot derrière une IP partagée, reçoit des 429 : constaté à 400 requêtes en rafale. Prévoir une exemption pour les robots reconnus, ou relever la limite pour les GET de pages indexables.
