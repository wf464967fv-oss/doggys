<?php

/*
 * Legal pages (see resources/views/pages/legal.blade.php). Placeholders: :bce :address :email.
 * Bump "updated_on" (ISO date) whenever a text changes materially.
 */
return [
    'updated_on' => '2026-10-15',
    'updated' => 'Dernière mise à jour : :date',
    'toc' => 'Sommaire',
    'placeholder' => '[à compléter]',
    'labels' => ['purpose' => 'Finalité', 'basis' => 'Base légale', 'retention' => 'Durée de conservation', 'recipients' => 'Destinataires'],
    'cta_title' => 'Une question sur vos données ou sur le site ?',
    'cta_text' => 'Écrivez-nous : nous répondons à toute demande dans un délai d’un mois au plus.',
    'cta' => 'Nous contacter',

    'notice' => [
        'meta_title' => 'Mentions légales',
        'meta_description' => 'Éditeur, hébergeur et crédits de Doggys, l’annuaire belge gratuit des professionnels canins.',
        'breadcrumb' => 'Mentions légales',
        'title' => 'Mentions légales',
        'intro' => 'Qui édite Doggys, qui l’héberge et d’où viennent les données affichées.',
        'sections' => [
            ['title' => 'Éditeur du site', 'paragraphs' => [
                'Le site doggys.be est édité par Erwan Goossens, indépendant complémentaire, sous le nom d’activité Novexa Digital.',
            ], 'list' => [
                'Numéro d’entreprise (BCE/KBO) : :bce',
                'Numéro de TVA : BE :bce',
                'Adresse : :address',
                'E-mail : :email',
                'Directeur de la publication : Erwan Goossens',
            ]],
            ['title' => 'Hébergeur', 'paragraphs' => [
                'Le site est hébergé par o2switch SAS, 222-224 Boulevard Gustave Flaubert, 63000 Clermont-Ferrand, France (o2switch.fr). Les données sont stockées dans l’Union européenne.',
            ]],
            ['title' => 'Nature du service', 'paragraphs' => [
                'Doggys est un annuaire gratuit et sans publicité des professionnels canins en Belgique. Doggys n’est ni un vétérinaire, ni un prestataire de soins, et n’est pas partie aux relations entre visiteurs et professionnels.',
                'Les fiches de professionnels sont construites à partir de sources ouvertes et d’informations transmises par les professionnels eux-mêmes. Un professionnel peut demander la correction ou la suppression de sa fiche depuis la fiche elle-même.',
            ]],
            ['title' => 'Propriété intellectuelle', 'paragraphs' => [
                'Le nom Doggys, le logo, la mise en page et les textes éditoriaux (guides compris) sont protégés par le droit d’auteur. Toute reproduction sans accord écrit préalable est interdite, hors courte citation avec mention de la source.',
                'Les avis appartiennent à leurs auteurs, qui accordent à Doggys le droit de les afficher sur le site.',
            ]],
            ['title' => 'Crédits et licences des données', 'paragraphs' => [
                'Les cartes et une partie des données de localisation proviennent d’OpenStreetMap, © les contributeurs d’OpenStreetMap, disponibles sous licence ODbL (openstreetmap.org/copyright).',
                'Les codes postaux, communes et coordonnées proviennent de GeoNames (geonames.org), sous licence Creative Commons Attribution 4.0 (CC BY 4.0).',
            ]],
            ['title' => 'Signaler un contenu', 'paragraphs' => [
                'Pour signaler un contenu illicite ou une erreur, utilisez les boutons de signalement du site ou le formulaire de contact. La procédure est décrite dans nos conditions d’utilisation.',
            ], 'links' => ['terms' => 'Conditions d’utilisation', 'privacy' => 'Confidentialité', 'contact' => 'Contact']],
        ],
    ],

    'privacy' => [
        'meta_title' => 'Politique de confidentialité',
        'meta_description' => 'Quelles données Doggys collecte, pourquoi, combien de temps, et comment exercer vos droits RGPD. Aucun traqueur.',
        'breadcrumb' => 'Confidentialité',
        'title' => 'Politique de confidentialité',
        'intro' => 'Doggys collecte le minimum de données, les héberge dans l’Union européenne et n’utilise aucun traqueur publicitaire ou statistique.',
        'sections' => [
            ['title' => 'Responsable du traitement', 'paragraphs' => [
                'Le responsable du traitement est Erwan Goossens (activité Novexa Digital), indépendant complémentaire. Numéro d’entreprise : :bce. Adresse : :address. Contact : :email ou via le formulaire de contact.',
                'Cette politique applique le Règlement général sur la protection des données (RGPD, règlement UE 2016/679) et la loi belge du 30 juillet 2018.',
            ]],
            ['title' => 'Les traitements de données', 'paragraphs' => [
                'Pour chaque traitement, voici pourquoi nous utilisons vos données, sur quel fondement, combien de temps nous les gardons et qui peut y accéder.',
            ], 'treatments' => [
                ['title' => 'Compte utilisateur', 'purpose' => 'créer et gérer votre compte (nom, e-mail, mot de passe chiffré, rôle), vous connecter et vous écrire à propos de votre compte.', 'basis' => 'exécution du service que vous demandez (art. 6.1.b RGPD).', 'retention' => 'jusqu’à la suppression de votre compte, que vous pouvez faire à tout moment.', 'recipients' => 'l’éditeur ; l’hébergeur o2switch (sous-traitant).'],
                ['title' => 'Profils de chiens', 'purpose' => 'vous permettre de tenir un carnet privé de vos chiens (nom, race, naissance, vaccins, notes de santé, puce). Ces informations ne sont jamais publiques.', 'basis' => 'exécution du service (art. 6.1.b).', 'retention' => 'jusqu’à leur suppression par vos soins ou celle de votre compte.', 'recipients' => 'vous seul ; l’hébergeur (sous-traitant).'],
                ['title' => 'Avis', 'purpose' => 'publier votre avis sur un professionnel, modérer les abus et calculer les notes. Votre avis est affiché sous le nom de votre compte.', 'basis' => 'exécution du service (art. 6.1.b) et intérêt légitime de fiabilité des avis (art. 6.1.f).', 'retention' => 'jusqu’à la suppression de l’avis ou du compte.', 'recipients' => 'le public (avis publié) ; le professionnel concerné, qui peut répondre ; l’éditeur.'],
                ['title' => 'Réclamation de fiche et preuves', 'purpose' => 'vérifier qu’une personne représente bien l’entreprise (numéro BCE, fonction, justificatif envoyé en stockage privé).', 'basis' => 'intérêt légitime d’éviter les usurpations (art. 6.1.f).', 'retention' => 'justificatifs refusés : supprimés après 90 jours ; justificatifs acceptés : tant que la fiche est gérée, puis supprimés avec la fiche ou le compte, ou sur demande.', 'recipients' => 'l’éditeur uniquement (jamais publics) ; l’hébergeur (sous-traitant).'],
                ['title' => 'Signalements', 'purpose' => 'traiter les signalements d’erreurs, les demandes de suppression de fiche et les avis ou contenus abusifs.', 'basis' => 'intérêt légitime et obligation légale de traiter les signalements de contenus illicites (art. 6.1.f et 6.1.c, règlement DSA).', 'retention' => 'jusqu’à 12 mois après le traitement du signalement.', 'recipients' => 'l’éditeur ; l’hébergeur (sous-traitant).'],
                ['title' => 'Messages de contact', 'purpose' => 'répondre à votre message (nom, e-mail, contenu).', 'basis' => 'mesures précontractuelles et intérêt légitime de répondre (art. 6.1.b et 6.1.f).', 'retention' => 'jusqu’à 12 mois après le traitement du message.', 'recipients' => 'l’éditeur ; l’hébergeur et son service de messagerie (sous-traitants).'],
                ['title' => 'Journaux techniques et sécurité', 'purpose' => 'assurer la sécurité du site, détecter les abus et diagnostiquer les pannes (adresse IP, date, page demandée, erreurs). Un contrôle anti-robot peut être utilisé sur les formulaires.', 'basis' => 'intérêt légitime de sécurité (art. 6.1.f).', 'retention' => '14 jours pour les journaux ; la session de connexion expire après 2 heures d’inactivité.', 'recipients' => 'l’éditeur ; l’hébergeur (sous-traitant) ; si le contrôle Cloudflare Turnstile est activé, Cloudflare reçoit votre adresse IP lors de l’envoi d’un formulaire.'],
                ['title' => 'Fiches de professionnels issues de sources ouvertes', 'purpose' => 'informer le public : nom de l’entreprise, activité, adresse, coordonnées professionnelles publiques, issues d’OpenStreetMap, de registres publics belges et d’informations publiées par les professionnels. Elles concernent surtout des personnes morales ; pour un indépendant, ce sont ses coordonnées professionnelles.', 'basis' => 'intérêt légitime d’annuaire et d’information du public (art. 6.1.f). Vous pouvez vous y opposer.', 'retention' => 'tant que l’entreprise exerce et jusqu’à demande de suppression.', 'recipients' => 'le public ; l’hébergeur (sous-traitant).'],
            ]],
            ['title' => 'Hébergement et transferts hors UE', 'paragraphs' => [
                'Le site et la base de données sont hébergés par o2switch, en France. Aucune donnée de compte n’est transférée hors de l’Union européenne.',
                'Les cartes affichent des tuiles d’OpenStreetMap : lorsqu’une carte est visible, votre navigateur contacte les serveurs de l’OpenStreetMap Foundation, qui voit votre adresse IP. Les polices de caractères sont hébergées sur notre propre serveur.',
            ]],
            ['title' => 'Aucun traqueur', 'paragraphs' => [
                'Doggys n’utilise ni publicité, ni outil de mesure d’audience, ni pixel de réseau social, ni cookie de suivi. Nous ne vendons ni ne louons aucune donnée.',
            ], 'links' => ['cookies' => 'Cookies']],
            ['title' => 'Vos droits', 'paragraphs' => [
                'Vous avez le droit d’accès, de rectification, d’effacement, de limitation, d’opposition et de portabilité de vos données.',
            ], 'list' => [
                'Utilisateurs : depuis votre compte, « Mes données » exporte toutes vos données dans un fichier, et la suppression du compte efface vos données personnelles. Vous pouvez aussi modifier votre profil, vos chiens et vos avis à tout moment.',
                'Professionnels : pour corriger ou faire supprimer une fiche, ou vous opposer à son affichage, utilisez le formulaire de signalement présent en bas de chaque fiche.',
                'Toute autre demande : formulaire de contact ou :email. Nous répondons dans un délai d’un mois.',
            ]],
            ['title' => 'Réclamation', 'paragraphs' => [
                'Si vous estimez que vos droits ne sont pas respectés, vous pouvez introduire une plainte auprès de l’Autorité de protection des données (APD/GBA), Rue de la Presse 35, 1000 Bruxelles, autoriteprotectiondonnees.be.',
            ]],
            ['title' => 'Sécurité et mineurs', 'paragraphs' => [
                'Les mots de passe sont chiffrés, les échanges protégés par HTTPS et les justificatifs conservés hors de l’espace public du serveur. Les comptes sont réservés aux personnes de 16 ans et plus.',
                'Nous pouvons mettre cette politique à jour ; la date de dernière mise à jour figure en haut de page.',
            ]],
        ],
    ],

    'terms' => [
        'meta_title' => 'Conditions d’utilisation',
        'meta_description' => 'Règles d’utilisation de Doggys : gratuité, comptes, avis, signalement des contenus illicites, responsabilité et droit belge.',
        'breadcrumb' => 'Conditions d’utilisation',
        'title' => 'Conditions d’utilisation',
        'intro' => 'Les règles simples qui permettent à Doggys de rester gratuit, fiable et bienveillant.',
        'sections' => [
            ['title' => 'Objet et acceptation', 'paragraphs' => [
                'Ces conditions régissent l’utilisation du site doggys.be, édité par Erwan Goossens (activité Novexa Digital, voir les mentions légales). En utilisant le site ou en créant un compte, vous les acceptez.',
            ], 'links' => ['notice' => 'Mentions légales']],
            ['title' => 'Un service gratuit', 'paragraphs' => [
                'Consulter l’annuaire, créer un compte, publier un avis et gérer la fiche d’une entreprise sont gratuits. Doggys ne vend pas de classement et n’affiche pas de publicité. Le service est fourni « en l’état » et peut évoluer ou être interrompu.',
            ]],
            ['title' => 'Comptes', 'paragraphs' => [
                'Vous devez fournir des informations exactes, avoir 16 ans au moins et protéger votre mot de passe. Un compte est personnel. Vous pouvez le supprimer à tout moment depuis votre espace. Nous pouvons suspendre ou supprimer un compte en cas de fraude, d’abus ou de violation de ces conditions.',
                'Un professionnel qui réclame une fiche doit réellement représenter l’entreprise et fournir des informations véridiques.',
            ]],
            ['title' => 'Avis', 'paragraphs' => [
                'Vous ne pouvez donner un avis que sur une expérience réelle et personnelle, sans propos injurieux, discriminatoire ou diffamatoire, ni donnée personnelle de tiers. Les professionnels ne peuvent pas noter leur propre fiche. Les règles complètes, la modération et le droit de réponse sont détaillés dans la page ci-dessous.',
            ], 'links' => ['reviews' => 'Comment les avis sont vérifiés']],
            ['title' => 'Signaler un contenu illicite', 'paragraphs' => [
                'Conformément au règlement européen sur les services numériques (DSA), toute personne peut nous notifier un contenu qu’elle estime illicite, via les boutons « Signaler » du site ou le formulaire de contact. Indiquez l’adresse de la page, les raisons pour lesquelles le contenu est illicite et vos coordonnées.',
                'Nous examinons chaque notification avec diligence, retirons ou masquons le contenu manifestement illicite et informons l’auteur de la décision et de ses motifs. L’auteur peut la contester en nous écrivant.',
            ], 'links' => ['contact' => 'Contact']],
            ['title' => 'Responsabilité', 'paragraphs' => [
                'Les fiches et avis sont fournis à titre informatif. Nous nous efforçons de maintenir des informations exactes, mais nous ne garantissons ni leur exhaustivité ni leur actualité, et nous ne recommandons aucun professionnel. Vérifiez toujours les qualifications et les tarifs auprès du professionnel. En cas d’urgence pour votre animal, contactez directement un vétérinaire.',
                'Doggys n’est pas partie aux relations entre utilisateurs et professionnels et n’en est pas responsable. Les avis n’engagent que leurs auteurs. Rien dans ces conditions ne limite une responsabilité qui ne peut légalement être limitée, notamment en cas de faute intentionnelle ou de dol.',
            ]],
            ['title' => 'Propriété intellectuelle', 'paragraphs' => [
                'Le contenu éditorial et le design du site sont protégés. Vous conservez vos droits sur vos avis et accordez à Doggys une licence gratuite et non exclusive pour les afficher tant qu’ils sont publiés. Les données cartographiques sont soumises aux licences indiquées dans les mentions légales.',
            ]],
            ['title' => 'Données personnelles', 'paragraphs' => [
                'Le traitement de vos données est décrit dans la politique de confidentialité.',
            ], 'links' => ['privacy' => 'Confidentialité', 'cookies' => 'Cookies']],
            ['title' => 'Droit applicable et litiges', 'paragraphs' => [
                'Ces conditions sont soumises au droit belge. Avant toute action, contactez-nous : nous cherchons toujours une solution amiable. Les consommateurs peuvent aussi utiliser la plateforme européenne de règlement en ligne des litiges. À défaut d’accord, les tribunaux belges compétents sont seuls compétents, sans préjudice des règles impératives de protection des consommateurs.',
            ]],
        ],
    ],

    'cookies' => [
        'meta_title' => 'Cookies',
        'meta_description' => 'Doggys n’utilise que des cookies essentiels (session, sécurité) : aucun bandeau de consentement n’est nécessaire.',
        'breadcrumb' => 'Cookies',
        'title' => 'Cookies',
        'intro' => 'Doggys n’utilise que les cookies strictement nécessaires au fonctionnement du site. Pas de suivi, donc pas de bandeau à valider.',
        'sections' => [
            ['title' => 'Pourquoi pas de bandeau ?', 'paragraphs' => [
                'La loi belge (loi du 13 juin 2005 relative aux communications électroniques) et la directive ePrivacy imposent de demander votre accord pour les cookies, sauf ceux qui sont strictement nécessaires à un service que vous demandez. Doggys n’en utilise pas d’autres : aucun consentement n’est donc requis, et un bandeau n’aurait aucun sens.',
            ]],
            ['title' => 'Les cookies utilisés', 'list' => [
                'Cookie de session : maintient votre connexion et vos messages de confirmation. Durée : 2 heures d’inactivité au maximum.',
                'XSRF-TOKEN : protège les formulaires contre les requêtes frauduleuses (CSRF). Durée : celle de la session.',
                'Cookie « se souvenir de moi » : déposé uniquement si vous cochez cette case à la connexion.',
            ]],
            ['title' => 'Ce que nous n’utilisons pas', 'paragraphs' => [
                'Aucun cookie publicitaire, aucun outil de mesure d’audience, aucun pixel de réseau social, aucune vidéo ni police chargée depuis un tiers. Les cartes affichent des tuiles d’OpenStreetMap, sans cookie de notre part.',
                'Si le contrôle anti-robot Cloudflare Turnstile est activé sur les formulaires, il sert uniquement à bloquer les robots.',
            ]],
            ['title' => 'Les gérer', 'paragraphs' => [
                'Vous pouvez supprimer ou bloquer les cookies dans les réglages de votre navigateur ; la connexion à votre compte ne fonctionnera alors plus. Le traitement de vos données est décrit dans la politique de confidentialité.',
            ], 'links' => ['privacy' => 'Confidentialité']],
        ],
    ],
];
