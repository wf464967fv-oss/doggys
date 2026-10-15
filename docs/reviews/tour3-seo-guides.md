# Revue SEO & performance, tour 3 : lot GUIDES

Date : 2026-10-15.

## Méthode

**Base de test isolée.** La base locale du projet ne contient aucun guide (`Post::count() = 0`). J'ai donc travaillé sur une copie de `database.sqlite` placée dans le scratchpad, dans laquelle j'ai lancé `GuideSeeder` (52 articles importés). Le serveur a été démarré sur cette copie (`DB_DATABASE` surchargé). J'ai vérifié ensuite que la base du projet n'a pas été modifiée : toujours 0 guide.

**Ce qui a été contrôlé :**
- crawl des 4 index et des 52 articles ;
- validation des hreflang, du JSON-LD et des liens internes ;
- mesure de recouvrement entre guides (8-grammes) ;
- Lighthouse mobile sur un article, en brut puis derrière le proxy gzip qui reproduit le `.htaccess` ;
- **vérification factuelle** des affirmations réglementaires sur les sites officiels : wallonie.be, vlaanderen.be, environnement.brussels, EUR-Lex et Conseil de l'UE, communications de DogID et du ministre flamand.

Les serveurs et le proxy ont été arrêtés par leur PID.

## Verdict : À CORRIGER

Le socle technique est très bon :
- hreflang réciproques sur les 52 articles ;
- JSON-LD Article + BreadcrumbList valide ;
- un seul h1 et hiérarchie Hn propre ;
- 1 170 à 1 630 mots par article, aucun contenu dupliqué entre guides ;
- Lighthouse mobile 99 avec gzip.

Les faits sont exacts dans leur grande majorité.

Restent bloquants :
- **une erreur réglementaire** (B1), dans les 4 langues : on demande à un non-Wallon de faire une démarche que l'administration wallonne dit explicitement inutile ;
- **une généralisation inexacte** (B2) sur l'agrément des élevages à Bruxelles.

Il reste aussi 1 défaut MAJEUR : 28 liens des guides pointent vers des pages catégorie en noindex.

## Scores Lighthouse (mobile, `/fr/guides/identification-puce-dogid-chien`)

| Mesure | Perf | Access. | Bonnes prat. | SEO | FCP | LCP | TBT | CLS | Poids |
|---|---|---|---|---|---|---|---|---|---|
| Brut (`artisan serve`) | 91 | 100 | 100 | 100 | 2,3 s | 3,1 s | 90 ms | 0 | 367 Ko |
| Gzip + cache (≈ Apache), 2 tirs | **99** | 100 | 100 | 100 | 1,4 s | 2,1 s | 50 ms | 0 | 185 Ko |

L'élément LCP est le chapô de l'article. Aucune image ni carte. Le seul levier restant est le CSS bloquant (15 Ko en gzip), déjà traité par Apache.

## Vérification factuelle

| # | Affirmation (guides concernés) | Verdict | Source / correction |
|---|---|---|---|
| F1 | Identification et enregistrement obligatoires depuis 1998 (identification) | **EXACT** | Obligatoire depuis le 1/9/1998 (DogID, news.belgium.be, environnement.brussels) |
| F2 | Puce et enregistrement avant 8 semaines, et en tout cas avant toute cession (identification, adopt-puppy) | **EXACT** | DogID et Bruxelles Environnement : chien né chez soi, avant 8 semaines ; chien acquis, déjà identifié avec son passeport |
| F3 | Passeport européen pour chaque chien identifié depuis 2004 (identification) | **EXACT** | Passeport remis à l'identification depuis le 7/6/2004 ; obligatoire pour voyager dans l'UE depuis le 1/10/2004 |
| F4 | Bien-être animal régionalisé depuis 2014 | **EXACT** | 6e réforme de l'État, transfert au 1/7/2014 |
| F5 | Wallonie : extrait du fichier central exigé depuis le 1/7/2022, valable 30 jours, demandé à la commune, vérifié par le vendeur, le refuge ou l'éleveur | **EXACT** | wallonie.be : « Depuis le 1er juillet 2022 » ; « Ce permis est valable durant 30 jours » ; « Chaque refuge, commerce ou éleveur … a l'obligation de vous demander un extrait » |
| F6 | **Wallonie, non-résidents** : « si vous habitez à Bruxelles ou en Flandre … demandez l'extrait au Service public de Wallonie (délai jusqu'à 15 jours) » (identification fr/nl/en/de) | **INEXACT : BLOQUANT (B1)** | wallonie.be : « VOUS NE RÉSIDEZ PAS EN WALLONIE mais vous souhaitez acquérir un animal auprès d'un professionnel sur le territoire wallon : **Vous pouvez l'acquérir sans document de l'administration wallonne.** » ; parmi les exceptions : « Si vous n'avez pas de domicile en Wallonie » |
| F7 | Wallonie : champ d'application « toute personne qui souhaite acquérir un animal (achat, adoption, don) » | **IMPRÉCIS** (mineur) | Exceptions officielles : acquisition à des fins économiques, cession entre particuliers d'un animal **autre** qu'un chien ou un chat, chien d'assistance ou de travail, personne sans domicile en Wallonie. Pour un chien, la formulation tient, sauf pour les non-résidents (F6) et les chiens d'assistance |
| F8 | Réforme wallonne : certificat numérique automatique qui remplacera l'extrait (identification, adopt-puppy) | **EXACT** (prudent, bien formulé) | Réforme adoptée en 1re lecture le 5/3/2026 ; « certificat de confiance numérique » via Mon Espace ; entrée en vigueur visée au 1er semestre 2027. Le guide ne donne pas de date : correct, puisque la réforme n'est pas votée |
| F9 | Wallonie : agrément obligatoire pour tout éleveur, y compris occasionnel (≤ 2 portées par an), catégories selon femelles et portées, numéro dans les annonces (choose-breeder, adopt-puppy) | **EXACT** | wallonie.be : « Toute personne … qui souhaite exercer une activité d'élevage … et commercialiser les portées, doit obtenir un agrément » ; occasionnel « au maximum 2 portées par an » ; « mentionner le numéro d'agrément … dans vos annonces » |
| F10 | Flandre : agrément avec numéro HK à partir de 3 portées par an (choose-breeder, identification, adopt-puppy) | **EXACT** | vlaanderen.be : « Voor honden is deze erkenning nodig vanaf 3 nestjes per jaar » |
| F11 | Flandre : seuls les éleveurs peuvent vendre des chiens | **EXACT** | vlaanderen.be : « Enkel kwekers mogen honden en katten verhandelen » |
| F12 | Flandre : « dans les annonces, le numéro de puce doit être mentionné » (identification, choose-breeder, adopt-puppy) | **EXACT mais incomplet** (mineur, M3) | Un éleveur occasionnel ne peut annoncer **que dans des médias spécialisés agréés** (pas de petites annonces générales ni de réseaux sociaux), avec le numéro de puce de chaque chien. Un éleveur agréé doit afficher son **numéro HK** dans toute publicité. C'est le premier signal anti-arnaque pour un acheteur flamand, et il manque |
| F13 | Flandre : chien cédé pucé, enregistré, avec passeport, d'au moins 8 semaines | **EXACT** | vlaanderen.be (vente interdite avant 8 semaines ; « gechipt en correct geregistreerd ») |
| F14 | NL `choose-breeder` : « drie soorten erkende kwekers: hobbykwekers, beroepskwekers en kweker-handelaars » | **NON VÉRIFIABLE** | Non retrouvé sur vlaanderen.be ni dans les sources consultées, qui ne distinguent qu'occasionnel (≤ 2 nesten) et erkend (≥ 3). Supprimer, ou sourcer (codex.vlaanderen.be) |
| F15 | **Bruxelles** : « Les élevages doivent être agréés par Bruxelles Environnement » (choose-breeder, identification fr/nl/en/de) | **INEXACT : BLOQUANT (B2)** | environnement.brussels : un éleveur occasionnel (particulier, **moins de 3 portées par an**) n'a besoin **ni d'agrément ni de certificat de garantie** ; il ne peut annoncer que dans des médias spécialisés. L'agrément vise l'élevage amateur (au moins 2 femelles reproductrices, au plus 10 portées), professionnel, ou éleveur-commerçant. Pensions, refuges et commerces : agrément obligatoire (exact) |
| F16 | Wallonie : colliers électriques et étrangleurs interdits, « sauf dérogations très encadrées » (training, behaviourist) | **EXACT** | wallonie.be : colliers électriques interdits, avec des dérogations (police, défense, sécurité civile, douanes) ; tolérance pour la clôture enterrée et les clubs jusqu'au 1/4/2024. Nuance à ajouter (mineur) : les **semi-étrangleurs avec butée ne sont pas interdits**, et l'étrangleur reste possible sur attestation vétérinaire (6 mois) |
| F17 | Flandre : colliers électriques interdits au 1/1/2027 (nl : « vanaf 1 januari 2027 ») | **EXACT, nuance** (mineur) | Ben Weyts et Raad voor Dierenwelzijn : à partir du 1/1/2027, interdiction des colliers à télécommande et des colliers anti-aboiement, usage et commerce, sans exception pour la police ni l'armée. **Exception : le collier couplé à une clôture invisible reste autorisé.** Écrire « les colliers à impulsions électriques (sauf clôture invisible) » |
| F18 | DE : les règles wallonnes (permis, agrément, colliers) valent « aussi en Ostbelgien » | **EXACT** | La Région wallonne publie ces démarches en allemand (wallonie.be/de : « Verwendung eines Elektro- oder Würgehalsbandes » ; procédure d'agrément « disponible en français et en allemand »). Le bien-être animal n'a pas été transféré à la Communauté germanophone (transferts de 2020 : aménagement du territoire, logement, énergie) |
| F19 | Rage : primo-vaccination dès 12 semaines, valable 21 jours après, donc pas de voyage avant 15 semaines ; rappel avant échéance valable immédiatement (holidays, vaccines, choose-breeder) | **EXACT** | Règl. (UE) 576/2013, annexe III ; AFSCA ; Your Europe |
| F20 | Tatouage lisible réalisé avant le 3/7/2011 accepté | **EXACT** | Règl. 576/2013, art. 17 |
| F21 | Échinocoque : traitement de 24 à 120 h avant l'arrivée, pour l'Irlande, l'Irlande du Nord, la Finlande, Malte et la Norvège ; la Grande-Bretagne impose un traitement similaire | **EXACT** | Règl. délégué (UE) 2018/772 (les 5 territoires listés) ; gov.uk : traitement 1 à 5 jours avant l'arrivée en GB |
| F22 | Titrage des anticorps antirabiques exigé au retour de certains pays tiers | **EXACT** | Règl. 576/2013 (pays tiers non listés : titrage, puis attente de 3 mois) |
| F23 | Rage non obligatoire pour un chien qui reste en Belgique | **EXACT** | AFSCA (Belgique indemne) |
| F24 | Règlement européen de 2026 sur le bien-être et la traçabilité, mise en œuvre progressive | **EXACT** | Règlement **(UE) 2026/1818** du 17/6/2026, publié au JO le 10/8/2026, application différée. Citer la référence et le lien EUR-Lex renforcerait la crédibilité (mineur) |
| F25 | Pensions agréées par la Région (holidays) | **EXACT** | Flandre (erkenning dierenpension), Wallonie, Bruxelles |
| F26 | Pedigree en Belgique : LOSH (Société Royale Saint-Hubert) | **EXACT** | Livre des origines reconnu |
| F27 | Toiletteur : métier non réglementé, sans diplôme obligatoire (choose-groomer) | **EXACT** (à ma connaissance) | Absent des listes de compétences professionnelles réglementées. Le comportementaliste n'est pas non plus réglementé (behaviourist) : cohérent |

Les conseils vétérinaires (vaccins de base, vermifuges, aliments toxiques, signes d'urgence, âges du chien senior) sont conformes aux recommandations usuelles (WSAVA/ESCCAP). Ils restent prudents et renvoient au vétérinaire, comme l'exige le cahier des charges : pas de remarque.

## Défauts

### BLOQUANT

**B1. Non-résidents wallons : démarche inexistante présentée comme obligatoire (4 langues)**
- Fichiers :
  - `database/data/guides/identification-registration/fr.md:75`
  - `…/en.md:75`
  - `…/de.md:75`
  - `…/nl.md:79`
- Problème : le guide demande à un habitant de Bruxelles, de Flandre (ou de l'étranger, version DE) d'obtenir l'extrait auprès du SPW, « délai jusqu'à 15 jours ». Selon wallonie.be (page mise à jour le 23/07/2026), un non-résident peut acquérir l'animal **sans aucun document de l'administration wallonne**. Le guide fait donc attendre 15 jours un adoptant flamand ou bruxellois, ou le dissuade d'adopter dans un refuge wallon.
- Correction attendue :
  - FR : « La règle concerne les personnes **domiciliées en Wallonie**. Si vous habitez à Bruxelles, en Flandre ou à l'étranger, vous pouvez acquérir un animal auprès d'un éleveur, d'un refuge ou d'un commerce wallon sans document de l'administration wallonne. »
  - Même correction dans les versions nl, en et de (DE : « außerhalb der Wallonie »).
  - Source : la démarche wallonie.be déjà citée en bas du guide.

**B2. Bruxelles : « les élevages doivent être agréés » est faux pour l'élevage occasionnel**
- Fichiers :
  - `database/data/guides/choose-breeder/{fr,nl,en,de}.md`, section « À Bruxelles » (`fr.md:31`) ;
  - `identification-registration/{fr,nl,en,de}.md`, section Bruxelles (`fr.md:85`, `nl.md:85`).
- Problème : un particulier qui fait moins de 3 portées par an n'a besoin ni d'agrément ni de certificat de garantie (environnement.brussels). Le guide pousse le lecteur à soupçonner un éleveur occasionnel bruxellois en règle et ne donne pas le vrai critère, c'est-à-dire les annonces limitées aux médias spécialisés.
- Correction attendue : « À Bruxelles, l'agrément est obligatoire à partir de l'élevage amateur (au moins 2 femelles reproductrices) et pour les pensions, refuges et commerces. Un particulier qui fait moins de 3 portées par an n'en a pas besoin, mais ne peut publier ses annonces que dans des médias spécialisés. » Ajouter la page « Métiers liés aux chats ou aux chiens » d'environnement.brussels aux sources.

### MAJEUR

**M1. 28 liens internes des guides pointent vers des pages catégorie en noindex**
- Fichier : `resources/views/guides/show.blade.php:91-110` (bloc `proCategories`, alimenté par `related_categories` du front matter).
- Problème : sur l'ensemble des guides, les liens vers les catégories d'éleveurs agréés, comportementalistes, toiletteurs à domicile, kinés, hydrothérapie, garderies et pet-sitters mènent à des pages servies en `noindex, follow`, faute d'assez de pros. Dans les 4 langues, cela fait 13 cibles distinctes et 28 liens au total. Le brief demande zéro lien vers une page en noindex.
- Correction attendue : filtrer `$proCategories` sur la même règle d'indexabilité que la page catégorie (par exemple une méthode `Category::isIndexable()` partagée avec `CategoryController`). À défaut, pointer vers la recherche localisée (déjà en noindex, donc à éviter aussi) ou masquer le lien.

### MINEUR

**m1. `og:type` vaut `website` sur les articles**
- Fichiers : `resources/views/components/layouts/app.blade.php:48` et `guides/show.blade.php`.
- Correction : ajouter une prop `ogType` au layout et passer `article` sur `guides.show`, avec `article:published_time` et `article:modified_time`.

**m2. `dateModified` = date d'import, et auteur non nommé sur des contenus de santé et de réglementation**
- Fichier : `guides/show.blade.php:14-17`.
- Problème : `updated_at` change à chaque `db:seed`. Aujourd'hui, les 52 articles affichent « modifié le 15/10/2026 12:27 ». Google peut y voir une fraîcheur artificielle.
- Correction :
  - ajouter un champ `updated` (et idéalement `reviewed_by`) dans le front matter, et l'utiliser pour `dateModified` et pour la mention visible « mis à jour le » ;
  - pour l'E-E-A-T de ces contenus YMYL, une relecture par un vétérinaire nommé (`reviewedBy` / `author` Person) aiderait.

**m3. Règles flamandes d'annonce incomplètes (F12)**
- Fichiers : `choose-breeder/*.md` (section Flandre), `identification-registration/*.md`, `adopt-puppy/*.md:55`.
- Ajouter : « Un éleveur occasionnel ne peut publier d'annonces que dans des médias spécialisés agréés par Dierenwelzijn Vlaanderen (pas sur les sites de petites annonces généralistes ni sur les réseaux sociaux) ; un éleveur agréé doit indiquer son numéro HK dans toute publicité. »

**m4. Nuances sur les colliers (F16, F17)**
- Fichiers : `training-socialisation/*.md:21`, `behaviourist/*.md:84`.
- Wallonie : préciser que les semi-étrangleurs avec butée restent autorisés.
- Flandre : préciser l'exception « clôture invisible ».

**m5. Affirmation non sourcée en NL (F14)**
- Fichier : `choose-breeder/nl.md:23`, « drie soorten erkende kwekers ».
- Correction : la supprimer, ou la sourcer sur codex.vlaanderen.be.

**m6. Titles SEO trop génériques**
- Ces titles ne visent pas la requête principale. Exemples de reformulation (≤ 50 caractères avant « | Doggys ») :
  - `holidays` FR « Vacances avec ou sans son chien » → « Voyager avec son chien : passeport, rage, pension » ;
  - `holidays` DE « Urlaub mit oder ohne Hund » (25 caractères) → « Urlaub mit Hund: Heimtierausweis und Tollwut » ;
  - `identification-registration` FR → « Puce et DogID : identifier son chien en Belgique » ;
  - `identification-registration` DE → « Hund chippen und registrieren in Belgien » (requête « Hund Chip Belgien ») ;
  - `identification-registration` NL : ajouter « registreren » (« Hond chippen en registreren in DogID »).
- Les autres titles et descriptions sont uniques, de 25 à 50 et de 132 à 155 caractères, et contiennent bien les requêtes : « vaccins vermifuges chien Belgique », « hond adopteren asiel », « Hund aus dem Tierheim »…

**m7. Typographie NL**
- Fichiers : `identification-registration/nl.md:74` et autres.
- Problème : « « permis de détention » » est écrit avec des guillemets français dans le texte NL.
- Correction : utiliser ‘…’ ou “…”, et écrire *permis de détention* en italique, comme terme étranger.

**m8. Référence du règlement européen**
- Citer « règlement (UE) 2026/1818 » avec le lien EUR-Lex dans `identification-registration/*.md` (section européenne) et dans les sources.

## Points vérifiés et conformes

- **Index :** `/fr/guides`, `/nl/gidsen`, `/en/guides`, `/de/ratgeber` en 200, un seul h1, canonical et hreflang ×5.
- **Hreflang :** 52 articles avec exactement 4 alternates + x-default, **100 % réciproques**, uniquement vers des traductions publiées (`translations()->published()`).
- **Slugs :** traduits et riches en mots-clés (`identification-puce-dogid-chien`, `hond-chippen-registreren-dogid`, `hund-chippen-registrieren-dogid`, `microchip-dogid-dog-registration-belgium`…). Un slug d'une autre langue renvoie **404** (`/fr/guides/hond-chippen-registreren-dogid`), de même qu'un slug inconnu.
- **JSON-LD :** valide sur les 52 articles.
  - `Article` : headline ≤ 110, `description`, `inLanguage`, `datePublished`, `dateModified`, `mainEntityOfPage`, `image`, `author`, `publisher` + logo.
  - `BreadcrumbList` (Accueil > Guides > article).
- **Hn :** h1 puis h2/h3 sans saut de niveau. La version NL met la section « Vlaanderen » en premier : bonne localisation.
- **Volume et duplication :** 1 170 à 1 630 mots par article. Aucun recouvrement > 3 % (8-grammes) entre deux guides d'une même langue : les paragraphes réglementaires sont réécrits, pas copiés.
- **Liens sortants :** sources officielles en `rel="noopener noreferrer"` (dofollow vers .be et .eu : très bien pour la crédibilité).
- **Qualité des langues :** NL flamand naturel (« u », « thuis raken », « gewest », « Leefmilieu Brussel », « Departement Omgeving ») ; DE soigné, avec un traitement explicite de l'Ostbelgien (correct, voir F18). Pas de trace de traduction automatique.

## Observation hors lot (à transmettre au chef d'orchestre)

Pendant l'audit (12:28), **toutes les pages catégorie répondaient 500** (`/fr/veterinaire`, `/nl/dierenarts`, `/fr/categories`…). L'erreur : `Undefined array key "city"` dans `resources/views/directory/category.blade.php` (`storage/logs/laravel.log`, lignes 4683 et 4829).

C'est probablement une correction de l'annuaire en cours chez un autre agent, mais 32 liens des guides menaient alors vers ces 500. À revérifier avant de livrer le lot.

## À prévoir

- `sitemap.xml` : inclure les 52 articles avec `xhtml:link` hreflang et `lastmod` tiré du front matter (m2).
- Sur les pages catégorie : 1 ou 2 liens vers les guides liés (maillage retour), déjà recommandé au tour 2 (mineur 11).
- Mettre à jour B1 et F8 quand la réforme wallonne (certificat numérique, 2027) entrera en vigueur : prévoir un rappel éditorial.
- Prévoir une revue juridique annuelle des 4 versions des guides `identification-registration`, `choose-breeder` et `holidays`.

## Sources consultées (vérification factuelle)

- wallonie.be :
  - [extrait du fichier central (permis de détention)](https://www.wallonie.be/fr/demarches/demander-un-extrait-du-fichier-central-de-la-delinquance-environnementale-et-du-bien-etre-animal-afin-dacheter-adopter-ou-recevoir-un-animal-de-compagnie)
  - [agrément éleveur](https://wallonie.be/fr/demarches/obtenir-un-agrement-en-tant-queleveur-de-chiens-ou-de-chats)
  - [colliers](https://www.wallonie.be/fr/demarches/utiliser-un-collier-electrique-ou-etrangleur-pour-chien), ainsi que [leur version allemande](https://www.wallonie.be/de/demarches/verwendung-eines-elektro-oder-wurgehalsbandes-fur-hunde)
- vlaanderen.be :
  - [honden en katten kweken](https://www.vlaanderen.be/honden-en-katten-kweken)
  - [huisdieren kweken, verkopen of weggeven](https://www.vlaanderen.be/huisdieren-kweken-verkopen-of-weggeven)
- Interdiction flamande des colliers électriques :
  - [communication du ministre B. Weyts](https://www.benweyts.be/nieuws/uitdoofscenario-voor-stroomhalsbanden-verbod-vanaf-2027)
  - [VRT](https://www.vrt.be/vrtnws/nl/2021/08/10/stroomhalsbanden-voor-honden-verboden-vanaf-2027/)
- environnement.brussels :
  - [métiers liés aux chats ou aux chiens](https://environnement.brussels/pro/services-et-demandes/agrements-et-enregistrements/metiers-lies-aux-chats-ou-aux-chiens-vos-obligations-et-conditions)
  - [identification et enregistrement](https://environnement.brussels/citoyen/lenvironnement-bruxelles/veiller-au-bien-etre-animal/identification-et-enregistrement-des-animaux)
- DogID / identification : [news.belgium.be – identification et enregistrement des chiens](https://news.belgium.be/fr/identification-et-enregistrement-des-chiens) et la [FAQ de DogID](https://www.dogid.be/en/faq/individual)
- UE :
  - [règlement (UE) 2026/1818](https://eur-lex.europa.eu/eli/reg/2026/1818/oj)
  - [communiqué du Conseil du 22/5/2026](https://www.consilium.europa.eu/en/press/press-releases/2026/05/22/council-approves-a-deal-on-strengthening-the-welfare-and-traceability-of-cats-and-dogs/)
  - règlements 576/2013 et 2018/772 (déplacements d'animaux et échinocoque)
- Réforme wallonne :
  - [communiqué du Gouvernement wallon du 3/7/2025](https://www.wallonie.be/fr/acteurs-et-institutions/wallonie/gouvernement-de-wallonie/communiques-presse/2025-07-03-0)
  - [RTBF, adoption en 1re lecture](https://www.rtbf.be/article/la-reforme-du-code-du-bien-etre-animal-adoptee-en-1re-lecture-par-le-gouvernement-wallon-11689752)
