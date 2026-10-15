# Revue CODE & SÉCURITÉ — tour 2 (contre-vérification Fondations + lot ANNUAIRE)

**Verdict : À CORRIGER** (1 bloquant, 2 majeurs, 7 mineurs)

Contrôles :
- `php artisan test` : 84/86. Les 2 échecs sont dans `GuideTest`, hors périmètre comme demandé.
- `pint --test` : propre.
- `vite build` : OK (sortie dans le scratchpad). Leaflet est isolé dans un chunk `map-*.js` chargé à la demande.
- Les sondes ont été écrites en tests temporaires dans le scratchpad, hors du dépôt. J'ai lu le code à HEAD (`git show HEAD:`) car `routes/web.php` est modifié par l'agent Guides.

## A. Contre-vérification des 14 points du tour 1

| # | Statut | Preuve |
|---|---|---|
| 1 Messages de contact non effacés | VALIDÉ | `AccountController.php:158` `contactMessages()->delete()`. Sonde : la ligne est supprimée (le `refresh()` lève `ModelNotFound`). |
| 2 Changement d'e-mail qui bloque l'accès | VALIDÉ | `pending_email` + lien signé `account.email.verify` (`routes/web.php:97`, `AccountController::confirmEmail`). L'ancienne adresse reste active. Couvert par `EmailChangeTest`. |
| 3 E-mail modifiable sans mot de passe | VALIDÉ | `UpdateUserProfileInformation.php:43` exige `current_password` quand l'e-mail change. Sonde : l'adresse n'est pas changée sans mot de passe. L'ancienne adresse est prévenue (`EmailChangeRequested`). |
| 4 Pas de limite par IP au login | VALIDÉ | `FortifyServiceProvider.php:57-60` ajoute une limite de 20/min par IP. Sonde : 429 après 20 adresses différentes. |
| 5 CSP sandbox écrasée | VALIDÉ | `SecurityHeaders.php:27`. Sonde : `default-src 'none'; style-src 'unsafe-inline'; sandbox` est conservé. |
| 6 Droits RGPD inaccessibles sans vérification | VALIDÉ | Profil, export et suppression sont désormais sous `auth` seul (`routes/web.php:93-105`). |
| 7 Sessions et jetons de réinitialisation conservés | VALIDÉ | `AccountController.php:159-160`, dans une transaction. |
| 8 Badge conservé sur une fiche orpheline | VALIDÉ | `AccountController.php:153-156` remet `verification_level=none` et `plan=free`. |
| 9 Brute-force du mot de passe courant | VALIDÉ | Limiteur `account-sensitive` (6/min par utilisateur et par route). Sonde : 429 atteint. |
| 10 Chiens illimités | VALIDÉ | `Dog::MAX_PER_USER = 20` (`DogRequest::after`) + `throttle:dogs` à 30/min. |
| 11 Export incomplet | VALIDÉ | `claimed_providers` et `review_reports` sont ajoutés (`AccountController.php:82-93`). |
| 12 Turnstile incohérent | VALIDÉ | `services.turnstile.enabled` exige les 2 clés et sert au widget, au middleware et à la CSP. |
| 13 Configuration de production | VALIDÉ | `session.secure` vaut true par défaut en production, `LOG_STACK=daily` + 14 jours, notes de production dans `.env.example`. |
| 14 Tests manquants | VALIDÉ | Tests ajoutés : réinitialisation complète, changement d'e-mail, mauvais hash ou autre utilisateur, limite par IP, sandbox CSP, plafond de chiens. |
| Remarque sur les preuves de réclamation | TOUJOURS EN DÉFAUT (latent) | `Claim::deleting` (`Claim.php:32-37`) ne couvre que les suppressions Eloquent. `claims.provider_id` reste en `cascadeOnDelete` : un futur `Provider::forceDelete()` passera par la cascade SQL sans effacer les fichiers de preuve. Aucun code ne fait encore de `forceDelete`, mais le lot admin doit supprimer les claims via Eloquent avant (`$provider->claims->each->delete()`) ou ajouter un hook `forceDeleting` sur `Provider`. |

## B. Lot ANNUAIRE (37a8364, fa834f3)

Vérifiés sans défaut :
- JSON-LD : `JSON_HEX_TAG` est présent. Sonde avec un nom `</script><script>alert(1)</script>` sur la fiche, la page ville et la page catégorie : pas de sortie brute.
- Popups Leaflet construites via `textContent`.
- `data-markers` passé dans `{{ }}`.
- Fiches `hidden`, `removed` et soft-deleted : 404 sur le web, sur `/api/v1/providers/{slug}`, et absentes des listes. Elles sont indiscernables d'un slug inconnu (404).
- Validation de l'API : 422 pour `lat=95`, `radius=500`, `page=-1`, `page=1e9`, les tableaux en paramètres et `location` de plus de 100 caractères.
- Pas d'injection SQL : tout passe par le query builder, et le `like` de `places` n'accepte que des chiffres.
- SQL portable : aucune fonction propre à un moteur.
- Import rejouable sur `(source, source_ref)`, slugs uniques en tenant compte des fiches soft-deleted, fiches revendiquées ou retirées protégées.
- `providers.json` actuel : 1 946 lignes, aucune valeur trop longue pour sa colonne, aucun `website` non-http, aucune clé en double.

### BLOQUANT

1. **La recherche géographique ne tient pas la cible de 50 000 fiches (temps et mémoire)** — `app/Services/ProviderSearch.php:52-76`, `app/Http/Controllers/CategoryController.php:40,56-60`, `app/Http/Controllers/ProviderController.php:34`.
   `nearby()` hydrate en PHP **toutes** les fiches de la boîte, catégories comprises. La branche `is_mobile` charge en plus tous les pros mobiles dans une boîte fixe de ±100 km, quel que soit le rayon demandé. Le tri et la pagination se font ensuite en mémoire. De même, la page catégorie charge toutes les fiches de la catégorie avec `->get()`, et la page ville appelle `nearby()` **sans** catégorie puis filtre en PHP.
   Mesuré sur SQLite avec 50 000 fiches (moitié autour de Bruxelles, 25 % de mobiles) :

   | Requête | Temps | Pic mémoire |
   |---|---|---|
   | `/fr/recherche` à 10 km | 10,7 s | 413 Mo |
   | `/fr/recherche` à 50 km | 12,8 s | 495 Mo |
   | `/api/v1/providers` à 50 km | 12,5 s | — |
   | `/fr/veterinaire/bruxelles` | 12,1 s | — |
   | `/fr/veterinaire` | 2,8 s | — |
   | `/fr/veterinaire?page=400` | 2,0 s | — |

   À 2 000 fiches : environ 0,3 à 0,4 s par recherche, déjà coûteux sur un mutualisé (voir n°2). Ces chiffres dépassent `max_execution_time` et `memory_limit` d'un mutualisé o2switch : erreurs 500 ou 503 dès quelques milliers de pros mobiles.
   Correction :
   - (a) Filtrer le rayon de service des mobiles en SQL avec une boîte par ligne : `lat BETWEEN ? - COALESCE(service_radius_km, ?)/111.045 AND …`, et même chose pour `lng` avec le facteur `1/(111.045·cos lat0)` calculé en PHP. C'est de l'arithmétique portable.
   - (b) Trier et paginer en SQL : `ORDER BY (lat-?)*(lat-?) + ((lng-?)*k)*((lng-?)*k)` + `paginate(20)`. Eager-loader les catégories seulement pour la page affichée. Calculer la haversine uniquement pour l'affichage.
   - (c) Page catégorie : `->paginate(24)` en SQL, et villes agrégées par `GROUP BY postal_code, city`.
   - (d) Page ville : passer `$category` à la requête SQL. Calculer `otherCategories` par un `COUNT` groupé sur `category_provider`.
   - (e) « Similaires » sur la fiche : `LIMIT 4` en SQL.
   - Ajouter un test qui borne le nombre de lignes hydratées, par exemple en vérifiant via `DB::listen` que la requête porte un `limit`.

### MAJEUR

2. **Recherche web et pages catégorie/ville sans aucun rate limit** — `routes/web.php:41-45` (HEAD).
   Sonde : 150 requêtes `/fr/recherche?lat=50.63&lng=5.56&radius=50` depuis une même IP, toutes en 200. Compte tenu du coût du n°1, un seul client anonyme peut monopoliser le CPU et la RAM du compte mutualisé ; l'API, elle, est limitée à 60/min.
   Correction : un limiteur nommé `search` (par exemple 30/min par IP) sur `search`, `categories.show` et `categories.city`. Ajouter un test de 429.

3. **Les `throttle:N,M` anonymes partagent un seul compteur par IP : l'autocomplétion bloque le signalement** — `routes/web.php:48` (`throttle:5,60`), `routes/api.php:10,16` (`throttle:60,1`, `throttle:120,1`).
   `ThrottleRequests` utilise la même clé `sha1(domaine|ip)` pour toutes les routes sans limiteur nommé. Sonde : 6 appels à `/api/v1/places?q=li` (une recherche tapée normalement), puis `POST /fr/pro/{slug}/signaler`, donne **429**. Un visiteur qui vient d'utiliser l'autocomplétion ne peut plus signaler une erreur ni demander une suppression RGPD. L'inverse est vrai aussi : les appels à `places` consomment le quota de `/api/v1/providers`.
   Correction : des limiteurs nommés avec une clé propre à chaque usage, par exemple `RateLimiter::for('provider-report', fn ($r) => Limit::perHour(5)->by('report|'.$r->ip()))`, et de même pour `api` et `places`. On peut aussi passer le 3ᵉ paramètre de préfixe (`throttle:5,60,report`). Ajouter un test qui reproduit la séquence autocomplétion puis signalement.

### MINEUR

4. **`/api/v1/places?q[]=1` répond 500** — `app/Http/Controllers/Api/ProviderController.php:59`. Le cast `(string) $request->query('q')` sur un tableau lève « Array to string conversion », ce qui pollue les logs et donne une 500 publique. Correction : `$request->string('q')`, ou valider `q` en `nullable|string|max:60`.

5. **`href` du site web non filtré par schéma** — `resources/views/directory/provider.blade.php:133` et `sameAs` ligne 17. Sonde : `website = 'javascript:alert(document.cookie)'` produit `href="javascript:…"`. La CSP actuelle bloque l'exécution, mais l'API transmet la valeur telle quelle à la future appli mobile, et les pros pourront bientôt éditer ce champ. Correction : n'accepter que `http(s)://` à l'import (`ImportProviders::importRow`) et à l'édition (`url:http,https`). Au rendu, n'afficher le lien que si `Str::startsWith($website, ['http://', 'https://'])`.

6. **L'import ne valide pas les lignes** — `app/Console/Commands/ImportProviders.php:93-97`. Une valeur `region` hors enum lève une `ValueError`. Une chaîne plus longue que sa colonne (`phone` 32, `postal_code` 4, `house_number` 16…) déclenche une erreur stricte sous MySQL, alors que SQLite l'accepte en silence. Dans les deux cas, toute la transaction est annulée et l'import entier échoue en production seulement. Correction : passer chaque ligne par `Validator::make` (longueurs, `Rule::enum(Region::class)`, `lat between:49,52`, `lng between:2,7`, `url:http,https`) et compter les lignes rejetées en `invalid`.

7. **L'import ne retire jamais une fiche disparue de la source** — `ImportProviders.php:48-52`. Un commerce fermé (supprimé d'OSM ou du registre) reste `active` indéfiniment. Correction : après la boucle, passer en `hidden` les fiches `source != manual`, non revendiquées, dont `source_ref` est absent du fichier, et les compter dans le résumé.

8. **Cache des villes non invalidé au reseed** — `app/Models/PostalCode.php:57` (TTL 1 jour, clé figée `v2`). `PostalCodeSeeder` n'appelle pas `Cache::forget(PostalCode::CITY_INDEX_CACHE_KEY)` : après une mise à jour des codes postaux en production, les pages ville et l'autocomplétion servent l'ancien index pendant 24 h. Correction : un `Cache::forget` à la fin du seeder et de la commande d'import.

9. **Couverture de tests incomplète** — `tests/Feature/Directory*Test.php`, `ImportProvidersTest.php`. Rien ne couvre :
   - l'échappement `</script>` du JSON-LD ;
   - les 422 de l'API (bornes lat/lng, rayon, tableaux) ;
   - l'absence des fiches `hidden`/`removed` dans l'API liste ;
   - la collision de slugs à l'import (deux pros homonymes dans la même ville → `-2`) ;
   - les lignes invalides de l'import ;
   - le 404 d'une page ville sans pro ;
   - le rate limit de la recherche.

   Correction : un test par point.

10. **(rappel du point A) Preuves de réclamation orphelines en cas de `forceDelete` de fiche** — voir le tableau A, à traiter au plus tard dans le lot admin.
