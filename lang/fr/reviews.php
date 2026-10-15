<?php

return [
    'reply_from' => 'Réponse de :name',

    'form' => [
        'title' => 'Donner votre avis',
        'rating' => 'Votre note',
        'rating_hint' => '1 étoile = très déçu·e, 5 étoiles = ravi·e.',
        'stars' => '{1} :count étoile sur 5|[2,*] :count étoiles sur 5',
        'body' => 'Votre expérience',
        'body_hint' => 'Entre 20 et 2 000 caractères. Racontez ce qui s’est passé, avec bienveillance et sans données personnelles.',
        'published_as' => 'Votre avis sera publié tout de suite, sous le nom « :name ».',
        'rules_link' => 'Comment les avis sont vérifiés',
        'submit' => 'Publier mon avis',
    ],

    'blocked' => [
        'guest' => 'Connectez-vous ou créez un compte gratuit pour partager votre expérience.',
        'unverified' => 'Confirmez d’abord votre adresse e-mail pour publier un avis.',
        'unverified_link' => 'Renvoyer le lien de confirmation',
        'own_listing' => 'Vous gérez cette fiche : vous ne pouvez pas la noter, mais vous pouvez répondre aux avis.',
        'own_listing_link' => 'Répondre aux avis',
        'cooldown' => 'Vous avez déjà donné votre avis sur ce professionnel ces :days derniers jours. Vous pouvez le modifier depuis « Mes avis ».',
        'daily_limit' => 'Vous avez publié :limit avis en 24 heures : c’est le maximum. Revenez demain !',
    ],

    'report' => [
        'button' => 'Signaler cet avis',
        'reason' => 'Pourquoi signalez-vous cet avis ?',
        'reason_hint' => 'Propos injurieux, faux avis, données personnelles, conflit d’intérêts… (10 caractères minimum).',
        'submit' => 'Envoyer le signalement',
        'done' => 'Vous avez signalé cet avis. Merci, nous l’examinons.',
    ],

    'flash' => [
        'published' => 'Merci ! Votre avis est publié.',
        'updated' => 'Votre avis a été mis à jour.',
        'deleted' => 'Votre avis a été supprimé.',
        'reported' => 'Merci, votre signalement a été transmis à l’équipe de modération.',
        'already_reported' => 'Vous avez déjà signalé cet avis.',
    ],

    'mine' => [
        'tab' => 'Mes avis',
        'title' => 'Mes avis',
        'meta' => 'Retrouvez, modifiez ou supprimez les avis que vous avez publiés sur Doggys.',
        'subtitle' => 'Les avis que vous avez partagés. Vous pouvez les modifier ou les supprimer à tout moment.',
        'empty_title' => 'Aucun avis pour l’instant',
        'empty_text' => 'Votre toiletteur, votre vétérinaire ou votre éducateur vous a fait bonne impression ? Aidez les autres familles à choisir.',
        'empty_cta' => 'Trouver un professionnel',
        'provider_gone' => 'Fiche retirée',
        'hidden' => 'Masqué par la modération',
        'edit' => 'Modifier',
        'delete' => 'Supprimer',
        'delete_confirm' => 'Supprimer définitivement cet avis ?',
    ],

    'edit' => [
        'title' => 'Modifier mon avis',
        'subtitle' => 'Votre avis sur :name.',
        'submit' => 'Enregistrer les modifications',
    ],

    'mail' => [
        'hidden_subject' => 'Votre avis a été masqué',
        'hidden_intro' => 'Votre avis sur « :name » a été masqué par la modération de Doggys à la suite d’un examen.',
        'hidden_reason' => 'Motif : :reason',
        'hidden_default_reason' => 'l’avis ne respecte pas nos règles de publication (propos injurieux, contenu sans rapport avec l’expérience vécue, données personnelles ou conflit d’intérêts).',
        'hidden_redress' => 'Vous pouvez contester cette décision en nous écrivant : nous réexaminerons votre avis.',
        'hidden_action' => 'Contester la décision',
    ],

    'policy' => [
        'meta_title' => 'Comment les avis sont vérifiés',
        'meta_description' => 'Qui peut publier un avis sur Doggys, ce que nous vérifions, comment les avis sont classés, signalés et modérés : nos règles, en toute transparence.',
        'breadcrumb' => 'Avis vérifiés',
        'eyebrow' => 'Transparence',
        'title' => 'Comment les avis sont vérifiés',
        'intro' => 'Les avis aident les familles à choisir un professionnel en confiance. Voici, conformément à la directive européenne « Omnibus » et au règlement sur les services numériques (DSA), comment nous les recueillons, les vérifions et les modérons.',
        'summary_title' => 'L’essentiel',
        'summary' => [
            'Seul un compte avec une adresse e-mail confirmée peut publier un avis.',
            'Nous ne vérifions pas l’achat ou la prestation : nos avis ne sont pas des « avis d’achat vérifiés ».',
            'Tous les avis sont publiés, positifs comme négatifs, du plus récent au plus ancien.',
            'Personne ne peut payer pour masquer, supprimer ou mettre en avant un avis.',
        ],
        'sections' => [
            [
                'title' => 'Qui peut publier un avis ?',
                'paragraphs' => [
                    'Toute personne disposant d’un compte Doggys gratuit dont l’adresse e-mail a été confirmée. Un avis comprend une note de 1 à 5 étoiles et un texte de 20 à 2 000 caractères. Il est publié sous le prénom et l’initiale du nom de son auteur (par exemple « Camille D. »).',
                ],
            ],
            [
                'title' => 'Ce que nous vérifions, et ce que nous ne vérifions pas',
                'paragraphs' => [
                    'Nous vérifions que chaque avis provient d’un compte dont l’adresse e-mail a été confirmée, et nous appliquons des limites contre les abus : un seul avis par professionnel et par compte tous les 30 jours, cinq avis au maximum par compte sur 24 heures, et aucun avis possible sur une fiche que l’on gère soi-même.',
                    'Nous ne vérifions pas que l’auteur a réellement utilisé les services du professionnel : nous ne demandons ni facture ni preuve de rendez-vous. Les avis reflètent l’expérience déclarée par leurs auteurs.',
                    'Nous n’acceptons aucun avis rémunéré ou rédigé en échange d’un avantage, et nous n’en sollicitons pas.',
                ],
            ],
            [
                'title' => 'Publication, classement et note moyenne',
                'paragraphs' => [
                    'Les avis sont publiés immédiatement, sans tri préalable. Ils sont affichés du plus récent au plus ancien, quelle que soit leur note.',
                    'La note moyenne d’une fiche est la moyenne simple des notes de tous les avis publiés. Elle est recalculée à chaque nouvel avis, modification, masquage ou suppression.',
                    'Un avis n’est jamais retiré parce qu’il est négatif. Aucun professionnel ne peut payer pour masquer, supprimer ou mettre en avant un avis.',
                ],
            ],
            [
                'title' => 'Le droit de réponse des professionnels',
                'paragraphs' => [
                    'Le professionnel dont la fiche a été validée par notre équipe peut répondre publiquement à chaque avis. Sa réponse s’affiche sous l’avis et peut être modifiée.',
                ],
            ],
            [
                'title' => 'Signaler un avis',
                'paragraphs' => [
                    'Sous chaque avis, le bouton « Signaler cet avis » permet à toute personne connectée de nous signaler un contenu qui lui semble illicite ou contraire à nos règles : propos injurieux ou discriminatoires, faux avis, données personnelles, conflit d’intérêts ou texte sans rapport avec l’expérience vécue. Sans compte, utilisez notre formulaire de contact en indiquant le lien de l’avis.',
                ],
            ],
            [
                'title' => 'Modération et motivation des décisions',
                'paragraphs' => [
                    'Chaque signalement est examiné par une personne, sans décision automatisée. Si un avis enfreint nos règles, il est masqué et la note moyenne est recalculée. Son auteur reçoit un e-mail qui explique la décision et son motif.',
                    'Si le signalement n’est pas fondé, l’avis reste en ligne.',
                ],
            ],
            [
                'title' => 'Contester une décision',
                'paragraphs' => [
                    'L’auteur d’un avis masqué, comme la personne à l’origine d’un signalement, peut contester la décision via notre formulaire de contact : nous réexaminons alors le dossier. Vous gardez aussi la possibilité de saisir le SPF Économie ou les juridictions compétentes.',
                ],
            ],
            [
                'title' => 'Vos avis vous appartiennent',
                'paragraphs' => [
                    'Vous pouvez modifier ou supprimer vos avis à tout moment depuis « Mes avis » dans votre compte. La suppression de votre compte supprime aussi tous vos avis.',
                ],
            ],
        ],
        'cta_title' => 'Une question sur un avis ?',
        'cta_text' => 'Écrivez-nous : nous répondons à chaque message.',
        'cta' => 'Nous contacter',
    ],
];
