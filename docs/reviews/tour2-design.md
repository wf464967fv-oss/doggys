# Revue DESIGN & UX, tour 2 : contre-vérification « Fondations » et audit « Annuaire »

Build : `npm run build` OK. Serveur propre sur le port 8124, tuiles OSM réelles chargées.
Captures : `storage/app/screenshots/review-design/tour2/` (préfixe `a-` = contre-vérification, `b-` = annuaire, `s-` = tranches de page, `crop-` = zooms). Mesures brutes dans `tour2/_report.json`.
Aucun scroll horizontal sur les 104 captures pleine page. Aucun ID dupliqué.

## Verdict : À CORRIGER

Les fondations sont presque soldées : 19 points validés sur 20, avec une régression introduite par la correction de M5. En revanche, le lot Annuaire a 1 BLOQUANT (livraison) et 9 MAJEURS. La fiche pro, en particulier, ne donne pas encore envie de contacter.

---

## (a) Contre-vérification de `fondations-design.md`

| # | Statut | Preuve |
|---|---|---|
| B1 ID dupliqué `field-password` | VALIDÉ | `a-profile-360.png`, `_report.json` sans `dupIds` |
| M1 bordures des champs ≥ 3:1 | VALIDÉ | `a-field-colors` : bordure rgb(176,122,74), soit 3.67:1 ; `a-register-errors-360.png` |
| M2 placeholders | VALIDÉ | placeholder rgb(122,90,69) = `muted`, 6.22:1 |
| M3 focus champs / fonds bruns | VALIDÉ | `a-focus-field-1280.png` (bordure brune et halo), `a-focus-footer-360.png` (contour orange sur le footer) |
| M4 cibles < 44 px (accueil, footer) | VALIDÉ sur l'accueil | `_report.json` : plus aucune cible < 44 px sur `a-home-*`. Le défaut **récidive dans l'annuaire**, voir N4 |
| M5 accueil mobile trop long | VALIDÉ (hauteur) | 8 894 → 5 469 px (FR), 5 381 (DE), 5 309 (NL) : `a-home-fr-360.png`. **Régression introduite**, voir R1 |
| M6 grille de catégories trouée | VALIDÉ sur l'accueil | `a-home-fr-1280.png` (grille continue, groupe en sur-titre). Le défaut **récidive sur /categories**, voir N7 |
| m1 bouton du menu mobile sur 2 lignes | VALIDÉ | `a-menu-360.png` |
| m2 CTA en double à 768 px | VALIDÉ | `a-menu-768.png` |
| m3 dropdowns : Échap / clic extérieur | VALIDÉ | `a-dropdown` : fermé après Échap et après clic extérieur |
| m4 header sticky | VALIDÉ | `scroll-padding-top: 96px` |
| m5 colonnes serrées à 768 px | VALIDÉ | `a-home-de-768.png` (carrousels « vie » et « confiance ») |
| m6 récapitulatif d'erreurs sur l'auth | VALIDÉ | `a-register-errors-360.png` |
| m7 alignement « Mot de passe oublié ? » | VALIDÉ | `a-focus-field-1280.png` (écart résiduel ≤ 4 px) |
| m8 espaces fines en français | VALIDÉ | `a-register-errors-360.png` (« j’en rêve !) » sur une ligne) |
| m9 code 404 en orange | VALIDÉ | `a-404-360.png` (orange foncé) |
| m10 hiérarchie dt/dd | VALIDÉ | `a-account-1280.png` |
| m11 poids « 18,50 » | VALIDÉ | `a-dog-edit-768.png` (« 18,5 ») |
| m12 badge de rôle isolé | VALIDÉ | `a-profile-360.png` (badge sous le h2) |
| m13 styles de champ dupliqués | VALIDÉ | `.field` dans `app.css:119`, utilisé dans `dogs/form.blade.php:64,69` |

### R1 (MAJEUR, régression de M5) : mots coupés lettre à lettre dans les tuiles de catégories à 360 px
- `crop-cats-fr-360.png` : « Hydrothérapi/e », « physiothérapi/e », « Comporteme/ntaliste ».
- `crop-cats-de-360.png` : « Hundetraine/r », « Tierosteopat/h », « Hundepensio/n », « Tierhandlun/g », « Hundeauslau/fgebiet ».
- `resources/views/home.blade.php:112` : `[overflow-wrap:anywhere]` casse n'importe où, et `hyphens-auto` ne produit aucun tiret dans Edge/Chrome Windows. Avec l'icône à gauche, il reste environ 85 px pour le libellé.
- Correction :
  - carte `home.blade.php:105` → `flex-col items-start gap-2 p-3` aussi sur mobile (icône au-dessus, libellé sur toute la largeur, environ 140 px) ;
  - retirer `[overflow-wrap:anywhere]` ;
  - libellé `text-sm leading-tight sm:text-lg` ;
  - garder `hyphens-auto`, efficace sur Safari et Android.
- Vérifier ensuite qu'aucun mot de `lang/de/categories.php` ne dépasse 140 px à 14 px (« Tierzahnheilkunde » tient).

---

## (b) Audit du lot ANNUAIRE

### BLOQUANT

#### N1. Les vues de l'annuaire commitées dépendent d'un fichier non commité
- `git ls-files resources/views/components/breadcrumbs.blade.php` est vide : le composant n'est pas suivi (il appartient au travail en cours de l'agent Guides, tout comme `lang/*/blog.php`, d'où vient la clé `blog.breadcrumb`). Or le commit `fa834f3` l'utilise dans `directory/{categories,category,city,provider}.blade.php`.
- Sur un checkout propre de `fa834f3`, toutes les pages catégorie, ville et fiche plantent (composant introuvable).
- Correction : commiter `components/breadcrumbs.blade.php` avec l'annuaire. Son aria-label doit passer sur une clé `ui.breadcrumb` (dans `lang/*/ui.php`) et non `blog.breadcrumb`. Hors grille design, mais cela bloque la livraison.

### MAJEURS

#### N2. Fiche pro : la carte n'arrive qu'après le formulaire de signalement sur mobile (jugement : à corriger)
- `s-provet-360-0.png` / `-1.png` : à 360 px, l'ordre est h1 → coordonnées → avis (gros état vide) → **signalement** → carte (vers 1 450 px) → bloc « C'est votre entreprise ? » → fiabilité.
- Pour un parc à chiens ou une fiche sans téléphone (`s-longname-360-0.png`, `b-pro-parc-360.png`), la carte est pourtant la seule information utile. Le signalement est une action secondaire et ne doit jamais précéder le contenu.
- `directory/provider.blade.php:91-235` : l'`<aside>` (carte) suit tout le bloc de gauche dans le DOM.
- Correction :
  - grille unique `lg:grid-cols-[minmax(0,1fr)_22rem] lg:grid-flow-dense` ;
  - ordre DOM : 1) coordonnées, 2) `<div class="lg:col-start-2 lg:row-start-1 lg:row-span-2">` avec la carte, 3) horaires, 4) avis, 5) `<aside class="lg:col-start-2">` avec réclamation et fiabilité, 6) signalement en dernier (`lg:col-start-1`) ;
  - mobile résultant : coordonnées → carte → horaires → avis → réclamation/fiabilité → signalement.
- Alternative plus simple : sortir `<x-map>` de l'aside et le placer dans la carte « Coordonnées » (`class="mt-4 h-56"`), réservé au mobile.

#### N3. La fiche pro ne donne pas envie de contacter : aucune action principale, du vide mis en avant
- `b-pro-vet-phone-360.png`, `s-prohours-1280-0.png`.
- Au-dessus de la ligne de flottaison, il n'y a que le nom, une pastille et « Pas encore d'avis ». Le téléphone n'est qu'un lien souligné au milieu d'une liste. Il n'y a ni bouton « Appeler » ni « Itinéraire ».
- Le seul bouton orange plein de la page est « C'est mon entreprise » : la page s'adresse au pro, pas au propriétaire.
- L'absence d'avis est affichée deux fois, dont un état vide de 300 px (`provider.blade.php:83` et `:182`).
- Corrections :
  1. Sous le h1 (`provider.blade.php:62`), ajouter une barre d'actions `mt-5 flex flex-wrap gap-3` :
     - `<x-button icon="phone" :href="'tel:'.$provider->phone">{{ phoneForDisplay }}</x-button>` en primaire si un téléphone existe ;
     - `<x-button variant="soft" icon="route" href="https://www.openstreetmap.org/directions?to={lat},{lng}">Itinéraire</x-button>` (toujours présent) ;
     - `<x-button variant="soft" icon="globe">Site web</x-button>` si un site existe.
  2. Mobile : barre fixe `fixed inset-x-0 bottom-0 z-30 flex gap-2 border-t border-line bg-cream-light/95 p-3 backdrop-blur sm:hidden` (Appeler + Itinéraire), avec `pb-20` sur `<main>`.
  3. Remplacer l'empty-state d'avis (`:182`) par une ligne compacte `rounded-2xl bg-cream-light p-4 text-sm` et supprimer le doublon du hero (`:83`).
  4. Bloc réclamation (`:231`) : passer le bouton en `variant="soft"` sur fond `bg-white ring-1 ring-line`, pour qu'il ne concurrence plus l'action propriétaire.

#### N4. Cibles tactiles < 44 px (récidive de M4)
- Pastilles de catégorie de la fiche : `min-h-9`, soit 36 px (`provider.blade.php:67`, `_report.json` : `A.inline-flex.min-h-9 117x36`). Correction : `min-h-11 px-4`.
- Pagination : 38 px (« Suivant » 91x38, pages 42x38) sur la recherche, la catégorie et la ville (`b-category-768.png`). C'est la vue Laravel par défaut. Correction : publier `resources/views/vendor/pagination/tailwind.blade.php` avec `min-h-11 min-w-11`, ou la passer en paramètre de `->links('…')`.
- Zoom Leaflet : 30x30 px. Correction (`app.css`, hors `@layer`, voir N6) : `.leaflet-touch .leaflet-bar a { width: 44px; height: 44px; line-height: 44px; }`.
- Fil d'Ariane : liens de 20 px de haut (`components/breadcrumbs.blade.php`). Correction : `inline-flex min-h-11 items-center`.

#### N5. Recherche mobile : le premier résultat arrive à environ 1 080 px
- `s-search-360-0.png` : sur une page de résultats, le formulaire complet (3 champs, filtres, 2 boutons, environ 650 px), puis la carte (288 px), passent avant le premier résultat. L'utilisateur, qui vient de chercher, ne voit aucun résultat sur son premier écran.
- `directory/search.blade.php:26-65`. Correction :
  - quand `$hasOrigin`, sous `md`, remplacer le formulaire par un résumé `<details>` : `<summary class="flex min-h-12 items-center justify-between rounded-2xl bg-white px-4 ring-1 ring-line">Vétérinaires · Liège · 10 km <span>Modifier</span></summary>`, qui contient le formulaire (`md:open` via `open` côté serveur seulement si `! $hasOrigin`) ;
  - carte mobile `h-56` au lieu de `h-72`, ou bouton « Voir la carte » repliable.
- Objectif : premier résultat visible sous 600 px.

#### N6. Les styles de la carte ne s'appliquent pas : CSS Leaflet hors couche
- `b-map-popup-1280.png` : lien « Voir la fiche » en bleu Leaflet (#0078A8), marges par défaut (gros vides autour de « 1,2 km »). L'attribution OSM, mentionnée légalement, est rognée par le coin arrondi (« OpenStreetMar », `s-search-360-0.png`, `s-provet-360-1.png`).
- Cause : `app.css:244-279` place `.doggys-pin`, `.leaflet-popup-*` et l'attribution dans `@layer components`, alors que `leaflet/dist/leaflet.css` (importé par `map.js:6`) est **hors couche**. En CSS cascade layers, un style hors couche l'emporte toujours, quelle que soit la spécificité.
- Correction : sortir le bloc `app.css:243-279` de `@layer components` (le laisser au niveau racine), ou importer Leaflet dans une couche inférieure (`@import 'leaflet/dist/leaflet.css' layer(base);` dans `app.css`, en retirant l'import JS).

#### N7. /categories : grille trouée et moitié « Bientôt » (récidive de M6)
- `b-categories-1280.png` (2 708 px) et `b-categories-360.png` (4 689 px) : 7 groupes dans une grille de 3 colonnes à moitié vides. 9 catégories sur 20 affichent « Bientôt » (toiletteur à domicile, kiné, hydrothérapie, dentisterie, comportementaliste, pet-sitter, promeneur, garderie, éleveur agréé). Pour une référence nationale, la page d'index expose surtout ce qui manque.
- `directory/categories.blade.php:13-19`. Correction :
  - une grille continue `grid-cols-2 sm:grid-cols-3 lg:grid-cols-4`, comme l'accueil ;
  - catégories avec fiches d'abord, triées par nombre décroissant ;
  - catégories vides regroupées en fin de page dans un bloc compact « Bientôt sur Doggys » (pastilles `min-h-11`, sans résumé), avec un CTA « Vous êtes pro ? Ajoutez votre activité ».
- Même logique sur l'accueil : ne pas mettre en avant des tuiles qui mènent à 0 résultat.

#### N8. Filtres morts : « Se déplace à domicile » et « Pros vérifiés uniquement » renvoient toujours 0 résultat
- `b-search-filters-1280.png` : vétérinaires près de Liège avec « à domicile » donne 0 résultat. La base contient 0 fiche `is_mobile` et 0 fiche vérifiée.
- L'état vide propose « Élargir à 25 km » alors que c'est le filtre qui vide la liste : la sortie proposée est fausse.
- Corrections :
  - `search.blade.php:48-49` : n'afficher chaque filtre que si au moins une fiche peut y répondre (compteur passé par le contrôleur) ;
  - `search.blade.php:88-97` : si `$filtersUsed`, l'action principale de l'état vide devient `<x-button :href="localized_route('search', request()->except(['mobile','verified','min_rating','page']))" icon="x">Retirer les filtres</x-button>`.

#### N9. Casse des noms de lieux : « Vaux-Sur-Sûre », « Vaux-Sous-Chèvremont », « Braine-L'Alleud », « Beveren-Aan-De-Ijzer »
- Visible dans les H1, titles, fils d'Ariane et cartes : `b-city-vaux-360.png` (« Vétérinaires à Vaux-Sur-Sûre »), `s-vauxnl-360-0.png`, `b-category-1280.png` (pastilles « Vaux-Sur-Sûre », « Braine-L'Alleud »), `b-search-results-1280.png` (« 4051 Vaux-Sous-Chèvremont »). Sur des pages SEO destinées à être référence, une faute d'orthographe dans le H1 nuit à la crédibilité.
- Source : `database/data/geonames-postal-codes-be.tsv` (GeoNames met chaque segment en capitale). Plus de 150 localités concernées (`grep -cE "\-(Sur|Sous|Lez|La|Le|De|Du|Des|L'|D')[- ]"` = 150), plus les noms néerlandais (« Aan », « Op », « Den », « Ij »).
- Correction : normaliser à l'import du TSV (et dans les fiches importées d'OSM qui en héritent) :
  - après un tiret, mettre en minuscules `sur|sous|lez|lès|la|le|les|de|du|des|aux|en|et|devant|aan|op|bij|ter|den|in` ;
  - `L'`/`D'` en `l'`/`d'` ;
  - `Ij` en `IJ`.
  - Exemples : « Vaux-sur-Sûre », « Braine-l'Alleud », « Beveren-aan-den-IJzer » (l'exception « den » vient du nom officiel). Ajouter un test sur ces 3 cas.

#### N10. Clavier : 25 arrêts de tabulation dans la carte avant la liste des résultats
- Sur la recherche et les pages ville (`search.blade.php:100-103`, `city.blade.php:79`), la carte précède la liste dans le DOM (`lg:order-2` ne change que l'ordre visuel). Au clavier, il faut traverser le conteneur, le zoom, le repère d'origine et 20 marqueurs avant d'atteindre le premier résultat (`_report.json` → `map-keyboard`, « tab stops in map 25 »).
- Les marqueurs doublonnent la liste (le composant le dit lui-même : « every marker is also listed in the page »).
- Correction : `resources/js/map.js:65,70` → `L.marker(…, { keyboard: false, … })`. Ajouter sur le conteneur un lien d'évitement « Aller aux résultats » (`sr-only focus:not-sr-only`). Garder le zoom accessible.

### MINEURS

1. **Repère d'origine caché sous les marqueurs** (`b-map-focus-marker-1280.png`, `s-search-360-0.png`). `map.js:65` : `zIndexOffset: -100` → `1000`. C'est le point de référence de la recherche.
2. **Popup Leaflet au clavier** : après Entrée sur un marqueur, la popup s'ouvre mais le focus reste sur le marqueur, et Échap ne la ferme pas (`popup after esc true`). `map.js:71` : sur `popupopen`, focaliser `.doggys-popup-link` ; sur `keydown` Échap, `map.closePopup()`. Devient sans objet si N10 retire les marqueurs du clavier.
3. **Marqueurs superposés** : à 10 km de Liège, une dizaine de pins de 46 px se recouvrent et ceux du haut sont coupés (`s-search-360-0.png`, `b-city-de-360.png`). `map.js:8-14` : pins de 30x38 px, et `fitBounds(points, { paddingTopLeft: [24, 52], paddingBottomRight: [24, 16] })` pour l'ancrage bas. Pas de dépendance de clustering.
4. **Cartes de résultats monotones** : « Pas encore d'avis » répété 20 fois par page (`b-search-results-1280.png`, `b-category-1280.png`). `provider-card.blade.php:49` : n'afficher que si `reviews_count > 0`. Remplacer par l'info utile disponible (téléphone, « Horaires connus », ou `x-badge` « Se déplace » quand c'est vrai).
5. **Autocomplétion** : le `<datalist>` natif affiche un triangle noir Edge dans le champ (`b-search-autocomplete-1280.png`), hors charte. `input.blade.php` (si `list`) : `[&::-webkit-calendar-picker-indicator]:opacity-0`. Par ailleurs, « Lie » propose « 4020 Liège » mais pas « 4000 Liège », le code principal (`_report.json` → `datalist`). Dédoublonner par nom en gardant le plus petit code postal.
6. **Horaires** : virgule sans espace « 09:00–11:30,17:00–19:30 » (`/fr/pro/camping-le-pirot-han-sur-lesse`). `Provider::openingHoursLines()` : remplacer `,` par `, `. Le texte libre OSM en néerlandais (« Bellen voor afspraak », `s-prohours-1280-0.png`) s'affiche sans `lang="nl"` sur une page FR (WCAG 3.1.2). Envelopper les lignes non reconnues dans `<span lang="und">` ou détecter la langue de la fiche.
7. **Fiches non « professionnelles »** : sur un parc à chiens, les textes parlent de « ce professionnel » (« Pas encore de coordonnées pour ce professionnel », `s-longname-360-0.png`) et proposent « C'est votre entreprise ? ». `provider.blade.php:141,231` : textes selon la catégorie (`parc-a-chiens`, `lieu-dog-friendly` → « ce lieu »), pas de bloc réclamation pour les parcs publics.
8. **Données incohérentes visibles** : « Catcocoon » (refuge pour chats) classé « Refuge » chien (`s-refcat-360-0.png`). « Formation aux métiers du chien » classé « Parc à chiens ». « Anvers (3) » vétérinaires et « Vaux-sur-Sûre (10) » en tête des communes (`b-category-1280.png`). À renvoyer à l'agent import : exclure les refuges félins, revoir la catégorisation OSM, et ventiler les communes fusionnées (Anvers → districts).
9. **Message d'erreur du signalement** : « Le champ Votre message est obligatoire. » (`crop-report-errors-360.png`). L'attribut injecté reprend le libellé du champ. Correction : messages dédiés dans la Form Request, par exemple « Décrivez le problème en quelques mots (10 caractères minimum). »
10. **Liste « Toutes les adresses » de la page catégorie** : 698 fiches sur 30 pages par ordre alphabétique, ouvrant sur « 'T Klaverblad », « 't Kabientje DAP » (`b-category-1280.png`). Navigation inutile au-delà de la page 1. Trier par nombre d'avis puis par nom en ignorant les apostrophes, ou remplacer par un regroupement par province.
11. **Focus des marqueurs** : contour carré décalé du pin (`b-map-focus-marker-1280.png`). `app.css` `.doggys-pin:focus-visible { outline-offset: 2px; border-radius: 9999px; }`, hors `@layer`, voir N6.
12. **Footer mobile en 2 colonnes** : « Politique de confidentialité » et « Conditions d'utilisation » sur 2 lignes chacun, collés (`a-404-360.png`). `footer.blade.php` : `space-y-1` → `space-y-2` sur les `ul`.
