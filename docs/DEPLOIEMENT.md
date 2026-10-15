# Mettre Doggys en ligne sur o2switch

Guide pas à pas, sans prérequis technique. Comptez 1 à 2 heures la première fois.
Les mises à jour suivantes prennent 5 minutes (étape 15).

**Vous aurez besoin de :** votre accès cPanel o2switch et un dépôt Git privé (GitHub) contenant le projet.

---

## 1. Créer la base de données MySQL

1. Connectez-vous à cPanel, rubrique **Bases de données MySQL®**.
2. **Créer une base** : nom `doggys` (cPanel ajoute un préfixe, par exemple `abcd1234_doggys`). Notez le nom complet.
3. **Ajouter un utilisateur** : nom `doggys`, mot de passe généré (long). Notez le nom complet et le mot de passe.
4. **Ajouter l'utilisateur à la base** avec **TOUS LES PRIVILÈGES**.

## 2. Choisir PHP 8.4 et ses extensions

1. cPanel, **Sélectionner la version de PHP** (ou « MultiPHP Manager »).
2. Choisissez **PHP 8.4** pour le domaine doggys.be.
3. Dans les extensions, vérifiez qu'elles sont cochées : `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `intl`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`, `zip`, `opcache`.
4. Dans les options PHP, mettez `memory_limit` à 256M et `upload_max_filesize` à 10M.

## 3. Ajouter le domaine doggys.be et le faire pointer sur `public/`

C'est l'étape la plus importante : **seul le dossier `public/` du projet doit être visible sur internet.** Le reste (mots de passe, code) ne doit jamais l'être.

1. Le code sera placé dans `/home/VOTRE_COMPTE/doggys` (et **pas** dans `public_html`).
2. cPanel, **Domaines**, puis **Créer un domaine** (ou modifiez doggys.be s'il existe déjà).
3. Cochez « Ne pas partager la racine de document » si proposé, et mettez comme **racine de document** : `/home/VOTRE_COMPTE/doggys/public`.
4. Faites aussi pointer `www.doggys.be` vers le même dossier (alias), ou redirigez-le vers doggys.be.

## 4. Envoyer le code

**Option A (recommandée) : Git dans cPanel**
1. Mettez le projet sur un dépôt Git privé (GitHub, GitLab...).
2. cPanel, **Git™ Version Control**, **Créer** : collez l'adresse du dépôt, chemin du dépôt `/home/VOTRE_COMPTE/doggys`.
3. Si le dépôt est privé, ajoutez la clé SSH publique affichée par cPanel dans les « Deploy keys » du dépôt.

**Option B : SSH**
1. cPanel, **Accès SSH**, autorisez l'accès SSH et créez une clé (ou utilisez le mot de passe).
2. Depuis votre ordinateur : `ssh VOTRE_COMPTE@votre-serveur.o2switch.net`, puis `git clone ADRESSE_DU_DEPOT doggys`.

## 5. Installer les dépendances PHP

En SSH, dans le dossier du projet :

```bash
cd ~/doggys
composer install --no-dev -o
```

(`-o` optimise le chargement. Si `composer` n'est pas trouvé : `php8.4 /usr/local/bin/composer install --no-dev -o`.)

## 6. CSS/JS compilés

Rien à faire : le dossier `public/build` est versionné dans Git et arrive avec le code. Après un changement de design, lancez `npm run build` sur votre ordinateur **avant** de pousser le code.

## 7. Le fichier `.env` de production

En SSH : `cp .env.example .env && nano .env`. Voici les valeurs à régler (remplacez ce qui est en MAJUSCULES) :

```ini
APP_NAME=Doggys
APP_ENV=production
APP_DEBUG=false
APP_URL=https://doggys.be
APP_LOCALE=fr
APP_FALLBACK_LOCALE=fr

LOG_CHANNEL=stack
LOG_STACK=daily
LOG_DAILY_DAYS=14
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=abcd1234_doggys
DB_USERNAME=abcd1234_doggys
DB_PASSWORD=LE_MOT_DE_PASSE_DE_L_ETAPE_1

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=sync

MAIL_MAILER=smtp
MAIL_HOST=mail.doggys.be
MAIL_PORT=465
MAIL_SCHEME=smtps
MAIL_USERNAME=contact@doggys.be
MAIL_PASSWORD=MOT_DE_PASSE_DE_LA_BOITE_MAIL
MAIL_FROM_ADDRESS="contact@doggys.be"
MAIL_FROM_NAME="Doggys"

# Anti-robots (facultatif, mettre les deux ou aucun) : clés gratuites sur dash.cloudflare.com > Turnstile
TURNSTILE_SITE_KEY=
TURNSTILE_SECRET_KEY=

# Mentions légales : VOS vraies informations (obligatoire avant ouverture au public)
DOGGYS_LEGAL_BCE=0000.000.000
DOGGYS_LEGAL_ADDRESS="Rue ..., 0000 Ville"
DOGGYS_LEGAL_EMAIL=contact@doggys.be
```

Notes :
- Créez d'abord la boîte e-mail `contact@doggys.be` dans cPanel, **Comptes de messagerie** (les paramètres SMTP sont affichés dans « Configurer les clients de messagerie »).
- Tant que `DOGGYS_LEGAL_*` est vide, les mentions légales affichent « [à compléter] ».
- Ne partagez jamais ce fichier. Il est interdit dans Git (`.gitignore`).

Puis générez la clé de l'application (une seule fois, **ne jamais la changer ensuite**, sinon les sessions sont perdues) :

```bash
php artisan key:generate
```

## 8. Créer les tables et les données de base

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
```

`db:seed` charge les catégories, codes postaux, communes et guides (sans risque si on le relance).

## 9. Importer les professionnels et les guides

```bash
php artisan doggys:import-providers
php artisan doggys:import-guides
```

Ces commandes se relancent sans danger : elles mettent à jour sans doublon. L'import des professionnels recalcule aussi les pages « ville ».

## 10. Créer votre compte administrateur

1. Allez sur https://doggys.be/fr/inscription et créez votre compte, puis confirmez l'e-mail reçu.
2. En SSH : `php artisan doggys:make-admin votre@email.be`
3. L'administration est sur https://doggys.be/admin

## 11. Activer les caches (site plus rapide)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

À refaire après **chaque** modification du `.env` ou mise à jour du code (le script de l'étape 15 le fait pour vous).

## 12. Activer le SSL (HTTPS)

1. cPanel, **SSL/TLS Status**.
2. Cochez doggys.be et www.doggys.be, cliquez **Run AutoSSL**. Le certificat est gratuit et se renouvelle seul.
3. Vérifiez que https://doggys.be s'affiche avec le cadenas.

## 13. Le cron (tâches automatiques)

Il exécute chaque jour les nettoyages RGPD (et toute future tâche planifiée).

1. cPanel, **Tâches Cron**.
2. Réglage « Une fois par minute » (`* * * * *`), commande :

```
cd /home/VOTRE_COMPTE/doggys && /usr/local/bin/php8.4 artisan schedule:run >> /dev/null 2>&1
```

(Si `php8.4` n'existe pas, utilisez `php`. Le chemin exact est donné dans « Sélectionner la version de PHP ».)

Tâches planifiées : suppression des justificatifs refusés de plus de 90 jours et des messages/signalements traités de plus de 12 mois, nettoyage des liens de réinitialisation de mot de passe expirés.

## 14. Sauvegardes

- **Base de données** : cPanel, **Sauvegarde**, téléchargez la sauvegarde MySQL chaque semaine au minimum. Pour automatiser, ajoutez un second cron quotidien :
  `mysqldump -u UTILISATEUR -p'MOT_DE_PASSE' NOM_BASE | gzip > /home/VOTRE_COMPTE/backups/doggys-$(date +\%F).sql.gz` (créez le dossier `backups`).
- **Fichiers** : seuls `.env` et `storage/app/private` (justificatifs) comptent : le reste revient de Git. Copiez-les hors du serveur régulièrement.
- o2switch garde aussi des sauvegardes quotidiennes : consultez-les dans cPanel, **Backup**.
- Testez une restauration au moins une fois.

## 15. Mettre à jour le site

1. Sur votre ordinateur : si le design a changé, `npm run build` (étape 6).
2. Poussez votre code sur Git.
3. En SSH, depuis le serveur :

```bash
cd ~/doggys
bash scripts/deploy.sh
```

Le script met le site en maintenance quelques secondes, récupère le code, installe les dépendances, applique les migrations, remet les caches et rouvre le site. Il peut être relancé sans danger.

## 16. Google Search Console et sitemap

1. Allez sur https://search.google.com/search-console et ajoutez la propriété **doggys.be** (type « Domaine » : validation par un enregistrement DNS TXT, à ajouter dans cPanel, **Éditeur de zone**).
2. Rubrique **Sitemaps** : envoyez `https://doggys.be/sitemap.xml`. Il contient les 4 langues et est régénéré toutes les heures.
3. Faites de même sur Bing Webmaster Tools (importation depuis Google possible).
4. Après quelques jours, regardez « Pages » pour repérer d'éventuelles erreurs.

## Vérifications finales

- https://doggys.be/fr s'affiche en HTTPS, sans erreur.
- Inscription : un e-mail de confirmation arrive.
- Les pages Mentions légales, Confidentialité, Conditions et Cookies sont complètes (aucun « [à compléter] »).
- https://doggys.be/sitemap.xml répond.
- Couper le réseau sur un téléphone : la page « Vous êtes hors ligne » s'affiche (le mode hors ligne ne s'active qu'en production).

## En cas de problème

- Page blanche ou erreur 500 : regardez `storage/logs/laravel-AAAA-MM-JJ.log` (jamais `APP_DEBUG=true` en ligne).
- Site sans mise en forme : le dossier `public/build` est absent ou incomplet (relancez `npm run build` en local, poussez, redéployez).
- Après une modification du `.env` sans effet : `php artisan config:cache`.
- Erreur de droits : `chmod -R 775 storage bootstrap/cache`.
- Retour en arrière d'urgence : `php artisan down`, restaurez la base, `git checkout` de la version précédente, `php artisan up`.
