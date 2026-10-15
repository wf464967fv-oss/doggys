# Tour 4 - Garde-fou DESIGN & UX

Verdict : **ACCEPTÉ** (aucun BLOQUANT, aucun MAJEUR ; 4 défauts MINEURS à corriger au fil de l'eau).

Méthode : Edge, 360 et 1280 px, ~28 pages x 2 largeurs (fiche + formulaire d'avis vide/erreurs/étoiles clavier,
avis avec réponse pro, avis-verifies, contact (+erreurs), Mes avis, réclamer, ajouter-mon-entreprise, espace pro
(liste, édition, badge, avis), admin (tableau de bord, réclamations liste/détail, fiches, avis), mentions légales,
confidentialité, hors-ligne, parcours accueil → « 4000 » → fiche → avis). Aucun scroll horizontal de page (0 px
partout). Audit automatique des cibles < 44 px.

## Ce qui est bon (constaté)
- Formulaire d'avis : étoiles opérationnelles au clavier (flèches), focus brun épais très visible ; erreurs claires
  (bandeau rose + message par champ avec icône, bordure framboise) ; texte d'aide conservé.
- Focus visible sur fond brun (pied de page, `.on-dark`) : cadre orange net.
- Réponse du pro lisible (encadré distinct « Réponse de … »), état « Aucun avis » et message « avis déjà donné » soignés.
- Pages hors-ligne, légales, admin (détail réclamation : formulaires accepter/refuser colorés) cohérents avec le site.
- Parcours accueil → recherche → fiche → avis : fluide, ton rassurant, mêmes boutons/cartes/badges partout.

## Défauts

### MINEUR 1 - Onglets compte/pro rognés sans indice de défilement (360 px)
- Pages : `/fr/mon-compte/espace-pro` (et pages compte), 360 px. Le 3e onglet (« Administration »/icône) est coupé net à droite.
- `resources/views/components/account-header.blade.php:24` (`overflow-x-auto`). Idem `components/admin-layout.blade.php:38`.
- Correction : ajouter un dégradé de bord, p.ex. sur le `<nav>` `[mask-image:linear-gradient(to_right,#000_90%,transparent)] lg:[mask-image:none]`
  et `snap-x` ; ou `flex-wrap` sous `sm` : `flex-wrap sm:flex-nowrap`.

### MINEUR 2 - Liens texte < 44 px de haut
- `/fr/contact` : lien « Comment les avis sont vérifiés » 42 px (360) / 19 px (1280), `pages/contact.blade.php:43`
  → `class="link inline-flex min-h-11 items-center"`.
- `/fr/mon-compte/avis` : lien du nom de la fiche 24 px (360 et 1280) → ajouter `inline-flex min-h-11 items-center` au lien.
- `/fr/mentions-legales` + `/fr/confidentialite` : liens « Conditions / Confidentialité / Contact / Cookies » en ligne
  23-24 px, `pages/legal.blade.php:65` → `class="inline-flex min-h-11 items-center"` sur le `<a>` (séparateur « · » à garder hors du lien).
- Fiche : liens de titre des « autres adresses » ~22 px de haut (zone de clic de la carte à vérifier ; sinon
  `py-2.5`). Attributions Leaflet/OSM 12 px : tierces, à tolérer.

### MINEUR 3 - Cases à cocher natives 20-24 px (ajouter mon entreprise, admin)
- `/fr/ajouter-mon-entreprise` (categories[], is_mobile) et `/admin/claims/{id}` (bce_verified), 360 et 1280.
- `components/checkbox.blade.php:18` : la ligne entière est un label (zone ≥ 44 px visuellement), donc OK en pratique ;
  vérifier que le `<label>` porte bien `min-h-11` partout (`flex min-h-11 items-center gap-3`). Le `<input>` lui-même
  peut passer à `size-6`.

### MINEUR 4 - Barre d'actions collante sur la fiche (360 px) devant le formulaire d'avis
- `/fr/pro/{slug}` connecté, après erreurs de validation : la barre « Appeler / Itinéraire » recouvre le bas
  du formulaire (texte « Votre avis sera publié… », bouton « Publier mon avis » proche de la barre).
- Correction : réserver de la place sous le contenu, p.ex. `pb-24 lg:pb-0` sur le `<main>`/conteneur de la fiche, et
  `scroll-mb-24` sur le formulaire d'avis ; ou masquer la barre quand le formulaire a le focus (`focus-within`).

## Non reproduit / hors périmètre
- Contraste : textes orange uniquement sur brun ou en gras/grand ; aucun défaut relevé.
- Réclamation en erreur : le bouton d'envoi n'a pas été cliquable par le script (champ BCE requis côté navigateur,
  validation HTML5 native) ; états d'erreur serveur non capturés sur cette page.
