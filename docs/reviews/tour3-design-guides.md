# Revue DESIGN & UX, tour 3 : lot GUIDES

Build : `npm run build` OK. La base locale ne contenait aucun guide (index en état vide). J'ai donc travaillé sur une **copie** de la base dans mon dossier temporaire, avec `migrate` + `doggys:import-guides` (52 articles), servie sur le port 8125. La base du projet n'a pas été touchée.

Pages : index `/fr/guides`, `/nl/gidsen`, `/en/guides`, `/de/ratgeber`, et 6 articles (FR senior, FR urgences, DE adoption chiot (le plus long, 1 184 mots), DE senior, NL urgences, EN alimentation) à 360, 768 et 1280 px.
Captures : `storage/app/screenshots/review-design/tour3/` (`s-*` = tranches de page). Mesures dans `tour3/_report.json`. Aucun scroll horizontal, aucun ID dupliqué, fil d'Ariane à 44 px (corrigé depuis le tour 2).

## Verdict : À CORRIGER

La typographie de lecture est bonne : 17 à 18 px, interligne 1.75, rythme H2/H3 net, encadrés « Bon à savoir » réussis, sources officielles en fin d'article. Mais il y a 4 MAJEURS : focus invisible sur deux composants, sommaire mobile, liens vers des catégories vides, et une phrase néerlandaise fautive dans le guide d'urgence.

---

## MAJEURS

### G1. Focus clavier invisible sur toutes les cartes de guide
- Index et « guides liés », toutes largeurs : `focus-card-1280.png`, carte focalisée au clavier sans aucun contour visible.
- Cause : `components/guide-card.blade.php:80` dessine le focus sur `after:` avec `focus-visible:after:outline-offset-2`, donc **à l'extérieur** du pseudo-élément. Or la carte est en `overflow-hidden` (`:69`, pour arrondir le bandeau coloré). Le contour est rogné (`cardOverflow=hidden` mesuré). WCAG 2.4.7.
- Correction : `focus-visible:after:outline-offset-[-3px]` (contour à l'intérieur), ou retirer `overflow-hidden` de la carte et mettre `rounded-t-3xl` sur le bandeau (`:70`).

### G2. Focus invisible dans le bloc « Trouver un pro » (fond brun)
- `focus-findpro-1280.png` : premier lien focalisé (« Toiletteur à domicile ») sans contour perceptible. L'outline global `outline-brown` est posé sur `bg-brown` (1:1). C'est le même défaut que M3 au tour 1, corrigé seulement pour le footer et le bandeau de l'accueil.
- `guides/show.blade.php:92` : ajouter `[&_:focus-visible]:outline-orange` sur la `<section>`.
- Mieux, pour ne plus avoir à y penser : créer dans `app.css` une utilité `.on-dark { & :focus-visible { outline-color: var(--color-orange); } }` et l'appliquer à tout bloc `bg-brown*`.

### G3. Sommaire ouvert par défaut sur mobile : mauvais choix, l'article commence au 2e écran
- `s-adopt-de-360-0.png`, `g-senior-fr-360.png` : le `<details open>` (`guides/show.blade.php:60`) occupe 434 à 578 px. Le premier paragraphe arrive à **1 203 px** (DE), **1 310 px** (FR senior) et **1 339 px** (DE senior) sur un écran de 780 px (`_report.json` → `articleTop`). Sur le premier écran, le lecteur ne voit que le titre et l'accroche, puis une liste de liens.
- Jugement : sur mobile, le sommaire doit être **fermé par défaut** (une ligne de 64 px « Dans ce guide · 9 parties ⌄ ») ; sur desktop, ouvert et sticky.
- Correction :
  - retirer `open` côté serveur ;
  - ajouter `x-data x-init="if (matchMedia('(min-width: 1024px)').matches) $el.open = true"` sur le `<details>` ;
  - afficher le nombre de parties dans le `summary` (`trans_choice`).
- Au passage, `lg:pointer-events-none` (`:61`) bloque la souris mais pas le clavier : Entrée replie quand même le sommaire sur desktop. Ajouter `lg:hidden` au chevron (déjà présent) et `x-on:click.prevent` sur le `summary` quand on est sur desktop, ou mettre `tabindex="-1"` sur le `summary` à partir de `lg`.

### G4. « Trouver un pro » renvoie vers des catégories vides
- `focus-findpro-1280.png` (guide FR senior) : sur 5 tuiles, **3 mènent à des pages « Bientôt »** (Toiletteur à domicile, Kiné & physiothérapie, Hydrothérapie). Dans le guide DE adoption, « Anerkannter Züchter » mène aussi à 0 fiche (`s-adopt-de-360-7.png`). Le bloc promet « des professionnels de confiance partout en Belgique » puis livre des pages vides.
- `GuideController.php:85` : ajouter `->withCount('providers')`, ne garder que `providers_count > 0` et trier par nombre décroissant. Masquer le bloc s'il reste moins de 2 tuiles.

### G5. Faute de néerlandais dans le guide d'urgence
- `database/data/guides/emergencies/nl.md:28` (`s-emerg-nl-360-2.png`) : « blauwachtige of heel bleke tandvlees of tong ». *Tandvlees* est un nom neutre (*het*) : l'adjectif ne prend pas de -e. Il faut écrire « **blauwachtig of heel bleek tandvlees** of een blauwe/bleke tong ». C'est le guide où la précision compte le plus.

## MINEURS

1. **Mesure de ligne un peu longue sur desktop** : environ 82 à 86 caractères par ligne pleine (`.prose-doggys` à 734 px de large pour 18 px, mesure sur `g-senior-fr-1280.png`). L'idéal pour un texte long se situe entre 60 et 75. `app.css` `.prose-doggys` : `max-w-[68ch]` → `max-w-[36rem]` (environ 70 caractères en Nunito 18 px).
2. **Pas de césure** : l'allemand et le néerlandais à 360 px (39 caractères par ligne) donnent un drapeau irrégulier, avec des lignes très courtes après les mots longs (« Unvorhergesehenes », « Krankenversicherung », `s-adopt-de-360-2.png`). Il n'y a pas de débordement. `.prose-doggys` : ajouter `hyphens-auto` (efficace sur Android et Safari, sans effet sur Edge Windows).
3. **Date « Mis à jour le 15 octobre 2026 » identique sur tous les guides** : `updated_at` vient de la date d'import (`show.blade.php:3`) et changera à chaque réimport. Ajouter un champ `updated_at` au front-matter, avec repli sur `published_at`, et l'utiliser ici et dans le JSON-LD `dateModified`.
4. **Confiance** : l'index annonce « relus avec soin » (`blog.subtitle`), mais aucun article ne dit par qui (`g-senior-fr-1280.png`). Ajouter une ligne d'en-tête `Rédaction Doggys · Sources : N références officielles` avec un lien vers `#sources-officielles`, à côté du temps de lecture (`show.blade.php:49-52`). N'ajouter un nom de relecteur que s'il existe réellement.
5. **Guide d'urgence sans action immédiate** : en cas d'urgence, le lien vers un vétérinaire n'apparaît qu'en fin d'article, après environ 10 000 px sur mobile. Pour `translation_key = emergencies` uniquement, insérer sous l'accroche un encadré `x-alert variant="error"` avec un bouton « Trouver un vétérinaire autour de moi » (`localized_route('search', ['category' => 'veterinaire', 'near_me' => 1])`).
6. **Index trop long sur mobile** : 8 109 px (FR) pour 13 guides (`idx-fr-360.png`). Le bandeau décoratif de 112 px par carte (`guide-card.blade.php:70`) n'apporte aucune information ; à lui seul, il représente environ 1 450 px. Correction : `h-20 sm:h-28` et icône `size-12 sm:size-16`, ou une carte horizontale sous `sm`. Le sur-titre de catégorie dans la carte (`:78`) répète le H2 du groupe juste au-dessus : le masquer sur l'index (prop `showCategory=false`) et le garder dans « guides liés ».
7. **Index en groupes à moitié vides sur desktop** (même schéma que M6 et N7) : groupes de 4, 3, 2, 3 et 1 cartes dans une grille de 3 colonnes (`idx-fr-1280.png`), dont « Démarches & règles » avec une seule carte sur une ligne vide. Pour 13 guides, il faut une grille continue avec la catégorie en sur-titre, en gardant les ancres des pastilles de thème comme filtres.
8. **Fil d'Ariane mobile** : le dernier élément répète le titre complet sur 2 lignes juste au-dessus du H1 (`s-adopt-de-360-0.png`). `components/breadcrumbs.blade.php` : `max-sm:hidden` sur le dernier `<li>`, ou `truncate max-w-[12rem]`.
9. **Terminologie DE incohérente** : « Auszug aus dem **Zentralregister** » dans le texte (`adopt-puppy/de.md:67`), mais « Auszug aus der **Zentraldatei** » dans les sources (`s-adopt-de-360-7.png`). Dans la même phrase, « die sogenannte Haltererlaubnis … Sie beantragen **ihn** » : le pronom renvoie à *Auszug* alors que le dernier nom cité est *Haltererlaubnis* (féminin). Reformuler : « Diesen Auszug beantragen Sie bei Ihrer Gemeinde ».
10. **Guides liés sur mobile** : 3 cartes de 470 px chacune (`s-adopt-de-360-8.png`). Même correction que le point 6, ou un carrousel `snap-x` comme sur l'accueil.
