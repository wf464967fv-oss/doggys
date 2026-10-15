# Revue SEO & performance, tour 2 : contre-vérification Fondations + lot Annuaire

Date : 2026-10-15. Méthode :
- `npm run build`, puis `php artisan serve --port=8124` ;
- crawl de 600 pages de l'annuaire en partant des 4 index de catégories ;
- Lighthouse 13.5.0 avec Edge headless.

`artisan serve` ne compresse rien et n'envoie aucun en-tête de cache. Pour mesurer ce que donnera Apache, j'ai lancé chaque test deux fois :
- **brut**, directement sur `artisan serve` ;
- **gzip**, derrière un petit proxy Node (port 8126) qui reproduit le `.htaccess` : gzip des types texte, `immutable` sur `/build/assets`, 30 jours sur `/fonts` et `/images`.

Le canonical forcé (M2) a été testé sur une seconde instance lancée en `APP_ENV=staging APP_URL=https://doggys.be` (port 8125). Les deux serveurs et le proxy ont été arrêtés par leur PID.

## Verdict : À CORRIGER

Les Fondations sont presque toutes validées : M1, M2, M4 et les mineurs 6 à 10 sont corrigés. Deux points restent ouverts : la SearchAction (M3, partiel) et le slug « fokker » (11).

Le lot Annuaire est solide techniquement :
- hreflang réciproques avec slugs traduits ;
- vraies 404 ;
- noindex pour la recherche, les pages 2+ et les villes de moins de 3 pros ;
- JSON-LD valide ;
- aucun lien cassé sur 600 pages.

Il reste **4 défauts MAJEURS** :
- LCP de la page ville mobile sous l'objectif à cause des tuiles de carte ;
- pages ville quasi dupliquées pour les sections de commune ;
- fiches très pauvres indexables ;
- title des fiches sans mot-clé.

## Scores Lighthouse (mobile sauf mention)

| Page | Brut (artisan serve) | Gzip + cache (≈ Apache) | Détail gzip |
|---|---|---|---|
| /fr (accueil) | 88 / 100 / 100 / 100 | **99** / 100 / 100 / 100 | FCP 1,4 s, LCP 2,0 s, CLS 0 (1er tir : 94, tirs 2 et 3 : 99) |
| /fr/veterinaire (catégorie) | 92 / 100 / 100 / 100 | **100** / 100 / 100 / 100 | FCP 1,1 s, LCP 1,8 s, 150 Ko |
| /fr/toiletteur-salon/liege (ville + carte) | 78 / 96 / 96 / 100 | **93–94** / 96 / 96 / 100 | FCP 1,3 s, **LCP 3,1–3,2 s = tuile OSM**, CLS 0, 384 Ko |
| /fr/toiletteur-salon/liege (desktop) | 98 / 96 / 100 / 100 | — | LCP 1,1 s (tuile) |
| /fr/pro/furfection-essen (fiche) | 85 / 100 / 100 / 100 | **96–99** / 100 / 100 / 100 | LCP 2,2–2,6 s, CLS 0 |

Scores dans l'ordre Perf / Accessibilité / Bonnes pratiques / SEO.

Poids après build, mesuré avec `gzip -9` :

| Fichier | Brut | Gzip |
|---|---|---|
| `app.css` | 92,3 Ko | 15,1 Ko |
| `app.js` | 73,9 Ko | 24,2 Ko |
| `map.js` (Leaflet) | 151,5 Ko | 43,9 Ko |
| `map.css` | 10,6 Ko | 2,7 Ko |

Leaflet est bien chargé à la demande (`import()` seulement si `[data-map]` est présent). Il n'est pas chargé sur l'accueil ni sur la page catégorie.

**Ce qui dépend d'Apache :** la compression et le cache. Sans eux, l'accueil reste à 88 et la catégorie à 92. Avec eux, toutes les pages sans carte sont à ≥ 96. Il faudra confirmer après déploiement avec :
- `curl -sI -H 'Accept-Encoding: gzip' https://doggys.be/build/assets/app-*.css`, qui doit renvoyer `Content-Encoding` et `Cache-Control: …immutable` ;
- un tir Lighthouse sur l'URL réelle.

## (a) Contre-vérification de `fondations-seo.md`

| # | Point | Statut | Preuve |
|---|---|---|---|
| M1 | Compression + cache dans `.htaccess` | **VALIDÉ** (relecture, voir ci-dessous) | Gzip émulé : accueil 88 → 99 |
| M2 | Canonical indépendant du Host | **VALIDÉ** | En staging, `Host: evil.example` donne `canonical https://doggys.be/fr` ; `/index.php/fr` donne le canonical `https://doggys.be/fr` ; og:url et og:image en https://doggys.be. `forceRootUrl` est inactif en local, comme prévu |
| M3 | JSON-LD Organization + WebSite | **VALIDÉ partiellement** | `@graph` Organization + WebSite valide, `inLanguage` fr-BE, `publisher` → `@id`. **SearchAction toujours absente**, alors que la recherche existe maintenant (défaut 5) |
| M4 | Title et h1 de l'accueil | **VALIDÉ** | « Doggys · Annuaire des professionnels canins en Belgique » (55) ; h1 « Les meilleurs professionnels canins, près de chez vous » ; NL, EN et DE équivalents |
| 5 | Liens de catégories vers `?category=` | **VALIDÉ** | Il ne reste que 4 liens vers `/fr/recherche` (CTA). Les catégories pointent vers `/fr/{slug}` |
| 6 | Image du hero | **VALIDÉ** | `srcset 240w/576w`, `sizes="288px"`, `width/height 288`, `loading="lazy"`, plus de fetchpriority |
| 7 | Wordmark | **VALIDÉ** | `logo-wordmark-160/320.webp` avec `srcset` et `sizes` |
| 8 | og:image:alt + twitter:image | **VALIDÉ** | Présents et traduits |
| 9 | Marque en double | **VALIDÉ** | « Join Doggys », « Rejoignez Doggys »… sans suffixe |
| 10 | Manifest | **VALIDÉ** | `"id": "/"`, description en 4 langues |
| 11 | Terminologie NL « fokker » | **TOUJOURS EN DÉFAUT** (partiel) | Seeder corrigé (« Erkende kweker »), mais `lang/nl/directory.php:40` dit encore « Erkende fokkers » et `lang/nl/routes.php:50` utilise le slug `erkende-fokker` (défaut 6) |

### Relecture de `public/.htaccess`, ligne par ligne

Aucune directive ne peut provoquer de 500 ni de boucle :

- **L1–6 : en-tête standard Laravel.** `Options -MultiViews -Indexes` est protégé par `<IfModule mod_negotiation.c>`. Si o2switch interdit `Options` dans AllowOverride, ce serait un 500, mais c'est le fichier Laravel d'origine, déjà déployé partout chez o2switch.
- **L9–12 : HTTP → HTTPS.** La condition `%{HTTPS} off` et `X-Forwarded-Proto` différent de `https` évite toute boucle derrière un proxy TLS. Localhost et 127.0.0.1 sont exclus. La syntaxe `\d` est valide (PCRE).
- **L15–16 : www → apex.** La cible est directement en `https://`. Après la redirection, l'hôte n'a plus de `www`, donc pas de boucle.
- **L19–20 : `/index.php…` → URL propre.** Le test porte sur `THE_REQUEST`, la ligne de requête d'origine. La réécriture interne vers `index.php` (L38) ne la modifie pas, donc pas de boucle. `/index.php` seul redirige vers `/`, puis `/` redirige en 302 vers `/fr`.
- **L22–38 : règles standard Laravel** (Authorization, XSRF, slash final, front controller).
- **L42–46 : `AddType`** dans `<IfModule mod_mime.c>` : sans risque.
- **L49–54 : brotli et deflate,** chacun dans son `<IfModule>`. Si les deux modules sont présents, chaque filtre ignore une réponse qui a déjà un `Content-Encoding`, donc pas de double compression. Voir le mineur 15 pour une écriture plus robuste.
- **L58–65 : `<If>/<ElseIf>`** (Apache ≥ 2.4) dans `<IfModule mod_headers.c>`. Syntaxe des expressions correcte, `<ElseIf>` placé juste après `<If>`. Seuls les fichiers statiques servis par Apache sont concernés ; les réponses PHP gardent leur `Cache-Control` Laravel.

## (b) Lot Annuaire : ce qui a été vérifié

**Crawl de 600 URL** (4 index, 76 pages catégorie, 520 pages ville) :
- **Statuts :** 600 × 200, aucun lien interne cassé.
- **Indexation :** 462 pages indexables, 138 en noindex (villes de moins de 3 pros et pages 2+).
- **Titles et descriptions :** aucun dépassement de longueur, une seule collision de title (défaut 9), 0 description dupliquée.
- **Canonical :** toujours auto-référent.
- **h1 :** exactement un par page.
- **JSON-LD :**
  - BreadcrumbList sur 600 pages, ItemList sur les 560 pages catégorie et ville, tout est du JSON valide.
  - Fiches : `LocalBusiness`, `VeterinaryCare`, `AnimalShelter`, `PetStore` ou `Park` selon la catégorie, avec PostalAddress, GeoCoordinates et `AggregateRating` seulement si `reviews_count > 0`. Conforme à la grille.

**Hreflang et sélecteur de langue :**
- `/fr/toiletteur-salon/liege` ↔ `/nl/hondentrimsalon/luik` ↔ `/en/dog-grooming-salon/liege` ↔ `/de/hundesalon/luttich`. Les 4 pages renvoient les mêmes 4 alternates + x-default. Le sélecteur de langue pointe sur ces mêmes URL.
- Fiches : `/fr/pro/{slug}` ↔ `/de/anbieter/{slug}`, même chose.

**Vraies 404 :**
- slug d'une autre langue (`/nl/veterinaire`, `/fr/toiletteur-salon/luik`) ;
- ville inconnue (`/fr/veterinaire/xyz`) ;
- fiche inconnue ;
- majuscules (`/fr/Veterinaire`) ;
- ville sans pro (`abort_if` vide).

**Recherche :** `noindex, follow` avec ou sans paramètres.

**Pagination :**
- Les pages 2+ sont en `noindex, follow`, sans canonical : cohérent.
- Le `rel="next"`/`"prev"` posé sur les liens par la vue Laravel est sans effet, et inoffensif.

**Collisions de routes :** aucun slug de catégorie, dans aucune langue, ne coïncide avec un segment réservé (contact, cookies, guides, pro, anbieter, categories…).

**Attribution OSM :** contrôle Leaflet « © OpenStreetMap » qui pointe vers /copyright (`resources/js/map.js:59`), plus le pied de page. Conforme ODbL.

**Carte :** conteneur à hauteur fixe (`h-72` / `sm:h-80`) avec un texte de chargement : CLS = 0 mesuré.

## Défauts

### MAJEUR

**1. LCP mobile des pages ville = une tuile OpenStreetMap (93–94 même avec gzip, objectif ≥ 95)**
- Fichier : `resources/views/directory/city.blade.php:62-65`.
- Problème : sur mobile, la carte (`h-72`) est affichée avant la liste (le `lg:order-2` ne la déplace qu'en desktop). Elle occupe donc l'écran initial. Le plus grand élément visible devient une tuile 256×256 de tile.openstreetmap.org, téléchargée après app.js, puis map.js, puis l'initialisation de Leaflet : LCP de 3,1 à 3,2 s avec gzip, 4,9 s en brut.
- Ce choix coûte aussi un « image-size-responsive » en Bonnes pratiques (96) et un « target-size » sur les marqueurs proches en Accessibilité (96). Ce dernier point est à transmettre au garde-fou Design.
- Correction attendue, au choix :
  - placer la carte **après** la liste sous `lg` (`order-2` sans préfixe, `lg:order-none` sur la liste ou équivalent) ;
  - ou monter la carte seulement quand elle entre dans le viewport : un `IntersectionObserver` autour de `import('./map.js')` dans `resources/js/app.js:129-131`, ce qui sert aussi les fiches, où la carte est sous la ligne de flottaison et fait télécharger environ 44 Ko de JS + 190 Ko de tuiles pour rien.
- Objectif : LCP = h1 ou intro, Lighthouse mobile ≥ 95 sur une page ville (avec gzip).

**2. Pages ville quasi dupliquées : les sections de commune ont leur propre page**
- Fichiers : `app/Http/Controllers/CategoryController.php:57-90` et le référentiel `PostalCode::cityFor/cityForSlug`.
- Problème mesuré sur les pages ville indexables de deux catégories en FR (68 pages) : **30 paires** ont une liste de page 1 identique à 50 % ou plus. Les pires cas sont des sections de commune :
  - `blankenberge` / `klemskerke` : 0,90 ;
  - `la-louviere` / `manage` : 0,90 ;
  - `la-louviere` / `boussoit` : 0,82 ;
  - `woluwe-saint-lambert` / `auderghem` : 0,79 ;
  - `la-louviere` / `havre` : 0,74.
- Le gabarit est identique (intro, 3 FAQ dont une réponse mot pour mot sur toutes les pages) et seul le nom de ville change. On compte 1 387 combinaisons catégorie × ville × 4 langues, soit environ 5 500 URL. C'est le profil « doorway pages » que Google déclasse en bloc.
- Correction attendue :
  1. Ne générer, lier et indexer des pages que pour les **communes** (≈ 581 entités, avec leur nom officiel et leurs exonymes), pas pour les sections ou localités de code postal. Une URL de section (`/fr/toiletteur-salon/klemskerke`) doit renvoyer une **301** vers sa commune (`…/de-haan`), ou au minimum un canonical vers elle.
  2. Règle anti-doublon : `noindex` si 70 % ou plus de la liste de page 1 est déjà présente sur une ville voisine plus peuplée.
  3. Varier le texte avec les données : nombre de pros vérifiés, part à domicile, communes couvertes, horaires du week-end… La réponse `faq_choose_a`, identique partout, doit dépendre de la catégorie (19 variantes × 4 langues).

**3. Fiches très pauvres indexables (546 fiches × 4 langues)**
- Fichiers : `resources/views/directory/provider.blade.php:44-46` (pas de `noindex`) et `ProviderController::show`.
- Problème : sur 1 946 fiches actives, 546 n'ont **ni adresse ni téléphone, ni e-mail, ni site** (seulement un nom et un code postal), et 0 a une description. Cela donne environ 2 180 URL indexables sans contenu utile. De plus, la meta description promet « Adresse, téléphone, horaires et avis » même quand rien de cela n'existe (`lang/fr/directory.php:79`).
- Correction attendue :
  - `noindex` (et exclusion du futur sitemap) pour toute fiche sans rue **et** sans moyen de contact, jusqu'à ce qu'elle soit complétée ou réclamée ;
  - une meta description construite à partir des champs réellement présents.

**4. Title des fiches sans catégorie ni langue**
- Fichier : `resources/views/directory/provider.blade.php:44`.
- Problème : le title est « FurFection – Essen | Doggys », identique en fr, nl, en et de, sans le métier. Les requêtes visées sont « toiletteur Essen » ou « hondentrimsalon Essen ».
- Correction attendue : `:name – :category à :city` traduit, par exemple :
  - FR « FurFection – Toiletteur en salon à Essen » ;
  - NL « FurFection – Hondentrimsalon in Essen » ;
  - DE « FurFection – Hundesalon in Essen ».

  Garder la limite de 50 caractères avant « | Doggys », en tronquant d'abord la catégorie, jamais le nom.

### MINEUR

**5. SearchAction absente du JSON-LD WebSite** (reste de M3)
- Fichier : `resources/views/home.blade.php`, bloc `@graph`.
- Ajouter :
  ```
  "potentialAction": {
    "@type": "SearchAction",
    "target": {
      "@type": "EntryPoint",
      "urlTemplate": "https://doggys.be/fr/recherche?location={search_term_string}"
    },
    "query-input": "required name=search_term_string"
  }
  ```
  avec l'URL localisée. Google n'affiche plus la sitelinks searchbox depuis fin 2024 ; c'est surtout une exigence de la grille.
- Au passage, mettre `@id` de l'Organization en `https://doggys.be/#organization` (il manque le `/`).

**6. Slug et libellé NL « erkende-fokker »**
- Fichiers : `lang/nl/routes.php:50` et `lang/nl/directory.php:40`.
- Remplacer par `erkende-hondenkweker` (ou `erkende-kweker`) et « Erkende kwekers ». C'est le terme réglementaire flamand, et il est cohérent avec le seeder.
- Le faire **avant la mise en ligne**, sinon il faudra une 301.

**7. Slugs de catégorie à arbitrer avant le lancement** (changer après la mise en ligne impose des 301)
- `lang/fr/routes.php` : `toiletteur-salon` ne correspond à aucune requête. Préférer `toiletteur-canin` (requêtes « toiletteur chien / canin + ville »). Éventuellement `refuge` → `refuge-animalier`.
- `lang/nl/routes.php` : `hondenlosloopweide` est un terme néerlandais (des Pays-Bas). En Flandre on dit « hondenloopzone » ou « hondenweide » : préférer `hondenloopzone`.
- `lang/de/routes.php` : `hundesalon` est correct, mais `hundefriseur` est la requête dominante en allemand. À trancher, impact faible (public DE limité).
- EN : RAS.

**8. Exonymes de villes manquants et translittération DE incohérente**
- Données `PostalCode`.
- Exonymes absents :
  - FR : Roeselare → **Roulers**, Ieper → **Ypres**, Tienen → **Tirlemont**, Halle → **Hal**, Lier → **Lierre**, Tongeren → **Tongres** ;
  - EN : Ieper → **Ypres**.

  Ce sont les formes que les francophones tapent.
- DE : Liège donne le slug `luttich`, alors que les routes translittèrent ü en `ue` (`zuechter`, `passwort-zuruecksetzen`). Harmoniser sur `luettich`.

**9. Title dupliqué entre deux villes homonymes**
- `/fr/parc-a-chiens/halle` et `/fr/parc-a-chiens/halle-2980` ont tous deux le title « Parcs à chiens à Halle ».
- Fichier : `resources/views/directory/city.blade.php:46`.
- Correction : pour une ville au slug suffixé, ajouter la commune de rattachement ou le code postal dans le title et le h1 (« … à Halle (Zoersel) »).

**10. Pages hors limites en 200**
- Exemples : `/fr/toiletteur-salon/liege?page=50` et `/fr/veterinaire?page=999` répondent 200 avec une liste vide (noindex).
- Correction attendue : `abort(404)` si `page > lastPage()`.
- Par ailleurs, le lien « page 1 » de la pagination pointe vers `?page=1`. Ce n'est pas grave (le canonical est propre), mais `->withPath()` ou un lien sans paramètre pour la page 1 est plus propre.

**11. Description générique des pages catégorie**
- Fichier : `lang/*/directory.php:144-147`.
- Problème : le h1 « Vétérinaires en Belgique » est suivi seulement d'un compteur et d'une phrase de résumé. Ce sont 19 pages × 4 langues à fort potentiel (« vétérinaire Belgique », « hondentrimsalon »), sans texte éditorial.
- Correction attendue : un paragraphe propre à chaque catégorie et à chaque langue (100–200 mots : rôle, réglementation, critères de choix) avec 1 ou 2 liens vers les guides quand ils existeront.

**12. Liens internes vers des pages en noindex**
- Les pages catégorie (bloc « per gemeente », 36 liens) et le bloc `nearbyCities` des pages ville lient des villes qui seront en noindex (moins de 3 pros) : 102 des 520 pages ville crawlées.
- Correction : ne lier que les villes indexables, ce qui revient à appliquer le même calcul (rayon de 15 km, au moins 3 pros) au moment de construire les liens.

**13. Pas de `X-Robots-Tag` sur l'API**
- `/api/v1/providers` et `/places` renvoient du JSON indexable en théorie, avec des URL de fiches.
- Correction : un en-tête `X-Robots-Tag: noindex` sur le groupe `api` (middleware), et `Disallow: /api/` dans le futur `robots.txt`.

**14. Meta description des pages ville et catégorie**
- Elles annoncent « horaires et avis » / « openingsuren en reviews » alors qu'il n'y a aucun avis aujourd'hui et que la majorité des fiches n'ont pas d'horaires (`lang/*/directory.php:144,157`).
- Correction : n'énumérer que ce qui existe, ou formuler au conditionnel. Promettre ce qui n'existe pas dégrade le CTR et la confiance.

**15. `.htaccess` : robustesse**
- Ligne 49-54 : préférer la forme documentée par Apache, `AddOutputFilterByType BROTLI_COMPRESS;DEFLATE …`, à l'intérieur d'un `<IfModule mod_brotli.c>`, avec le bloc `DEFLATE` seul en repli.
- Ligne 9-16 : `http://www.doggys.be` fait deux redirections (vers `https://www`, puis vers l'apex). Placer la règle www → apex (déjà en https) **avant** la règle HTTPS pour n'avoir qu'un saut.

## À prévoir (lots ultérieurs, pas des défauts)

- **`sitemap.xml`** (index + un par langue) : n'y mettre **que** les pages indexables, c'est-à-dire :
  - les pages catégorie ;
  - les pages ville de communes avec au moins 3 pros, après dédoublonnage (défaut 2) ;
  - les fiches non pauvres (défaut 3).

  Ajouter la ligne `Sitemap:` dans `robots.txt`. Aujourd'hui `/sitemap.xml` répond 404 et `robots.txt` ne contient que `Disallow:` vide.
- **JSON-LD Article** sur les guides et **maillage guides ↔ catégories** : l'agent Guides est en cours, `/guides` n'a pas été audité.
- **Tuiles OSM :** la politique d'usage de tile.openstreetmap.org interdit un usage intensif en production. Prévoir un cache ou un fournisseur de tuiles compatible ODbL si le trafic grossit, en gardant l'attribution.
- **Données :** `/fr/toiletteur-salon/anvers` est en noindex (moins de 3 salons dans un rayon de 15 km autour d'Anvers) : couverture d'import incomplète en Flandre. À compléter avec les sources légales prévues.
