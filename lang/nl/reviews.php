<?php

return [
    'reply_from' => 'Antwoord van :name',

    'form' => [
        'title' => 'Deel uw ervaring',
        'rating' => 'Uw score',
        'rating_hint' => '1 ster = erg teleurgesteld, 5 sterren = heel tevreden.',
        'stars' => '{1} :count ster op 5|[2,*] :count sterren op 5',
        'body' => 'Uw ervaring',
        'body_hint' => 'Tussen 20 en 2.000 tekens. Vertel wat er gebeurde, vriendelijk en zonder persoonsgegevens.',
        'published_as' => 'Uw review verschijnt meteen, onder de naam ‘:name’.',
        'rules_link' => 'Hoe reviews worden gecontroleerd',
        'submit' => 'Mijn review plaatsen',
    ],

    'blocked' => [
        'guest' => 'Meld u aan of maak een gratis account aan om uw ervaring te delen.',
        'unverified' => 'Bevestig eerst uw e-mailadres om een review te plaatsen.',
        'unverified_link' => 'Bevestigingslink opnieuw versturen',
        'own_listing' => 'U beheert deze fiche: u kunt ze niet beoordelen, maar wel op de reviews antwoorden.',
        'own_listing_link' => 'Op reviews antwoorden',
        'cooldown' => 'U hebt deze professional de afgelopen :days dagen al beoordeeld. U kunt uw review aanpassen via ‘Mijn reviews’.',
        'daily_limit' => 'U hebt :limit reviews geplaatst in 24 uur: dat is het maximum. Probeer het morgen opnieuw!',
    ],

    'report' => [
        'button' => 'Deze review melden',
        'reason' => 'Waarom meldt u deze review?',
        'reason_hint' => 'Beledigende taal, valse review, persoonsgegevens, belangenconflict… (minstens 10 tekens).',
        'submit' => 'Melding versturen',
        'done' => 'U hebt deze review gemeld. Bedankt, we bekijken het.',
    ],

    'flash' => [
        'published' => 'Bedankt! Uw review staat online.',
        'updated' => 'Uw review is bijgewerkt.',
        'deleted' => 'Uw review is verwijderd.',
        'reported' => 'Bedankt, uw melding is doorgegeven aan ons moderatieteam.',
        'already_reported' => 'U hebt deze review al gemeld.',
    ],

    'mine' => [
        'tab' => 'Mijn reviews',
        'title' => 'Mijn reviews',
        'meta' => 'Bekijk, wijzig of verwijder de reviews die u op Doggys hebt geplaatst.',
        'subtitle' => 'De reviews die u hebt gedeeld. U kunt ze op elk moment wijzigen of verwijderen.',
        'empty_title' => 'Nog geen reviews',
        'empty_text' => 'Was u tevreden over uw trimmer, dierenarts of hondentrainer? Help andere gezinnen kiezen.',
        'empty_cta' => 'Een professional vinden',
        'provider_gone' => 'Fiche verwijderd',
        'hidden' => 'Verborgen door moderatie',
        'edit' => 'Wijzigen',
        'delete' => 'Verwijderen',
        'delete_confirm' => 'Deze review definitief verwijderen?',
    ],

    'edit' => [
        'title' => 'Mijn review wijzigen',
        'subtitle' => 'Uw review over :name.',
        'submit' => 'Wijzigingen opslaan',
    ],

    'mail' => [
        'hidden_subject' => 'Uw review werd verborgen',
        'hidden_intro' => 'Na onderzoek heeft de moderatie van Doggys uw review over ‘:name’ verborgen.',
        'hidden_reason' => 'Reden: :reason',
        'hidden_default_reason' => 'de review volgt onze publicatieregels niet (beledigende taal, inhoud die niets met de ervaring te maken heeft, persoonsgegevens of belangenconflict).',
        'hidden_redress' => 'U kunt deze beslissing betwisten door ons te schrijven: we bekijken uw review dan opnieuw.',
        'hidden_action' => 'De beslissing betwisten',
    ],

    'policy' => [
        'meta_title' => 'Hoe reviews worden gecontroleerd',
        'meta_description' => 'Wie een review mag plaatsen op Doggys, wat we controleren en hoe reviews gerangschikt, gemeld en gemodereerd worden: onze regels, volledig transparant.',
        'breadcrumb' => 'Gecontroleerde reviews',
        'eyebrow' => 'Transparantie',
        'title' => 'Hoe reviews worden gecontroleerd',
        'intro' => 'Reviews helpen gezinnen om met vertrouwen een professional te kiezen. Volgens de Europese Omnibusrichtlijn en de digitaledienstenverordening (DSA) leggen we hier uit hoe we ze verzamelen, controleren en modereren.',
        'summary_title' => 'In het kort',
        'summary' => [
            'Alleen een account met een bevestigd e-mailadres kan een review plaatsen.',
            'We controleren geen aankoop of afspraak: onze reviews zijn geen ‘geverifieerde aankopen’.',
            'Alle reviews worden gepubliceerd, positief én negatief, van nieuw naar oud.',
            'Niemand kan betalen om een review te verbergen, te verwijderen of uit te lichten.',
        ],
        'sections' => [
            [
                'title' => 'Wie mag een review plaatsen?',
                'paragraphs' => [
                    'Iedereen met een gratis Doggys-account waarvan het e-mailadres bevestigd is. Een review bestaat uit een score van 1 tot 5 sterren en een tekst van 20 tot 2.000 tekens. Hij verschijnt onder de voornaam en de eerste letter van de achternaam van de auteur (bijvoorbeeld ‘Camille D.’).',
                ],
            ],
            [
                'title' => 'Wat we wel en niet controleren',
                'paragraphs' => [
                    'We controleren dat elke review afkomstig is van een account met een bevestigd e-mailadres, en we hanteren limieten tegen misbruik: één review per professional en per account om de 30 dagen, maximaal vijf reviews per account binnen 24 uur, en geen review op een fiche die u zelf beheert.',
                    'We controleren niet of de auteur echt een beroep deed op de professional: we vragen geen factuur of bewijs van afspraak. Reviews geven de ervaring weer zoals de auteurs die beschrijven.',
                    'We aanvaarden geen betaalde reviews of reviews in ruil voor een voordeel, en we vragen er ook niet om.',
                ],
            ],
            [
                'title' => 'Publicatie, volgorde en gemiddelde score',
                'paragraphs' => [
                    'Reviews worden onmiddellijk gepubliceerd, zonder voorafgaande selectie. Ze staan van nieuw naar oud, ongeacht hun score.',
                    'De gemiddelde score van een fiche is het gewone gemiddelde van alle gepubliceerde reviews. Ze wordt opnieuw berekend bij elke nieuwe, gewijzigde, verborgen of verwijderde review.',
                    'Een review wordt nooit verwijderd omdat hij negatief is. Geen enkele professional kan betalen om een review te verbergen, te verwijderen of uit te lichten.',
                ],
            ],
            [
                'title' => 'Het recht op antwoord van professionals',
                'paragraphs' => [
                    'De professional van wie de fiche door ons team gevalideerd is, kan publiek op elke review antwoorden. Het antwoord verschijnt onder de review en kan aangepast worden.',
                ],
            ],
            [
                'title' => 'Een review melden',
                'paragraphs' => [
                    'Onder elke review kan iedereen die aangemeld is met de knop ‘Deze review melden’ inhoud signaleren die volgens hem onwettig is of tegen onze regels ingaat: beledigende of discriminerende taal, valse reviews, persoonsgegevens, belangenconflicten of tekst die niets met de ervaring te maken heeft. Zonder account gebruikt u ons contactformulier, met de link naar de review.',
                ],
            ],
            [
                'title' => 'Moderatie en motivering van beslissingen',
                'paragraphs' => [
                    'Elke melding wordt door een mens beoordeeld, zonder geautomatiseerde beslissing. Gaat een review in tegen onze regels, dan wordt hij verborgen en wordt de gemiddelde score herberekend. De auteur krijgt een e-mail met de beslissing en de reden ervan.',
                    'Is de melding ongegrond, dan blijft de review online.',
                ],
            ],
            [
                'title' => 'Een beslissing betwisten',
                'paragraphs' => [
                    'De auteur van een verborgen review en wie hem gemeld heeft, kunnen de beslissing betwisten via ons contactformulier: we bekijken het dossier dan opnieuw. U kunt zich ook wenden tot de FOD Economie of de bevoegde rechtbanken.',
                ],
            ],
            [
                'title' => 'Uw reviews zijn van u',
                'paragraphs' => [
                    'U kunt uw reviews op elk moment wijzigen of verwijderen via ‘Mijn reviews’ in uw account. Als u uw account verwijdert, worden ook al uw reviews verwijderd.',
                ],
            ],
        ],
        'cta_title' => 'Een vraag over een review?',
        'cta_text' => 'Schrijf ons: we beantwoorden elk bericht.',
        'cta' => 'Contact opnemen',
    ],
];
