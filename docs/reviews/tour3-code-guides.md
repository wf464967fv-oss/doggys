# Revue CODE & SÉCURITÉ — tour 3, lot GUIDES (7e36394, 30df286)

**Verdict : À CORRIGER** (1 bloquant, 1 majeur, 4 mineurs)

Contrôles :
- `GuideTest` : 11/11 vert dans l'arbre de travail.
- Le code a été lu à `30df286` / HEAD. Les sondes sont des tests temporaires dans le scratchpad, hors du dépôt.

Vérifiés sans défaut :
- **XSS Markdown**, 21 vecteurs testés via `Post::renderBody()`. Tous sont neutralisés :
  - `javascript:` en clair, en casse mixte, encodé en entité `&#106;`, en lien de référence ou en autolink `<javascript:…>` : `<a>` sans `href` ;
  - `data:` et `vbscript:` : `<a>` sans `href` ;
  - `![x](javascript:…)` : `src=""` ;
  - HTML brut (`<script>`, `<img onerror>`, `<a href=javascript:>`, `<iframe>`) : supprimé ;
  - guillemets cassés dans `title` ou `alt` : échappés ;
  - `<img>` dans un `##` : supprimé, et l'`id` est un slug ;
  - `java%73cript:` : reste un `href` relatif inerte, les navigateurs ne décodent pas le schéma.
  - Les liens externes reçoivent `rel="noopener noreferrer"`. Le contenu vient de fichiers du dépôt, pas d'utilisateurs.
- **JSON-LD Article** : `JSON_HEX_TAG`. Titre, extrait et entrées de sommaire passent par `{{ }}`. Le seul `{!! !!}` porte sur la sortie assainie de CommonMark (`show.blade.php:80`).
- **Route** : contrainte `[a-z0-9-]+`. Un slug en majuscules, un brouillon, un guide programmé ou une autre langue donnent 404 (`firstOrFail`).
- **Requêtes** : pas de N+1. L'index fait 1 requête, l'article 5, en 30 ms. Le rendu Markdown à chaque requête est négligeable : un cache n'est pas justifié aujourd'hui.
- **Import** : idempotent sur `(locale, translation_key)`. `updated_at` ne bouge que si le contenu change, donc `dateModified` reste juste. CRLF est géré. Les deux-points dans une valeur entre guillemets sont gérés.
- **Migration** `related_categories` : `json` nullable, portable (le `after()` est ignoré par SQLite et accepté par MySQL).
- `orderByRaw('case when category = ? …')` est lié et portable.
- `GuideTest::test_every_guide_file_is_complete…` vérifie, pour chaque fichier versionné : format du slug, catégorie connue, `related_categories` existantes, longueurs SEO, présence des 4 langues.

## BLOQUANT

1. **`x-breadcrumbs` est utilisé mais absent du dépôt** — `resources/views/guides/index.blade.php:8`, `resources/views/guides/show.blade.php:36`, ainsi que `directory/categories.blade.php:5`, `category.blade.php:31`, `city.blade.php:55` et `provider.blade.php:55` (lot Annuaire).
   `git ls-files resources/views/components/breadcrumbs.blade.php` ne renvoie rien à HEAD : le fichier n'existe que dans l'arbre de travail. Un `git clone` ou un déploiement de HEAD rend `/fr/guides`, chaque guide, et toutes les pages catégorie, ville et fiche en **500** (« Unable to locate a class or view for component [breadcrumbs] »). Les tests ne passent que grâce au fichier non commité.
   Correction : commiter `resources/views/components/breadcrumbs.blade.php` (l'agent Annuaire est dessus d'après le coordinateur). Le lot ne sera validé qu'après vérification de `git ls-files` sur ce chemin.

## MAJEUR

2. **Un guide supprimé du disque reste publié indéfiniment** — `database/seeders/GuideSeeder.php:16-26`.
   Sonde : un guide `fr` en base, absent de `database/data/guides`, est conservé après `doggys:import-guides` et sa page répond toujours 200. Il n'existe aucune admin des posts. Retirer un guide (erreur médicale, demande juridique) impose donc un SQL manuel sur l'hébergement mutualisé. Il en va de même pour une seule langue retirée. Renommer un dossier fait pire : `translation_key` change, l'ancienne ligne garde son slug, et l'insertion de la nouvelle viole l'index unique `(locale, slug)`, ce qui fait échouer l'import.
   Correction : à la fin de `run()`, supprimer (ou dépublier en `published_at = null`) les posts dont la paire `(locale, translation_key)` n'a pas été vue pendant l'import, et afficher ce nombre. Ajouter un test.

## MINEUR

3. **Le parseur échoue sur un BOM UTF-8 ou des fins de ligne CR seules** — `GuideSeeder.php:35-37`.
   Sonde : un fichier enregistré « UTF-8 avec BOM » (le défaut de certains éditeurs Windows) ou en CR seul lève « Missing front-matter », alors que le fichier est valide.
   Correction : `$raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw)` et normaliser `\r\n?` en `\n`.

4. **L'import n'est ni atomique ni validé** — `GuideSeeder.php:18-25`, `parse()` lignes 49-69.
   - Une date illisible (`published_at: demain midi`) lève une `InvalidFormatException` au milieu de la boucle. Les fichiers déjà traités sont écrits, les suivants non : la base reste dans un état mélangé.
   - `parse()` accepte un slug invalide (`Mon Slug!`, inatteignable avec la contrainte de route mais publié), une catégorie inconnue (le guide n'apparaît dans aucun groupe de l'index et sa page affiche la clé brute `blog.categories.xxx`), une locale arbitraire (tout `*.md`, par exemple `xx.md` ou `README.md`, devient un post) et des `related_categories` inconnues.
   - Le test de fichiers protège les guides actuels, pas un fichier ajouté sous un autre nom ni un import lancé sans passer les tests.

   Correction : un `DB::transaction` autour de la boucle ; dans `parse()`, valider la locale (`in_array(config('app.locales'))`), le slug (`^[a-z0-9]+(-[a-z0-9]+)*$`), `category` (clé de `GuideController::CATEGORIES`) et la date, puis lever une `RuntimeException` explicite.

5. **Code mort de transition dans la vue article** — `resources/views/guides/show.blade.php:4-6,113`. Les tests `Route::has(...categories.show)` et `Route::has(...categories.index)` avec repli sur la recherche datent d'avant le lot Annuaire. Ces routes existent désormais : supprimer les branches de repli et appeler directement `localized_route('categories.show', …)` et `localized_route('categories.index')`.

6. **Couverture de tests** — `tests/Feature/GuideTest.php`. Non couverts :
   - la suppression ou dépublication des guides retirés du disque (après le n°2) ;
   - le BOM et le CRLF dans `parse()` ;
   - le rejet d'un front-matter invalide (slug, catégorie, locale, date) ;
   - l'échappement JSON-LD avec un titre contenant `</script>` ;
   - les vecteurs `data:`, entité `&#106;` et lien de référence `[r]: javascript:`.

   Correction : ajouter ces cas, avec des fichiers temporaires pour `parse()`.
