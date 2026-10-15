# Revue DESIGN & UX — lot « Fondations »

Captures : `storage/app/screenshots/review-design/` (360/768/1280, accueil ×4 langues, auth, compte, chien, erreurs, menu mobile, dropdowns, focus). Métriques brutes : `_report.json` (hauteurs, débordements, cibles < 44 px, IDs dupliqués).
Aucun scroll horizontal constaté sur les 70 captures (y compris DE/NL à 360 px).

## Verdict : À CORRIGER

Un BLOQUANT (accessibilité des labels) et 6 MAJEURS (contrastes non textuels, contraste des placeholders, focus invisible sur fond brun, cibles tactiles, longueur de l'accueil sur mobile, grille des catégories sur desktop).

---

## BLOQUANT

### B1. ID dupliqué `field-password` sur la page Profil : le label du champ de suppression pointe vers le mauvais champ
- Page : `/fr/mon-compte/profil`, toutes largeurs (`profile-360.png`, `profile-1280.png`, `_report.json` → `DUP:field-password`).
- `resources/views/account/profile.blade.php:140` et `:159` : les deux `<x-input name="password">` génèrent `id="field-password"`. Le `<label for>` de « Votre mot de passe, pour confirmer » (suppression du compte) cible donc le champ « Nouveau mot de passe ». Cliquer sur ce label place le focus dans le formulaire de changement de mot de passe, et le lecteur d'écran annonce le mauvais nom pour l'un des deux champs. Le HTML est aussi invalide.
- Correction : `profile.blade.php:159` → ajouter `id="delete-password"` (le composant le gère déjà, `input.blade.php:20`). Même vérification pour `:141` et `register` si un second formulaire est ajouté un jour.

## MAJEURS

### M1. Bordures des champs invisibles (WCAG 1.4.11, contraste non textuel)
- Toutes les pages avec formulaire, par exemple `login-1280.png`, `register-360.png`, `dog-create-360.png`.
- `resources/views/components/input.blade.php:42`, `select.blade.php:37`, `textarea.blade.php:32`, `dogs/form.blade.php:64,69` : `border-line` (#f1dcc0) sur blanc = **1.33:1** (minimum 3:1). Sur fond `cream-light`, c'est 1.27:1. Sur mobile, le contour des champs vides se distingue à peine du fond de la carte.
- Correction : ajouter un token `--color-line-strong: #b07a4a;` (3.67:1 sur blanc) dans `resources/css/app.css` (`@theme`) et remplacer `border-line` par `border-line-strong` sur les champs (input/select/textarea/champs de vaccins). Garder `ring-line` pour les cartes (décor).

### M2. Placeholders sous le seuil de contraste
- Accueil (« Code postal ou commune »), toutes langues et largeurs (`slice-home-fr-360-0.png`).
- `input.blade.php:40`, `textarea.blade.php:31`, `dogs/form.blade.php:64` : `placeholder:text-muted/70` = **3.19:1** (minimum 4.5:1).
- Correction : `placeholder:text-muted` (6.22:1).

### M3. Focus des champs trop faible et focus invisible sur fonds bruns
- a) Champs : `focus:outline-none` remplace l'outline global par une bordure orange (#f5a043, **2.10:1** sur blanc) et un `ring-orange/25` (**1.20:1**). Au clavier, on distingue mal le champ actif. Fichiers : `input.blade.php:40-43`, `select.blade.php:36-38`, `textarea.blade.php:31-33`, `dogs/form.blade.php:64,69`. Correction : `focus:border-brown focus:ring-4 focus:ring-orange/40` (brun 8.5:1), ou supprimer `focus:outline-none` pour garder l'outline global `outline-3 outline-brown`.
- b) Footer : l'outline global `outline-brown` (#6b3e1f) sur `bg-brown-dark` (#4a2812) = **1.46:1**. Il est quasi invisible (`focus-footer-1280.png`, lien « Trouver un pro »). Même problème pour le bouton « Créer son profil » dans le bandeau `bg-brown` (`home.blade.php:130`, contour brun sur brun = 1:1).
- Correction : `footer.blade.php:1` et `home.blade.php:130` → ajouter `[&_:focus-visible]:outline-orange` (6.23:1 sur brown-dark, 4.28:1 sur brown). Faire de même sur tout futur bloc `bg-brown*`.

### M4. Cibles tactiles < 44 px
- Accueil, toutes largeurs : les pastilles de catégories de la section « Toute la vie de votre chien » font **36 px** de haut (12 liens, `_report.json` → `A 113x36 "Éleveur agréé"`…).
- `home.blade.php:119` : `min-h-9`. Correction : `min-h-11 px-4`.
- Liens « OpenStreetMap » / « GeoNames » du footer : 19 px de haut (`footer.blade.php:47-48`). Correction : `inline-flex min-h-11 items-center` ou `py-3` sur ces liens.

### M5. Accueil mobile trop long (8 894 px FR, 8 892 px DE à 360 px)
- `home-fr-360.png`, `home-de-360.png`, `slice-home-fr-360-0..6.png`.
- Répartition : la grille des catégories occupe environ 3 000 px (20 cartes en une colonne de ~110 px plus 7 titres de groupe), la section « vie » environ 1 500 px (3 cartes empilées) et le footer environ 1 100 px (4 blocs empilés). Les CTA « pros » et confiance arrivent après plus de 6 000 px de scroll.
- Corrections attendues :
  1. Catégories sur < sm : grille compacte en 2 colonnes. `home.blade.php:71` → `grid grid-cols-2 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4`. Carte `home.blade.php:75` → `flex-col items-start gap-2 p-3 sm:flex-row sm:gap-3 sm:p-4`. Icône `size-10 sm:size-12`. Libellé `text-base sm:text-lg`. Résumé `home.blade.php:81` → `hidden sm:block`. Titres de groupe `home.blade.php:68` → `text-lg`, et `mt-6` au lieu de `space-y-10` sur mobile (`space-y-6 sm:space-y-10`). Gain estimé : environ 1 700 px.
  2. Section « vie » sur mobile : `ol` `home.blade.php:108` → `flex snap-x snap-mandatory gap-4 overflow-x-auto -mx-4 px-4 pb-2 md:grid md:grid-cols-3 md:overflow-visible`. Chaque `li` → `w-[85%] shrink-0 snap-start md:w-auto`. Gain estimé : environ 900 px.
  3. Footer mobile : `footer.blade.php:5` → `grid grid-cols-2 gap-x-6 gap-y-8 … lg:grid-cols-4`, avec le bloc marque en `col-span-2 lg:col-span-1`. Gain estimé : environ 350 px.
- Objectif : moins de 5 500 px à 360 px.

### M6. Grille des catégories trouée sur desktop
- `home-fr-1280.png`, `categories-de-1280.png` : 7 groupes de 1 à 6 éléments dans une grille de 4 colonnes. Chaque groupe laisse 2 à 3 cellules vides (« Commerces » : 1 carte sur 4, « Toilettage », « Éducation », « Adoption », « Sorties » : 2 sur 4). La section fait environ 1 800 px de haut, avec un aspect « liste administrative » en escalier.
- Correction (au choix) :
  - a) Une seule grille `lg:grid-cols-4` des 20 catégories, groupe affiché en sur-titre dans chaque carte (`text-xs font-bold uppercase text-orange-dark`) ;
  - b) ou fusionner les petits groupes par paires dans une même rangée (`lg:grid-cols-2` de groupes, chaque groupe en `grid-cols-2`).
- Hauteur visée : 900 px maximum à 1280 px.

## MINEURS

### m1. Bouton « Créer un compte » sur deux lignes dans le menu mobile
- `menu-guest-360.png`. `header.blade.php:108` : `grid-cols-2` à 360 px coupe le libellé en deux lignes dans le bouton plein. Correction : `grid grid-cols-1 gap-3 min-[400px]:grid-cols-2`, ou `whitespace-nowrap` + `size="sm"`.

### m2. CTA d'inscription en double à 768 px
- `menu-guest-768.png` : « Créer un compte » apparaît dans le header (`header.blade.php:65`, `sm:block`) et dans le menu ouvert (`:110`). Correction : afficher le bloc login/inscription du menu en `sm:hidden` pour le bouton d'inscription, ou ne garder que « Se connecter » en `sm:` dans le menu.

### m3. Dropdowns `<details>` qui ne se ferment ni avec Échap ni au clic extérieur
- Sélecteur de langue (`language-switcher.blade.php:25`) et menu utilisateur (`header.blade.php:40`), `lang-dropdown-1280.png`, `user-dropdown-1280.png`. Le panneau reste ouvert par-dessus le hero.
- Correction (Alpine est déjà chargé) : sur le `<details>`, ajouter `x-data x-on:keydown.escape="$el.removeAttribute('open'); $el.querySelector('summary').focus()" x-on:click.outside="$el.removeAttribute('open')"`.

### m4. Le header sticky masque le contenu atteint au clavier et les ancres
- Visible sur `life-de-768.png` et `categories-de-1280.png` : le header de 64 à 80 px recouvre le haut de la zone ciblée. Le lien d'évitement `#main` et les éléments focalisés en haut d'écran passent dessous.
- Correction : `app.css` `@layer base html { scroll-padding-top: 6rem; }`.

### m5. Section « vie » et « confiance » trop serrées à 768 px
- `life-de-768.png`, `trust-de-768.png` : `md:grid-cols-3` donne des colonnes d'environ 200 px de texte (titres sur 2 lignes, 6 à 7 lignes de paragraphe, pastilles empilées).
- Correction : `home.blade.php:108` et `:145` → `lg:grid-cols-3` (une colonne ou le carrousel M5.2 en dessous).

### m6. Pas de récapitulatif d'erreurs sur les formulaires d'auth (incohérent avec le formulaire chien)
- `register-errors-360.png`, `login-errors-360.png` : le formulaire chien affiche `x-alert` « Certains champs sont à corriger » (`dogs/form.blade.php:16-18`). L'inscription et la connexion n'ont rien en tête de formulaire. Sur la page d'inscription mobile (2 572 px), la première erreur reste visible, mais les erreurs « conditions » arrivent sous la ligne de flottaison.
- Correction : ajouter le même bloc `@if ($errors->any()) <x-alert variant="error" :title="__('ui.form_has_errors')" class="mb-5" /> @endif` au début de `<x-auth-shell>` (`auth-shell.blade.php:33`), ce qui couvre toutes les pages d'auth d'un coup.

### m7. Alignement « Mot de passe oublié ? » sur la page de connexion
- `login-1280.png` : le lien est environ 8 px plus bas que « Rester connecté·e » (`items-center` sur le parent, mais la checkbox est en `items-start` avec `pt-0.5`).
- Correction : `login.blade.php:7` → `items-start`, et retirer `min-h-11` du lien au profit de `py-3` (ou aligner la checkbox en `items-center`).

### m8. Typographie française : « ! » orphelin
- `register-1280.png` : « J'ai un chien (ou j'en rêve » puis « !) » renvoyé à la ligne. `lang/fr/auth.php:34`.
- Correction : espace fine insécable (U+202F) avant `! ? ; :` dans `lang/fr/*.php`, par exemple `j’en rêve\u{202F}!)`.

### m9. Code « 404 » en orange sur crème : 2.10:1
- `err404-360.png`. `message-page.blade.php:244` : `text-orange`. Il est décoratif (`aria-hidden`), mais c'est le premier repère visuel de la page.
- Correction : `text-orange-dark` (5.86:1), ou conserver l'orange avec un `[-webkit-text-stroke:2px_var(--color-brown)]`.

### m10. Hiérarchie libellé/valeur inversée dans les fiches chien du tableau de bord
- `account-1280.png`, `account-360.png` : `dt` en `font-bold text-muted` et `dd` en `font-semibold`, même taille. « Âge / 5 ans » se lisent au même niveau.
- Correction : `dashboard.blade.php:106,117,121,125` → `dt` en `text-xs font-bold uppercase tracking-wide text-muted`, `dd` en `text-base font-bold text-brown-dark`.

### m11. Poids affiché « 18,50 » dans le formulaire chien
- `dog-edit-768.png`. `dogs/form.blade.php:37` reçoit le décimal brut (cast `decimal:2`).
- Correction : passer `:value="$dog->weight_kg !== null ? rtrim(rtrim($dog->weight_kg, '0'), '.') : null"`, ou caster en `float` dans le modèle.

### m12. Badge de rôle isolé dans le formulaire Profil
- `profile-360.png` : la pastille « Particulier » flotte entre le champ e-mail et le bouton « Enregistrer », comme s'il s'agissait d'un champ.
- Correction : `profile.blade.php:127-129` → la déplacer à côté du titre `h2` (`flex items-center justify-between`), hors du `<form>`.

### m13. Styles de champ dupliqués hors composant
- `dogs/form.blade.php:64,69` recopie à la main les classes de `x-input`. Toute correction de M1, M2 ou M3 doit donc être faite à deux endroits.
- Correction : extraire les classes dans une classe `@layer components { .field { … } }` dans `app.css`, réutilisée par `input`, `select`, `textarea` et les champs Alpine des vaccins.
