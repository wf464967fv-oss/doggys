# Revue CODE & SÉCURITÉ — lot « Fondations » (759d6dd..73c19eb)

**Verdict : À CORRIGER** (0 bloquant, 5 majeurs, 9 mineurs)

Contrôles passés : `php artisan test` 42/42 vert, `pint --test` propre, `vite build` OK (build dans un dossier scratch). Vérifiés sans défaut : IDOR chiens (403 via `->can()`, 404 si absent), export limité à l'utilisateur courant + `password.confirm`, mass assignment (`role`, `verification_level`, `status`, `proof_path` non fillables, rôle admin refusé à l'inscription), clés imbriquées non validées de `vaccinations` bien retirées par `validated()`, `{!! !!}` seulement sur des chaînes de traduction et des SVG statiques, CSRF, réinitialisation de mot de passe sans énumération, lien de vérification signé + lié à l'utilisateur connecté, `/storage/{path}` signé, index utf8mb4 < 3072 octets, pas de N+1, pas de worker permanent requis.

## MAJEUR

1. **La suppression de compte RGPD laisse les messages de contact de l'utilisateur** — `database/migrations/2026_10_15_100010_create_contact_messages_table.php:16` (`nullOnDelete`) + `app/Http/Controllers/AccountController.php:88-104`.
   Vérifié : après `DELETE /fr/mon-compte`, la ligne `contact_messages` existe toujours avec `user_id = NULL` mais garde `name`, `email` et `message`. L'effacement est incomplet alors que le docblock du contrôleur le présente comme complet.
   Correction : supprimer ces lignes à la suppression du compte. Soit `cascadeOnDelete()` sur la FK, soit `$user->contactMessages()->delete()` dans `destroy()`. Ajouter l'assertion dans `AccountTest::test_user_can_delete_their_account_and_data`.

2. **Changer d'e-mail peut bloquer définitivement l'accès à l'espace compte** — `routes/web.php:79-86` + `app/Actions/Fortify/UpdateUserProfileInformation.php:35-59`.
   Le changement remet `email_verified_at` à null. Or tout le groupe `account.*` (profil, export, suppression, chiens) est derrière `verified`. Si l'utilisateur fait une faute de frappe dans la nouvelle adresse, il ne peut plus ouvrir son profil pour la corriger, ni exporter, ni supprimer son compte. Le lien de vérification et la réinitialisation du mot de passe partent vers l'adresse erronée.
   Correction : garder l'ancienne adresse active tant que la nouvelle n'est pas vérifiée. Concrètement, une colonne `pending_email` avec le lien de vérification envoyé à la nouvelle adresse, et l'échange au clic. À défaut, sortir `account.profile`, `account.profile.update`, `account.export` et `account.destroy` du middleware `verified` (garder seulement `auth`).

3. **L'e-mail du compte se change sans redemander le mot de passe** — `routes/web.php:82`, `app/Actions/Fortify/UpdateUserProfileInformation.php:21-44`.
   Vérifié : `PUT /fr/mon-compte/profil` avec `email=attacker@evil.test` donne une 302 et l'e-mail est remplacé. Une session volée ou un poste laissé ouvert suffit à prendre le compte : l'attaquant change l'e-mail, puis fait une réinitialisation du mot de passe. L'ancienne adresse n'est pas prévenue.
   Correction : exiger `current_password` quand l'e-mail change (règle `required_unless` / `current_password:web`), ou placer la route sous `password.confirm`. Envoyer une notification à l'ancienne adresse. Ajouter un test.

4. **Aucune limite par IP sur la connexion (attaque par pulvérisation de mots de passe)** — `app/Providers/FortifyServiceProvider.php:53-57`.
   Le limiteur est indexé seulement sur `email|ip`. Vérifié : 30 tentatives depuis une même IP sur 30 adresses différentes ne sont jamais bloquées (aucune 429). Une IP peut donc tester un mot de passe courant sur toute la base.
   Correction : renvoyer deux limites, `[Limit::perMinute(5)->by($emailIp), Limit::perMinute(20)->by($request->ip())]`, et ajouter un test.

5. **`SecurityHeaders` écrase la CSP « sandbox » des fichiers privés servis** — `app/Http/Middleware/SecurityHeaders.php:26`.
   Vérifié : une URL signée `storage.local` vers un fichier `.html` renvoie 200 en `text/html`. La CSP `default-src 'none'; sandbox` posée par `ServeFile` est remplacée par la CSP du site (scripts `'self'` autorisés, plus de sandbox). Les preuves de réclamation (disque `local`) seront ouvertes par l'admin dans son origine authentifiée.
   Correction : ne poser la CSP (et `X-Frame-Options`) que si la réponse n'en a pas déjà une : `if (! $headers->has('Content-Security-Policy')) {...}`. Ajouter un test sur une URL `temporaryUrl()`.

## MINEUR

6. **Un compte non vérifié ne peut ni exporter ni supprimer ses données** — `routes/web.php:79`. Les droits RGPD dépendent de la réception d'un e-mail, et aucun job ne purge les comptes jamais vérifiés. Correction : rendre `account.destroy` (et le profil) accessibles avec `auth` seul ; ce point est réglé en même temps que le n°2.

7. **Effacement incomplet des traces techniques** — `app/Http/Controllers/AccountController.php:94-103`. Les lignes `sessions` (IP, user-agent, `user_id`) des autres appareils et `password_reset_tokens` (e-mail) restent en base. Correction : `DB::table('sessions')->where('user_id', $user->id)->delete()` et `Password::broker()->deleteToken($user)` (ou un delete par e-mail) dans `destroy()`, le tout dans une transaction avant la suppression des fichiers.

8. **Un compte pro supprimé laisse une fiche « vérifiée » sans propriétaire** — `database/migrations/2026_10_15_100001_create_providers_table.php:37` (`nullOnDelete`). `claimed_by_user_id` passe à null, mais `verification_level` (`verified` / `regulated`) et `plan` restent. Le badge « Pro vérifié » continue de s'afficher alors que l'identité contrôlée n'est plus liée à rien. Correction : dans `destroy()`, avant la suppression, faire `forceFill(['verification_level' => 'none', 'plan' => 'free'])` sur `claimedProviders`.

9. **Brute-force du mot de passe courant sans limite** — `routes/web.php:83,86`. Vérifié : 30 essais `DELETE /fr/mon-compte` et 30 essais `PUT …/mot-de-passe` avec un mauvais mot de passe, aucune 429. Avec une session volée, on peut deviner le mot de passe sans limite. Correction : `->middleware('throttle:6,1')` sur `account.password.update` et `account.destroy`.

10. **Création de chiens illimitée** — `routes/web.php:89`, `app/Http/Controllers/DogController.php:21-26`. Aucun plafond ni throttle : un compte vérifié peut créer des milliers de lignes (chacune avec 50 vaccins et 2000 caractères de notes). Correction : un plafond métier (par exemple 20 chiens par compte, contrôlé dans `DogRequest` ou dans le contrôleur) et `throttle:30,1` sur les routes chiens en écriture.

11. **L'export RGPD est incomplet** — `app/Http/Controllers/AccountController.php:35-75`. Il manque les signalements d'avis écrits par l'utilisateur (`review_reports.user_id` / `reason`) et les fiches revendiquées (`claimedProviders`). Correction : ajouter ces deux collections et couvrir `contact_messages` et `claims` dans le test d'export.

12. **Turnstile incohérent selon les clés configurées** — `app/Http/Middleware/ProtectAgainstBots.php:28` vérifie `secret_key`, alors que `resources/views/components/antibot.blade.php:10` et la CSP (`SecurityHeaders.php:42`) dépendent de `site_key`. Avec seulement le secret renseigné, le widget n'est pas affiché et **toutes** les inscriptions échouent. Correction : n'activer Turnstile que si les deux clés sont présentes, via un même helper ou une même condition aux trois endroits.

13. **Configuration de production non sécurisée par défaut** — `.env.example:19,30-34`, `config/session.php:172`. Rien n'indique `SESSION_SECURE_COOKIE=true` : vérifié, les cookies `doggys-session` et `XSRF-TOKEN` partent avec `secure=false`. `LOG_STACK=single` fait grossir `laravel.log` sans rotation sur un mutualisé, et aucun gabarit de production n'existe (`APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=warning`, `MAIL_MAILER=smtp`). Correction : mettre `'secure' => env('SESSION_SECURE_COOKIE', env('APP_ENV') === 'production')` dans `config/session.php`, passer à `LOG_STACK=daily` + `LOG_DAILY_DAYS=14`, et documenter les valeurs de production en commentaire dans `.env.example`.

14. **Tests manquants sur des chemins critiques** — `tests/Feature/`. Aucun test pour : la réinitialisation complète (POST `password.update` avec un jeton valide), le changement d'e-mail (dé-vérification + envoi du lien), une vérification avec un mauvais `hash` ou l'`id` d'un autre utilisateur (doit être refusée), l'effacement des `contact_messages`, la limite par IP au login, la CSP des fichiers servis. Correction : un test Feature par point.

Hors de ce lot mais lié au schéma : `claims.provider_id` est en `cascadeOnDelete` (`2026_10_15_100007_create_claims_table.php`). Un `forceDelete()` de fiche supprimera les réclamations sans effacer les fichiers de preuve, qui resteront orphelins sur le disque privé. Le lot « réclamations » doit supprimer `proof_path` dans un événement `deleting` de `Claim`, ou dans le code qui supprime définitivement la fiche.
