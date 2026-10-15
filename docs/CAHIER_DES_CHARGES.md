# Doggys — Cahier des charges (V1)

Plateforme belge de référence qui accompagne le propriétaire de chien toute la vie de l'animal :
trouver les professionnels canins près de chez soi, lire/laisser des avis, gérer le profil de son chien, lire des guides.

- Nom : **Doggys** — domaine **doggys.be** — slogan :
  FR « Le meilleur ami de l'humain » · NL « De beste vriend van de mens » · EN « Humanity's best friend » · DE « Der beste Freund des Menschen »
- Gratuit pour tous, sans publicité. Code privé.
- Toute la Belgique dès le lancement. 4 langues : **fr** (défaut), **nl**, **en**, **de**.
- Web app installable (PWA). Une app native viendra plus tard → garder une API JSON propre (`/api/v1`).
- Propriétaire : Erwan Goossens (indépendant complémentaire, Novexa Digital) — responsable de traitement RGPD.

## Stack (figée)
- Laravel 13, PHP 8.4, Blade + Tailwind CSS 4 + Alpine.js (léger). Pas de SPA.
- Base : SQLite en local, **MySQL/MariaDB en production (o2switch, hébergement mutualisé cPanel)**. Tout le SQL doit être portable SQLite/MySQL (pas de fonctions géo spécifiques : bounding box en SQL + haversine en PHP).
- Carte : Leaflet + tuiles OpenStreetMap (attribution obligatoire).
- Auth : Laravel Fortify (vues Blade maison), vérification e-mail obligatoire, rate limiting.
- Admin : Filament si compatible Laravel 13, sinon admin Blade simple sous `/admin`.
- Anti-bot : honeypot + Cloudflare Turnstile (désactivé si clés absentes du `.env`).
- Mail : driver `log` en local ; adresse de test `contact@doggys.be` en attendant.
- Aucun service tiers payant, aucun traqueur, aucun cookie non essentiel (donc pas de bandeau de consentement ; page cookies informative).

## Catégories de professionnels (slug technique → libellé traduit)
Toilettage : `toiletteur-salon`, `toiletteur-domicile` ·
Santé : `veterinaire`, `urgences-24h` (uniquement cliniques ouvertes 24h/24), `osteopathe`, `kine-physio`, `hydrotherapie`, `dentisterie` ·
Éducation : `educateur`, `comportementaliste` ·
Garde : `pet-sitter`, `promeneur`, `garderie`, `pension` ·
Adoption : `eleveur-agree`, `refuge` ·
Commerce : `animalerie` ·
Lieux : `lieu-dog-friendly`, `parc-a-chiens`.
Un pro peut appartenir à plusieurs catégories (pivot).

## Recherche
- Choix d'une catégorie (ou toutes) + localisation : géolocalisation navigateur OU code postal / commune belge (table des codes postaux avec lat/lng).
- Résultats en liste + carte, triés par distance. Filtres : rayon (5/10/25/50 km), se déplace à domicile, badge vérifié, note minimale.
- Pros « à domicile » : apparaissent si le client est dans leur rayon d'intervention.
- Pages SEO statiques par catégorie × commune principale (ex. `/fr/toiletteur-salon/liege`) générées depuis la même recherche.

## Fiche professionnel (V1)
Nom, catégories, adresse ou zone d'intervention, téléphone, e-mail, site web, horaires (si connus), carte, badges, note moyenne + avis.
Pas de photos ni de tarifs en V1.
- Bouton **« C'est mon entreprise »** : le pro (compte pro) envoie n° BCE + preuve (document) → l'admin valide → le pro peut **supprimer sa fiche** ou **la gérer (s'affilier)**.
- Bouton **« Signaler une erreur »** et **« Demander la suppression »** (RGPD, sans compte).
- Badges : 🟢 **vert « Pro vérifié »** = n° BCE + identité contrôlés · 🔵 **bleu « Agréé »** = profession réglementée contrôlée (n° Ordre des vétérinaires, agrément éleveur, refuge agréé…). Bleu implique vert.

## Comptes
- **Particulier** : profil + un ou plusieurs chiens (nom, race, sexe, date de naissance, n° de puce, stérilisé, poids, vaccins, allergies/notes santé). Données privées, jamais publiques. Export et suppression du compte (RGPD).
- **Professionnel** : réclamer une fiche, éditer ses infos, demander un badge, répondre aux avis. Gratuit en V1 (abonnement plus tard : prévoir un champ `plan` sans logique de paiement).
- **Admin** : Erwan.

## Avis
- Compte avec e-mail vérifié obligatoire. Note 1–5 + texte (20–2000 caractères).
- Max **1 avis par pro et par compte tous les 30 jours** ; max **5 avis par jour par compte**. Pas d'avis sur sa propre fiche.
- Publication directe ; bouton **Signaler** → file de modération admin ; **droit de réponse** du pro.
- Page publique « Comment les avis sont vérifiés » (directive Omnibus / DSA).

## Contenu
- Blog + guides en 4 langues (vraies traductions, pas de placeholder). Au lancement au moins 8 guides de base : adopter un chiot, premières semaines, vaccins et vermifuges, identification/puce et enregistrement (règles par région : Wallonie, Flandre, Bruxelles), alimentation, éducation de base, choisir son toiletteur/éducateur, chien âgé.
- Rédaction prudente : pas de conseil médical péremptoire, renvoyer vers le vétérinaire.

## Données au lancement
Sources légales uniquement : OpenStreetMap (Overpass API, licence ODbL → attribution), BCE / KBO Open Data, annuaires publics dont les conditions le permettent, saisie manuelle. **Jamais Google Maps, Facebook ou autre source dont les CGU l'interdisent.** Chaque fiche garde `source` + `source_ref` + date d'import, et l'import est rejouable sans doublons.

## Légal
Mentions légales, politique de confidentialité, conditions d'utilisation, page cookies, page avis, formulaire de contact. Responsable : Erwan Goossens. Hébergeur : o2switch (France).

## Hors V1
Abonnements payants, photos/tarifs/agenda des pros, réservation, partage du profil chien avec les vétos, apps stores.
