# Tour 4 - Garde-fou CODE & SECURITE (lot PRO/AVIS/ADMIN/CONTACT, 62e77b0..13bdb89)

Verdict : A CORRIGER (0 bloquant, 1 majeur, 5 mineurs). `php artisan test --compact` : 152 tests verts.

Verifie et conforme : policies (ReviewPolicy, ProviderPolicy::manage) appliquees par `->can()` ou FormRequest::authorize sur
toutes les routes a id (edit/update/destroy avis, report, reply, pro.*) ; `pro` (verifie + isPro), `can:admin`,
`password.recent` (15 min) sur les suppressions/roles ; preuves : `mimes`+`mimetypes` (MIME sniffe), 5 Mo, nom aleatoire,
extension deduite du MIME, disque prive `local`, URL temporaire 10 min, `#[Hidden]` sur proof_path ; BCE modulo 97 correct
(prefixe BE, separateurs, 0/1 en tete) ; mass assignment (Review fillable rating/body, Provider sans status/claimed_by) ;
recalcul de note par ecriture directe `toBase()->update` (cree/edite/masque/supprime/purge) ; purge en transaction avec
suppression des preuves `afterCommit` ; hook `forceDeleting` supprime bien les fichiers (teste) ; aucun `{!! !!}` sur
contenu utilisateur ; limiteurs nommes a cle prefixee ; admin sans N+1 (with() partout).

## MAJEUR
1. app/Models/Review.php (blockReason) + app/Http/Controllers/ReviewController.php:26-28 - Double POST simultane.
   Le controle des 30 jours / 5 par 24 h est un SELECT dans l'`after()` du FormRequest, puis un INSERT sans verrou ni
   contrainte : deux requetes paralleles (double clic, script) passent toutes deux et creent 2 avis pour le meme pro
   (ou depassent 5/jour), la note moyenne est faussee. Le limiteur `reviews` (10/min) ne l'empeche pas.
   Correction : dans `store()`, `Cache::lock('review:'.$user->id, 10)->block(3, fn () => ...)` autour de
   `Review::blockReason()` + `save()` (re-verifier dans le verrou, 422 sinon), ou transaction + recheck.
   Ajouter un test (second appel apres le premier = refuse) deja present pour le sequentiel, pas pour le verrou.

## MINEUR
2. routes/web.php (groupe `pro`, listing.store) + app/Http/Controllers/ClaimController.php:63-84 - Spam « Ajoutez votre
   entreprise ». Seul garde-fou : 10 POST/h par compte pro + antibot. Chaque POST cree une fiche `hidden` + un fichier de
   5 Mo + un claim `pending` : ~240 fiches/jour/compte, file admin noyee, disque rempli, fiches hidden jamais purgees.
   Correction : plafonner les claims `pending` par utilisateur (ex. 5, ValidationException au-dela) dans `storeListing`/`store`.
3. app/Models/User.php (purge) - Un pro supprime avec une fiche creee via « Ajoutez votre entreprise » encore `hidden`
   laisse cette fiche orpheline a jamais (son claim est supprime, plus personne ne peut l'approuver).
   Correction : dans purge(), supprimer (forceDelete) les providers hidden dont le seul claim pending est `creates_listing` du user.
4. app/Policies/ProviderPolicy.php:20 et ReviewPolicy.php:155 - `manage`/`reply` ne regardent pas le statut. Un pro dont la
   fiche a ete `removed` (demande RGPD appliquee) peut encore l'editer (PUT pro.update), demander un badge, et repondre
   a des avis masques. Correction : `&& $provider->status !== ProviderStatus::Removed` ; `reply` : avis `Published` seulement.
5. app/Notifications/ContactMessageReceived.php:35 - `->line($message)` : le texte du visiteur est interprete en Markdown
   par le gabarit mail (liens/images cliquables dans la boite Doggys : phishing contre l'admin). Correction : echapper
   (`e()` + remplacer les caracteres Markdown) ou envoyer en `view` texte brut.
6. app/Providers/AppServiceProvider.php (limiteur `contact`) - `Limit::perDay(200)->by('contact|global')` : un attaquant
   multi-IP epuise le quota global et bloque le formulaire pour tous. Correction : retirer le plafond global
   (garder 5/h/IP) ou le monter fortement et alerter.
7. Tests manquants : (a) `User::purge()` d'un pro avec claim + badge request + fichiers de preuves (suppression des
   fichiers, fiche remise a `free`/`none`) ; (b) IDOR explicite : pro B -> GET/PUT pro.edit, pro.badge, pro.reply
   d'un avis de la fiche du pro A = 403 ; (c) verrou anti double POST (point 1) ; (d) mail ContactMessageReceived.
