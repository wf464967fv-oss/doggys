<?php

return [
    'reply_from' => 'Antwort von :name',

    'form' => [
        'title' => 'Ihre Erfahrung teilen',
        'rating' => 'Ihre Bewertung',
        'rating_hint' => '1 Stern = sehr enttäuscht, 5 Sterne = begeistert.',
        'stars' => '{1} :count Stern von 5|[2,*] :count Sterne von 5',
        'body' => 'Ihre Erfahrung',
        'body_hint' => 'Zwischen 20 und 2.000 Zeichen. Erzählen Sie freundlich, was passiert ist, ohne persönliche Daten.',
        'published_as' => 'Ihre Bewertung erscheint sofort, unter dem Namen „:name“.',
        'rules_link' => 'Wie Bewertungen geprüft werden',
        'submit' => 'Bewertung veröffentlichen',
    ],

    'blocked' => [
        'guest' => 'Melden Sie sich an oder erstellen Sie ein kostenloses Konto, um Ihre Erfahrung zu teilen.',
        'unverified' => 'Bitte bestätigen Sie zuerst Ihre E-Mail-Adresse, um eine Bewertung zu schreiben.',
        'unverified_link' => 'Bestätigungslink erneut senden',
        'own_listing' => 'Sie verwalten diesen Eintrag: Sie können ihn nicht bewerten, aber auf Bewertungen antworten.',
        'own_listing_link' => 'Auf Bewertungen antworten',
        'cooldown' => 'Sie haben diesen Profi in den letzten :days Tagen bereits bewertet. Unter „Meine Bewertungen“ können Sie Ihre Bewertung ändern.',
        'daily_limit' => 'Sie haben :limit Bewertungen in 24 Stunden veröffentlicht – mehr geht nicht. Morgen wieder!',
    ],

    'report' => [
        'button' => 'Bewertung melden',
        'reason' => 'Warum melden Sie diese Bewertung?',
        'reason_hint' => 'Beleidigungen, gefälschte Bewertung, persönliche Daten, Interessenkonflikt … (mindestens 10 Zeichen).',
        'submit' => 'Meldung senden',
        'done' => 'Sie haben diese Bewertung gemeldet. Danke, wir prüfen sie.',
    ],

    'flash' => [
        'published' => 'Danke! Ihre Bewertung ist online.',
        'updated' => 'Ihre Bewertung wurde aktualisiert.',
        'deleted' => 'Ihre Bewertung wurde gelöscht.',
        'reported' => 'Danke, Ihre Meldung wurde an unser Moderationsteam weitergeleitet.',
        'already_reported' => 'Sie haben diese Bewertung bereits gemeldet.',
    ],

    'mine' => [
        'tab' => 'Meine Bewertungen',
        'title' => 'Meine Bewertungen',
        'meta' => 'Ihre auf Doggys veröffentlichten Bewertungen ansehen, ändern oder löschen.',
        'subtitle' => 'Die Bewertungen, die Sie geteilt haben. Sie können sie jederzeit ändern oder löschen.',
        'empty_title' => 'Noch keine Bewertungen',
        'empty_text' => 'Ihr Hundefriseur, Tierarzt oder Hundetrainer hat Sie überzeugt? Helfen Sie anderen Familien bei der Wahl.',
        'empty_cta' => 'Einen Profi finden',
        'provider_gone' => 'Eintrag entfernt',
        'hidden' => 'Von der Moderation ausgeblendet',
        'edit' => 'Ändern',
        'delete' => 'Löschen',
        'delete_confirm' => 'Diese Bewertung endgültig löschen?',
    ],

    'edit' => [
        'title' => 'Meine Bewertung ändern',
        'subtitle' => 'Ihre Bewertung von :name.',
        'submit' => 'Änderungen speichern',
    ],

    'mail' => [
        'hidden_subject' => 'Ihre Bewertung wurde ausgeblendet',
        'hidden_intro' => 'Nach einer Prüfung hat die Doggys-Moderation Ihre Bewertung von „:name“ ausgeblendet.',
        'hidden_reason' => 'Grund: :reason',
        'hidden_default_reason' => 'Die Bewertung entspricht nicht unseren Veröffentlichungsregeln (Beleidigungen, Inhalt ohne Bezug zur Erfahrung, persönliche Daten oder Interessenkonflikt).',
        'hidden_redress' => 'Sie können diese Entscheidung anfechten, indem Sie uns schreiben: Wir prüfen Ihre Bewertung dann erneut.',
        'hidden_action' => 'Entscheidung anfechten',
    ],

    'policy' => [
        'meta_title' => 'Wie Bewertungen geprüft werden',
        'meta_description' => 'Wer auf Doggys bewerten darf, was wir prüfen und wie Bewertungen sortiert, gemeldet und moderiert werden: unsere Regeln, ganz transparent.',
        'breadcrumb' => 'Geprüfte Bewertungen',
        'eyebrow' => 'Transparenz',
        'title' => 'Wie Bewertungen geprüft werden',
        'intro' => 'Bewertungen helfen Familien, einen Profi mit Vertrauen zu wählen. Gemäß der europäischen Omnibus-Richtlinie und dem Gesetz über digitale Dienste (DSA) erklären wir hier, wie wir sie sammeln, prüfen und moderieren.',
        'summary_title' => 'Das Wichtigste',
        'summary' => [
            'Nur ein Konto mit bestätigter E-Mail-Adresse kann eine Bewertung veröffentlichen.',
            'Wir prüfen weder Kauf noch Termin: Unsere Bewertungen sind keine „verifizierten Käufe“.',
            'Alle Bewertungen werden veröffentlicht, positive wie negative, von neu nach alt.',
            'Niemand kann dafür bezahlen, eine Bewertung auszublenden, zu löschen oder hervorzuheben.',
        ],
        'sections' => [
            [
                'title' => 'Wer darf eine Bewertung schreiben?',
                'paragraphs' => [
                    'Alle Personen mit einem kostenlosen Doggys-Konto, dessen E-Mail-Adresse bestätigt wurde. Eine Bewertung besteht aus 1 bis 5 Sternen und einem Text mit 20 bis 2.000 Zeichen. Sie erscheint unter dem Vornamen und dem ersten Buchstaben des Nachnamens (zum Beispiel „Camille D.“).',
                ],
            ],
            [
                'title' => 'Was wir prüfen – und was nicht',
                'paragraphs' => [
                    'Wir prüfen, dass jede Bewertung von einem Konto mit bestätigter E-Mail-Adresse stammt, und setzen Grenzen gegen Missbrauch: eine Bewertung pro Profi und Konto alle 30 Tage, höchstens fünf Bewertungen pro Konto innerhalb von 24 Stunden und keine Bewertung eines Eintrags, den man selbst verwaltet.',
                    'Wir prüfen nicht, ob die Person die Leistungen des Profis tatsächlich genutzt hat: Wir verlangen weder Rechnung noch Terminnachweis. Bewertungen geben die Erfahrung wieder, die ihre Verfasser schildern.',
                    'Wir akzeptieren keine bezahlten Bewertungen oder Bewertungen gegen eine Gegenleistung und fordern auch keine an.',
                ],
            ],
            [
                'title' => 'Veröffentlichung, Reihenfolge und Durchschnittsnote',
                'paragraphs' => [
                    'Bewertungen werden sofort und ohne vorherige Auswahl veröffentlicht. Sie erscheinen von neu nach alt, unabhängig von ihrer Note.',
                    'Die Durchschnittsnote eines Eintrags ist der einfache Durchschnitt aller veröffentlichten Bewertungen. Sie wird bei jeder neuen, geänderten, ausgeblendeten oder gelöschten Bewertung neu berechnet.',
                    'Eine Bewertung wird nie entfernt, weil sie negativ ist. Kein Profi kann dafür bezahlen, eine Bewertung auszublenden, zu löschen oder hervorzuheben.',
                ],
            ],
            [
                'title' => 'Das Antwortrecht der Profis',
                'paragraphs' => [
                    'Profis, deren Eintrag von unserem Team bestätigt wurde, können öffentlich auf jede Bewertung antworten. Die Antwort erscheint unter der Bewertung und kann geändert werden.',
                ],
            ],
            [
                'title' => 'Eine Bewertung melden',
                'paragraphs' => [
                    'Unter jeder Bewertung können angemeldete Personen mit „Bewertung melden“ Inhalte melden, die sie für rechtswidrig oder regelwidrig halten: beleidigende oder diskriminierende Äußerungen, gefälschte Bewertungen, persönliche Daten, Interessenkonflikte oder Texte ohne Bezug zur Erfahrung. Ohne Konto nutzen Sie unser Kontaktformular und geben den Link zur Bewertung an.',
                ],
            ],
            [
                'title' => 'Moderation und Begründung der Entscheidungen',
                'paragraphs' => [
                    'Jede Meldung wird von einem Menschen geprüft, ohne automatisierte Entscheidung. Verstößt eine Bewertung gegen unsere Regeln, wird sie ausgeblendet und die Durchschnittsnote neu berechnet. Die verfassende Person erhält eine E-Mail mit der Entscheidung und ihrer Begründung.',
                    'Ist die Meldung unbegründet, bleibt die Bewertung online.',
                ],
            ],
            [
                'title' => 'Eine Entscheidung anfechten',
                'paragraphs' => [
                    'Wer eine ausgeblendete Bewertung verfasst oder eine Bewertung gemeldet hat, kann die Entscheidung über unser Kontaktformular anfechten: Wir prüfen den Fall dann erneut. Sie können sich auch an den FÖD Wirtschaft oder die zuständigen Gerichte wenden.',
                ],
            ],
            [
                'title' => 'Ihre Bewertungen gehören Ihnen',
                'paragraphs' => [
                    'Sie können Ihre Bewertungen jederzeit unter „Meine Bewertungen“ in Ihrem Konto ändern oder löschen. Wenn Sie Ihr Konto löschen, werden auch alle Ihre Bewertungen gelöscht.',
                ],
            ],
        ],
        'cta_title' => 'Eine Frage zu einer Bewertung?',
        'cta_text' => 'Schreiben Sie uns: Wir beantworten jede Nachricht.',
        'cta' => 'Kontakt aufnehmen',
    ],
];
