# Doggys — Garde-fous

Chaque lot de travail doit passer les trois garde-fous ci-dessous avant d'être accepté par le chef d'orchestre.
Un point non respecté = lot renvoyé. Un garde-fou cite toujours `fichier:ligne` + la correction attendue.

## 1. Garde-fou CODE & SÉCURITÉ
- `php artisan test --compact` vert ; `vendor/bin/pint --test` propre ; `npm run build` sans erreur.
- Chaque fonctionnalité non triviale a au moins un test Feature (recherche, avis + limites, réclamation, auth, RGPD export/suppression, i18n).
- Autorisation : Policies/Gates sur toute action (un pro ne modifie que SA fiche, un user que SES chiens/avis, admin protégé).
- Validation : Form Requests partout ; aucune donnée utilisateur non validée en base ; mass assignment protégé (`$fillable`).
- Sécurité : CSRF, échappement Blade (`{{ }}` — jamais `{!! !!}` sur contenu utilisateur), rate limiting (login, inscription, avis, signalement, contact), mots de passe forts, vérification e-mail, uploads de preuves en stockage **privé** avec type/poids limités, en-têtes de sécurité (CSP, X-Frame-Options, Referrer-Policy, HSTS en prod), pas de secrets dans le code, `APP_DEBUG=false` en prod.
- Pas de N+1 (eager loading), index DB sur les colonnes de recherche (lat, lng, category, postal_code, status).
- SQL portable SQLite/MySQL. Compatible hébergement mutualisé o2switch (pas de worker permanent obligatoire : queue `sync` ou `database` + cron).
- Ponytail : pas d'abstraction inutile, pas de dépendance ajoutée sans raison forte.

## 2. Garde-fou DESIGN & UX
- Identité : couleurs `#F5A043` orange, `#FDE7C4` crème, `#6B3E1F` brun foncé, `#D4995C` brun moyen, `#FF5C77` rose ; logo `public/images/logo-doggys.png`, pictogramme `public/images/logo-mark.png`. Titres en police ronde épaisse (esprit logo), texte en police douce lisible, auto-hébergées.
- Chaleureux, rassurant, familial : coins arrondis, espaces généreux, petites pattes 🐾 discrètes, illustrations/icônes cohérentes (SVG inline). Jamais « annuaire administratif ».
- Mobile d'abord : parfait à 360 px, 768 px, 1280 px ; pas de scroll horizontal ; cibles tactiles ≥ 44 px.
- Accessibilité WCAG 2.1 AA : contraste ≥ 4.5:1 (attention orange sur blanc → texte orange uniquement en grand/gras ou sur brun), focus visible, labels de formulaires, alt des images, navigation clavier, `lang` correct.
- Cohérence : composants Blade réutilisés (boutons, cartes, badges, champs), aucun style isolé copié-collé.
- États : vide, chargement, erreur, succès présents et soignés sur chaque écran.
- Vérification par captures Playwright (Edge : `channel: 'msedge'`) aux 3 largeurs sur chaque page clé.

## 3. Garde-fou SEO & PERFORMANCE
- Chaque page : `<title>` unique (≤ 60 car.), meta description unique (≤ 155), un seul `<h1>`, hiérarchie Hn propre, canonical, `hreflang` fr/nl/en/de + `x-default`, Open Graph + Twitter card.
- URLs propres, localisées par préfixe de langue (`/fr`, `/nl`, `/en`, `/de`), slugs lisibles, pas de paramètres pour les pages indexables.
- JSON-LD : Organization + WebSite (SearchAction) sur l'accueil ; LocalBusiness / VeterinaryCare / PetStore… + AggregateRating (seulement si avis réels) sur les fiches ; Article sur les guides ; BreadcrumbList partout.
- `sitemap.xml` (index + par langue), `robots.txt`, pages pauvres/recherches internes en `noindex`.
- Performance : HTML rendu serveur, CSS/JS minimal, images optimisées (WebP, dimensions explicites, lazy), polices `font-display: swap` + preload, Leaflet chargé uniquement là où il y a une carte. Objectif Lighthouse ≥ 95 en Performance, Accessibilité, Bonnes pratiques, SEO.
- Contenu : textes uniques par langue (pas de traduction automatique bancale), maillage interne guides ↔ catégories.

## 4. Chef d'orchestre
Lit les rapports des trois garde-fous, vérifie lui-même les points critiques (tests, build, captures), renvoie chaque défaut à l'agent responsable, et recommence jusqu'à zéro défaut bloquant.
