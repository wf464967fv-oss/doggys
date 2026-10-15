<?php

/*
 * Legal pages (see resources/views/pages/legal.blade.php). Placeholders: :bce :address :email.
 */
return [
    'updated_on' => '2026-10-15',
    'updated' => 'Zuletzt aktualisiert: :date',
    'toc' => 'Inhalt',
    'placeholder' => '[noch zu ergänzen]',
    'labels' => ['purpose' => 'Zweck', 'basis' => 'Rechtsgrundlage', 'retention' => 'Speicherdauer', 'recipients' => 'Empfänger'],
    'cta_title' => 'Eine Frage zu Ihren Daten oder zur Website?',
    'cta_text' => 'Schreiben Sie uns: Wir beantworten jede Anfrage spätestens innerhalb eines Monats.',
    'cta' => 'Kontakt aufnehmen',

    'notice' => [
        'meta_title' => 'Impressum',
        'meta_description' => 'Herausgeber, Hoster und Quellenangaben von Doggys, dem kostenlosen belgischen Verzeichnis für Hundeprofis.',
        'breadcrumb' => 'Impressum',
        'title' => 'Impressum',
        'intro' => 'Wer Doggys herausgibt, wer es hostet und woher die angezeigten Daten stammen.',
        'sections' => [
            ['title' => 'Herausgeber', 'paragraphs' => [
                'Die Website doggys.be wird herausgegeben von Erwan Goossens, Selbstständiger im Nebenberuf, unter der Geschäftsbezeichnung Novexa Digital.',
            ], 'list' => [
                'Unternehmensnummer (BCE/KBO): :bce',
                'Anschrift: :address',
                'E-Mail: :email',
                'Verantwortlich für den Inhalt: Erwan Goossens',
            ]],
            ['title' => 'Hosting', 'paragraphs' => [
                'Die Website wird von o2switch SAS, 222-224 Boulevard Gustave Flaubert, 63000 Clermont-Ferrand, Frankreich (o2switch.fr), gehostet. Die Daten werden in der Europäischen Union gespeichert.',
            ]],
            ['title' => 'Art des Dienstes', 'paragraphs' => [
                'Doggys ist ein kostenloses, werbefreies Verzeichnis von Hundeprofis in Belgien. Doggys ist weder Tierarztpraxis noch Dienstleister und ist nicht Partei der Beziehung zwischen Besuchern und Profis.',
                'Die Einträge der Profis stammen aus offenen Quellen und aus Angaben der Profis selbst. Ein Profi kann direkt auf dem Eintrag die Korrektur oder Löschung beantragen.',
            ]],
            ['title' => 'Geistiges Eigentum', 'paragraphs' => [
                'Der Name Doggys, das Logo, das Layout und die redaktionellen Texte (einschließlich der Ratgeber) sind urheberrechtlich geschützt. Eine Vervielfältigung ohne vorherige schriftliche Zustimmung ist untersagt, ausgenommen kurze Zitate mit Quellenangabe.',
                'Bewertungen gehören ihren Verfassern, die Doggys das Recht einräumen, sie auf der Website anzuzeigen.',
            ]],
            ['title' => 'Quellenangaben und Datenlizenzen', 'paragraphs' => [
                'Die Karten und ein Teil der Standortdaten stammen von OpenStreetMap, © OpenStreetMap-Mitwirkende, verfügbar unter der ODbL-Lizenz (openstreetmap.org/copyright).',
                'Postleitzahlen, Gemeinden und Koordinaten stammen von GeoNames (geonames.org) unter der Lizenz Creative Commons Namensnennung 4.0 (CC BY 4.0).',
            ]],
            ['title' => 'Inhalte melden', 'paragraphs' => [
                'Um rechtswidrige Inhalte oder einen Fehler zu melden, nutzen Sie die Melde-Schaltflächen der Website oder das Kontaktformular. Das Verfahren ist in unseren Nutzungsbedingungen beschrieben.',
            ], 'links' => ['terms' => 'Nutzungsbedingungen', 'privacy' => 'Datenschutz', 'contact' => 'Kontakt']],
        ],
    ],

    'privacy' => [
        'meta_title' => 'Datenschutzerklärung',
        'meta_description' => 'Welche Daten Doggys erhebt, warum, wie lange, und wie Sie Ihre DSGVO-Rechte ausüben. Keine Tracker.',
        'breadcrumb' => 'Datenschutz',
        'title' => 'Datenschutzerklärung',
        'intro' => 'Doggys erhebt so wenige Daten wie möglich, hostet sie in der Europäischen Union und verwendet keine Werbe- oder Statistik-Tracker.',
        'sections' => [
            ['title' => 'Verantwortlicher', 'paragraphs' => [
                'Verantwortlicher ist Erwan Goossens (Geschäftsbezeichnung Novexa Digital), Selbstständiger im Nebenberuf. Unternehmensnummer: :bce. Anschrift: :address. Kontakt: :email oder über das Kontaktformular.',
                'Diese Erklärung wendet die Datenschutz-Grundverordnung (DSGVO, Verordnung (EU) 2016/679) und das belgische Gesetz vom 30. Juli 2018 an.',
            ]],
            ['title' => 'Datenverarbeitungen', 'paragraphs' => [
                'Für jede Verarbeitung: warum wir Ihre Daten verwenden, auf welcher Grundlage, wie lange wir sie speichern und wer darauf zugreifen kann.',
            ], 'treatments' => [
                ['title' => 'Benutzerkonto', 'purpose' => 'Ihr Konto (Name, E-Mail, verschlüsseltes Passwort, Rolle) anlegen und verwalten, Ihnen die Anmeldung ermöglichen und Ihnen zu Ihrem Konto schreiben.', 'basis' => 'Erfüllung des von Ihnen gewünschten Dienstes (Art. 6 Abs. 1 lit. b DSGVO).', 'retention' => 'bis zur Löschung Ihres Kontos, die Sie jederzeit vornehmen können.', 'recipients' => 'der Herausgeber; der Hoster o2switch (Auftragsverarbeiter).'],
                ['title' => 'Hundeprofile', 'purpose' => 'Ihnen ein privates Heft für Ihre Hunde ermöglichen (Name, Rasse, Geburtsdatum, Impfungen, Gesundheitsnotizen, Chip). Diese Angaben sind nie öffentlich.', 'basis' => 'Erfüllung des Dienstes (Art. 6 Abs. 1 lit. b).', 'retention' => 'bis Sie sie oder Ihr Konto löschen.', 'recipients' => 'nur Sie; der Hoster (Auftragsverarbeiter).'],
                ['title' => 'Bewertungen', 'purpose' => 'Ihre Bewertung eines Profis veröffentlichen, Missbrauch moderieren und Durchschnittswerte berechnen. Die Bewertung erscheint unter dem Namen Ihres Kontos.', 'basis' => 'Erfüllung des Dienstes (Art. 6 Abs. 1 lit. b) und berechtigtes Interesse an verlässlichen Bewertungen (Art. 6 Abs. 1 lit. f).', 'retention' => 'bis die Bewertung oder das Konto gelöscht wird.', 'recipients' => 'die Öffentlichkeit (veröffentlichte Bewertung); der betroffene Profi, der antworten kann; der Herausgeber.'],
                ['title' => 'Eintragsansprüche und Nachweise', 'purpose' => 'prüfen, ob eine Person das Unternehmen tatsächlich vertritt (BCE-Nummer, Funktion, Nachweis im privaten Speicher).', 'basis' => 'berechtigtes Interesse an der Verhinderung von Identitätsmissbrauch (Art. 6 Abs. 1 lit. f).', 'retention' => 'abgelehnte Nachweise: Löschung nach 90 Tagen; akzeptierte Nachweise: solange der Eintrag verwaltet wird, danach Löschung mit dem Eintrag oder Konto bzw. auf Antrag.', 'recipients' => 'nur der Herausgeber (nie öffentlich); der Hoster (Auftragsverarbeiter).'],
                ['title' => 'Meldungen', 'purpose' => 'Meldungen von Fehlern, Anträge auf Löschung eines Eintrags sowie missbräuchliche Bewertungen oder Inhalte bearbeiten.', 'basis' => 'berechtigtes Interesse und rechtliche Pflicht zur Bearbeitung von Meldungen rechtswidriger Inhalte (Art. 6 Abs. 1 lit. f und c, DSA).', 'retention' => 'bis zu 12 Monate nach Bearbeitung der Meldung.', 'recipients' => 'der Herausgeber; der Hoster (Auftragsverarbeiter).'],
                ['title' => 'Kontaktnachrichten', 'purpose' => 'Ihre Nachricht beantworten (Name, E-Mail, Inhalt).', 'basis' => 'vorvertragliche Maßnahmen und berechtigtes Interesse an einer Antwort (Art. 6 Abs. 1 lit. b und f).', 'retention' => 'bis zu 12 Monate nach Bearbeitung der Nachricht.', 'recipients' => 'der Herausgeber; der Hoster und sein Mailservice (Auftragsverarbeiter).'],
                ['title' => 'Technische Protokolle und Sicherheit', 'purpose' => 'die Website sichern, Missbrauch erkennen und Störungen analysieren (IP-Adresse, Datum, aufgerufene Seite, Fehler). Bei Formularen kann eine Anti-Bot-Prüfung eingesetzt werden.', 'basis' => 'berechtigtes Interesse an der Sicherheit (Art. 6 Abs. 1 lit. f).', 'retention' => '14 Tage für Protokolle; die Anmeldesitzung endet nach 2 Stunden Inaktivität.', 'recipients' => 'der Herausgeber; der Hoster (Auftragsverarbeiter); ist die Cloudflare-Turnstile-Prüfung aktiv, erhält Cloudflare beim Absenden eines Formulars Ihre IP-Adresse.'],
                ['title' => 'Einträge von Profis aus offenen Quellen', 'purpose' => 'die Öffentlichkeit informieren: Firmenname, Tätigkeit, Anschrift und öffentliche geschäftliche Kontaktdaten aus OpenStreetMap, belgischen öffentlichen Registern und von den Profis veröffentlichten Angaben. Sie betreffen überwiegend juristische Personen; bei Einzelunternehmern sind es geschäftliche Kontaktdaten.', 'basis' => 'berechtigtes Interesse am Betrieb eines Verzeichnisses und an der Information der Öffentlichkeit (Art. 6 Abs. 1 lit. f). Sie können widersprechen.', 'retention' => 'solange das Unternehmen tätig ist und bis zu einem Löschantrag.', 'recipients' => 'die Öffentlichkeit; der Hoster (Auftragsverarbeiter).'],
            ]],
            ['title' => 'Hosting und Übermittlung außerhalb der EU', 'paragraphs' => [
                'Website und Datenbank werden von o2switch in Frankreich gehostet. Kontodaten werden nicht außerhalb der Europäischen Union übermittelt.',
                'Karten zeigen OpenStreetMap-Kacheln: Ist eine Karte sichtbar, kontaktiert Ihr Browser die Server der OpenStreetMap Foundation, die Ihre IP-Adresse sehen. Schriftarten werden von unserem eigenen Server geladen.',
            ]],
            ['title' => 'Keine Tracker', 'paragraphs' => [
                'Doggys verwendet keine Werbung, keine Reichweitenmessung, keine Pixel sozialer Netzwerke und keine Tracking-Cookies. Wir verkaufen oder vermieten keine Daten.',
            ], 'links' => ['cookies' => 'Cookies']],
            ['title' => 'Ihre Rechte', 'paragraphs' => [
                'Sie haben das Recht auf Auskunft, Berichtigung, Löschung, Einschränkung, Widerspruch und Datenübertragbarkeit.',
            ], 'list' => [
                'Nutzer: In Ihrem Konto exportiert „Meine Daten“ alle Ihre Daten in eine Datei, und die Löschung des Kontos entfernt Ihre personenbezogenen Daten. Profil, Hunde und Bewertungen können Sie jederzeit ändern.',
                'Profis: Um einen Eintrag zu korrigieren oder löschen zu lassen oder der Anzeige zu widersprechen, nutzen Sie das Meldeformular am Ende jedes Eintrags.',
                'Jede andere Anfrage: Kontaktformular oder :email. Wir antworten innerhalb eines Monats.',
            ]],
            ['title' => 'Beschwerde', 'paragraphs' => [
                'Wenn Sie der Ansicht sind, dass Ihre Rechte nicht gewahrt werden, können Sie bei der belgischen Datenschutzbehörde (APD/GBA), Rue de la Presse 35 / Drukpersstraat 35, 1000 Brüssel, datenschutzbehoerde.be bzw. autoriteprotectiondonnees.be, Beschwerde einreichen.',
            ]],
            ['title' => 'Sicherheit und Minderjährige', 'paragraphs' => [
                'Passwörter werden verschlüsselt gespeichert, der Datenverkehr ist per HTTPS geschützt und Nachweise liegen außerhalb des öffentlichen Bereichs des Servers. Konten sind Personen ab 16 Jahren vorbehalten.',
                'Wir können diese Erklärung aktualisieren; das Datum der letzten Aktualisierung steht oben auf der Seite.',
            ]],
        ],
    ],

    'terms' => [
        'meta_title' => 'Nutzungsbedingungen',
        'meta_description' => 'Regeln zur Nutzung von Doggys: kostenloser Dienst, Konten, Bewertungen, Meldung rechtswidriger Inhalte, Haftung und belgisches Recht.',
        'breadcrumb' => 'Nutzungsbedingungen',
        'title' => 'Nutzungsbedingungen',
        'intro' => 'Die einfachen Regeln, die Doggys kostenlos, verlässlich und freundlich halten.',
        'sections' => [
            ['title' => 'Gegenstand und Annahme', 'paragraphs' => [
                'Diese Bedingungen regeln die Nutzung von doggys.be, herausgegeben von Erwan Goossens (Novexa Digital, siehe Impressum). Mit der Nutzung der Website oder der Erstellung eines Kontos akzeptieren Sie sie.',
            ], 'links' => ['notice' => 'Impressum']],
            ['title' => 'Ein kostenloser Dienst', 'paragraphs' => [
                'Das Verzeichnis zu nutzen, ein Konto zu erstellen, eine Bewertung zu schreiben und den Eintrag eines Unternehmens zu verwalten ist kostenlos. Doggys verkauft keine Platzierungen und zeigt keine Werbung. Der Dienst wird „wie besehen“ angeboten und kann sich ändern oder unterbrochen werden.',
            ]],
            ['title' => 'Konten', 'paragraphs' => [
                'Sie müssen korrekte Angaben machen, mindestens 16 Jahre alt sein und Ihr Passwort schützen. Ein Konto ist persönlich. Sie können es jederzeit in Ihrem Bereich löschen. Wir können ein Konto bei Betrug, Missbrauch oder Verstoß gegen diese Bedingungen sperren oder löschen.',
                'Ein Profi, der einen Eintrag beansprucht, muss das Unternehmen tatsächlich vertreten und wahrheitsgemäße Angaben machen.',
            ]],
            ['title' => 'Bewertungen', 'paragraphs' => [
                'Sie dürfen nur eine echte, persönliche Erfahrung bewerten, ohne beleidigende, diskriminierende oder verleumderische Äußerungen und ohne personenbezogene Daten Dritter. Profis können ihren eigenen Eintrag nicht bewerten. Die vollständigen Regeln, die Moderation und das Antwortrecht stehen auf der folgenden Seite.',
            ], 'links' => ['reviews' => 'Wie Bewertungen geprüft werden']],
            ['title' => 'Rechtswidrige Inhalte melden', 'paragraphs' => [
                'Nach dem europäischen Gesetz über digitale Dienste (DSA) kann jeder uns Inhalte melden, die er für rechtswidrig hält, über die Schaltflächen „Melden“ oder das Kontaktformular. Geben Sie die Adresse der Seite, die Gründe für die Rechtswidrigkeit und Ihre Kontaktdaten an.',
                'Wir prüfen jede Meldung sorgfältig, entfernen oder verbergen offensichtlich rechtswidrige Inhalte und informieren den Verfasser über die Entscheidung und ihre Gründe. Der Verfasser kann ihr widersprechen, indem er uns schreibt.',
            ], 'links' => ['contact' => 'Kontakt']],
            ['title' => 'Haftung', 'paragraphs' => [
                'Einträge und Bewertungen dienen der Information. Wir bemühen uns um richtige Angaben, garantieren aber weder Vollständigkeit noch Aktualität und empfehlen keinen Profi. Prüfen Sie Qualifikationen und Preise immer beim Profi. Bei einem Notfall Ihres Tieres wenden Sie sich direkt an eine Tierarztpraxis.',
                'Doggys ist nicht Partei der Beziehungen zwischen Nutzern und Profis und dafür nicht verantwortlich. Bewertungen binden nur ihre Verfasser. Nichts in diesen Bedingungen beschränkt eine Haftung, die gesetzlich nicht beschränkt werden darf, insbesondere bei Vorsatz oder Arglist.',
            ]],
            ['title' => 'Geistiges Eigentum', 'paragraphs' => [
                'Die redaktionellen Inhalte und das Design der Website sind geschützt. Sie behalten Ihre Rechte an Ihren Bewertungen und räumen Doggys eine unentgeltliche, nicht ausschließliche Lizenz zur Anzeige ein, solange sie veröffentlicht sind. Für Kartendaten gelten die im Impressum genannten Lizenzen.',
            ]],
            ['title' => 'Personenbezogene Daten', 'paragraphs' => [
                'Die Verarbeitung Ihrer Daten ist in der Datenschutzerklärung beschrieben.',
            ], 'links' => ['privacy' => 'Datenschutz', 'cookies' => 'Cookies']],
            ['title' => 'Anwendbares Recht und Streitigkeiten', 'paragraphs' => [
                'Diese Bedingungen unterliegen belgischem Recht. Kontaktieren Sie uns vor jedem Schritt: Wir suchen stets eine gütliche Lösung. Verbraucher können außerdem die EU-Plattform zur Online-Streitbeilegung nutzen. Kommt keine Einigung zustande, sind ausschließlich die zuständigen belgischen Gerichte zuständig, unbeschadet zwingender Verbraucherschutzvorschriften.',
            ]],
        ],
    ],

    'cookies' => [
        'meta_title' => 'Cookies',
        'meta_description' => 'Doggys verwendet nur notwendige Cookies (Sitzung, Sicherheit): Ein Einwilligungsbanner ist nicht erforderlich.',
        'breadcrumb' => 'Cookies',
        'title' => 'Cookies',
        'intro' => 'Doggys verwendet nur die Cookies, die für den Betrieb der Website unbedingt erforderlich sind. Kein Tracking, daher kein Banner zum Wegklicken.',
        'sections' => [
            ['title' => 'Warum kein Banner?', 'paragraphs' => [
                'Das belgische Recht (Gesetz vom 13. Juni 2005 über die elektronische Kommunikation) und die ePrivacy-Richtlinie verlangen Ihre Einwilligung für Cookies, außer für solche, die für einen von Ihnen gewünschten Dienst unbedingt erforderlich sind. Doggys verwendet keine anderen: Eine Einwilligung ist daher nicht nötig, und ein Banner wäre sinnlos.',
            ]],
            ['title' => 'Verwendete Cookies', 'list' => [
                'Sitzungscookie: hält Sie angemeldet und überträgt Bestätigungsmeldungen. Dauer: höchstens 2 Stunden Inaktivität.',
                'XSRF-TOKEN: schützt Formulare vor gefälschten Anfragen (CSRF). Dauer: die der Sitzung.',
                'Cookie „Angemeldet bleiben“: wird nur gesetzt, wenn Sie dieses Kästchen bei der Anmeldung ankreuzen.',
            ]],
            ['title' => 'Was wir nicht verwenden', 'paragraphs' => [
                'Keine Werbe-Cookies, keine Reichweitenmessung, keine Pixel sozialer Netzwerke, keine von Dritten geladenen Videos oder Schriftarten. Karten zeigen OpenStreetMap-Kacheln, ohne Cookie von uns.',
                'Ist die Anti-Bot-Prüfung Cloudflare Turnstile auf Formularen aktiv, dient sie nur dazu, Roboter zu blockieren.',
            ]],
            ['title' => 'Verwalten', 'paragraphs' => [
                'Sie können Cookies in den Browsereinstellungen löschen oder blockieren; die Anmeldung funktioniert dann nicht mehr. Die Verarbeitung Ihrer Daten ist in der Datenschutzerklärung beschrieben.',
            ], 'links' => ['privacy' => 'Datenschutz']],
        ],
    ],
];
