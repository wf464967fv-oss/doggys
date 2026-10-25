<?php

/*
 * Legal pages (see resources/views/pages/legal.blade.php). Placeholders: :bce :address :email.
 */
return [
    'updated_on' => '2026-10-15',
    'updated' => 'Laatst bijgewerkt: :date',
    'toc' => 'Inhoud',
    'placeholder' => '[aan te vullen]',
    'labels' => ['purpose' => 'Doel', 'basis' => 'Rechtsgrond', 'retention' => 'Bewaartermijn', 'recipients' => 'Ontvangers'],
    'cta_title' => 'Een vraag over je gegevens of de site?',
    'cta_text' => 'Schrijf ons: we beantwoorden elke aanvraag binnen maximaal één maand.',
    'cta' => 'Contacteer ons',

    'notice' => [
        'meta_title' => 'Wettelijke vermeldingen',
        'meta_description' => 'Uitgever, hostingpartij en bronvermeldingen van Doggys, de gratis Belgische gids voor hondenprofessionals.',
        'breadcrumb' => 'Wettelijke vermeldingen',
        'title' => 'Wettelijke vermeldingen',
        'intro' => 'Wie Doggys uitgeeft, wie het host en waar de getoonde gegevens vandaan komen.',
        'sections' => [
            ['title' => 'Uitgever', 'paragraphs' => [
                'De website doggys.be wordt uitgegeven door Erwan Goossens, zelfstandige in bijberoep, onder de activiteitsnaam Novexa Digital.',
            ], 'list' => [
                'Ondernemingsnummer (KBO/BCE): :bce',
                'Btw-nummer: BE :bce',
                'Adres: :address',
                'E-mail: :email',
                'Verantwoordelijke uitgever: Erwan Goossens',
            ]],
            ['title' => 'Hosting', 'paragraphs' => [
                'De site wordt gehost door o2switch SAS, 222-224 Boulevard Gustave Flaubert, 63000 Clermont-Ferrand, Frankrijk (o2switch.fr). De gegevens worden in de Europese Unie opgeslagen.',
            ]],
            ['title' => 'Aard van de dienst', 'paragraphs' => [
                'Doggys is een gratis, reclamevrije gids van hondenprofessionals in België. Doggys is geen dierenarts of zorgverlener en is geen partij in de relatie tussen bezoekers en professionals.',
                'De fiches van professionals zijn opgebouwd uit open bronnen en uit informatie van de professionals zelf. Een professional kan vanaf de fiche zelf om verbetering of verwijdering vragen.',
            ]],
            ['title' => 'Intellectuele eigendom', 'paragraphs' => [
                'De naam Doggys, het logo, de opmaak en de redactionele teksten (gidsen inbegrepen) zijn auteursrechtelijk beschermd. Reproductie zonder voorafgaande schriftelijke toestemming is verboden, behalve een kort citaat met bronvermelding.',
                'Beoordelingen blijven eigendom van hun auteurs, die Doggys het recht geven ze op de site te tonen.',
            ]],
            ['title' => 'Bronvermelding en licenties', 'paragraphs' => [
                'De kaarten en een deel van de locatiegegevens komen van OpenStreetMap, © OpenStreetMap-bijdragers, beschikbaar onder de ODbL-licentie (openstreetmap.org/copyright).',
                'Postcodes, gemeenten en coördinaten komen van GeoNames (geonames.org), onder de licentie Creative Commons Naamsvermelding 4.0 (CC BY 4.0).',
            ]],
            ['title' => 'Inhoud melden', 'paragraphs' => [
                'Om illegale inhoud of een fout te melden, gebruik je de meldknoppen op de site of het contactformulier. De procedure staat in onze gebruiksvoorwaarden.',
            ], 'links' => ['terms' => 'Gebruiksvoorwaarden', 'privacy' => 'Privacy', 'contact' => 'Contact']],
        ],
    ],

    'privacy' => [
        'meta_title' => 'Privacybeleid',
        'meta_description' => 'Welke gegevens Doggys verzamelt, waarom, hoe lang en hoe je je AVG-rechten uitoefent. Geen trackers.',
        'breadcrumb' => 'Privacy',
        'title' => 'Privacybeleid',
        'intro' => 'Doggys verzamelt zo weinig mogelijk gegevens, host ze in de Europese Unie en gebruikt geen reclame- of statistiektrackers.',
        'sections' => [
            ['title' => 'Verwerkingsverantwoordelijke', 'paragraphs' => [
                'De verwerkingsverantwoordelijke is Erwan Goossens (activiteitsnaam Novexa Digital), zelfstandige in bijberoep. Ondernemingsnummer: :bce. Adres: :address. Contact: :email of via het contactformulier.',
                'Dit beleid past de Algemene Verordening Gegevensbescherming (AVG, Verordening (EU) 2016/679) en de Belgische wet van 30 juli 2018 toe.',
            ]],
            ['title' => 'De gegevensverwerkingen', 'paragraphs' => [
                'Per verwerking: waarom we je gegevens gebruiken, op welke grond, hoe lang we ze bewaren en wie er toegang toe heeft.',
            ], 'treatments' => [
                ['title' => 'Gebruikersaccount', 'purpose' => 'je account aanmaken en beheren (naam, e-mail, versleuteld wachtwoord, rol), je laten inloggen en je over je account mailen.', 'basis' => 'uitvoering van de dienst die je vraagt (art. 6.1.b AVG).', 'retention' => 'tot je account wordt verwijderd, wat je op elk moment kunt doen.', 'recipients' => 'de uitgever; de hostingpartij o2switch (verwerker).'],
                ['title' => 'Hondenprofielen', 'purpose' => 'je een privéschrift van je honden laten bijhouden (naam, ras, geboortedatum, vaccinaties, gezondheidsnotities, chip). Deze informatie is nooit openbaar.', 'basis' => 'uitvoering van de dienst (art. 6.1.b).', 'retention' => 'tot jij ze of je account verwijdert.', 'recipients' => 'alleen jij; de hostingpartij (verwerker).'],
                ['title' => 'Beoordelingen', 'purpose' => 'je beoordeling van een professional publiceren, misbruik modereren en scores berekenen. Je beoordeling verschijnt onder de naam van je account.', 'basis' => 'uitvoering van de dienst (art. 6.1.b) en gerechtvaardigd belang bij betrouwbare beoordelingen (art. 6.1.f).', 'retention' => 'tot de beoordeling of het account wordt verwijderd.', 'recipients' => 'het publiek (gepubliceerde beoordeling); de betrokken professional, die kan antwoorden; de uitgever.'],
                ['title' => 'Fiche-aanvragen en bewijzen', 'purpose' => 'nagaan of iemand het bedrijf werkelijk vertegenwoordigt (KBO-nummer, functie, bewijsstuk in privéopslag).', 'basis' => 'gerechtvaardigd belang om identiteitsmisbruik te voorkomen (art. 6.1.f).', 'retention' => 'geweigerde bewijsstukken: verwijderd na 90 dagen; aanvaarde bewijsstukken: zolang de fiche beheerd wordt, daarna verwijderd met de fiche of het account, of op verzoek.', 'recipients' => 'alleen de uitgever (nooit openbaar); de hostingpartij (verwerker).'],
                ['title' => 'Meldingen', 'purpose' => 'meldingen van fouten, verzoeken tot verwijdering van een fiche en misbruikte beoordelingen of inhoud behandelen.', 'basis' => 'gerechtvaardigd belang en wettelijke verplichting om meldingen van illegale inhoud te behandelen (art. 6.1.f en 6.1.c, DSA).', 'retention' => 'tot 12 maanden na behandeling van de melding.', 'recipients' => 'de uitgever; de hostingpartij (verwerker).'],
                ['title' => 'Contactberichten', 'purpose' => 'je bericht beantwoorden (naam, e-mail, inhoud).', 'basis' => 'precontractuele stappen en gerechtvaardigd belang om te antwoorden (art. 6.1.b en 6.1.f).', 'retention' => 'tot 12 maanden na behandeling van het bericht.', 'recipients' => 'de uitgever; de hostingpartij en haar mailservice (verwerkers).'],
                ['title' => 'Technische logs en beveiliging', 'purpose' => 'de site beveiligen, misbruik opsporen en storingen diagnosticeren (IP-adres, datum, opgevraagde pagina, fouten). Op formulieren kan een anti-botcontrole worden gebruikt.', 'basis' => 'gerechtvaardigd belang bij beveiliging (art. 6.1.f).', 'retention' => '14 dagen voor logs; de inlogsessie verloopt na 2 uur inactiviteit.', 'recipients' => 'de uitgever; de hostingpartij (verwerker); als de Cloudflare Turnstile-controle actief is, ontvangt Cloudflare je IP-adres bij het versturen van een formulier.'],
                ['title' => 'Fiches van professionals uit open bronnen', 'purpose' => 'het publiek informeren: bedrijfsnaam, activiteit, adres en openbare zakelijke contactgegevens uit OpenStreetMap, Belgische openbare registers en door professionals gepubliceerde informatie. Ze betreffen vooral rechtspersonen; voor een zelfstandige gaat het om zakelijke contactgegevens.', 'basis' => 'gerechtvaardigd belang bij een gids en bij informatie voor het publiek (art. 6.1.f). Je kunt bezwaar maken.', 'retention' => 'zolang het bedrijf actief is en tot een verzoek tot verwijdering.', 'recipients' => 'het publiek; de hostingpartij (verwerker).'],
            ]],
            ['title' => 'Hosting en doorgifte buiten de EU', 'paragraphs' => [
                'De site en de database worden door o2switch in Frankrijk gehost. Er worden geen accountgegevens buiten de Europese Unie doorgegeven.',
                'Kaarten tonen OpenStreetMap-tegels: wanneer een kaart zichtbaar is, contacteert je browser de servers van de OpenStreetMap Foundation, die je IP-adres ziet. De lettertypes staan op onze eigen server.',
            ]],
            ['title' => 'Geen trackers', 'paragraphs' => [
                'Doggys gebruikt geen reclame, geen bezoekersmeting, geen pixels van sociale netwerken en geen trackingcookies. We verkopen of verhuren geen gegevens.',
            ], 'links' => ['cookies' => 'Cookies']],
            ['title' => 'Je rechten', 'paragraphs' => [
                'Je hebt recht op inzage, correctie, wissing, beperking, bezwaar en overdraagbaarheid van je gegevens.',
            ], 'list' => [
                'Gebruikers: vanuit je account exporteert "Mijn gegevens" al je gegevens in een bestand, en het verwijderen van je account wist je persoonsgegevens. Je kunt ook op elk moment je profiel, honden en beoordelingen aanpassen.',
                'Professionals: om een fiche te corrigeren of te laten verwijderen, of bezwaar te maken tegen de weergave ervan, gebruik je het meldformulier onderaan elke fiche.',
                'Elke andere aanvraag: contactformulier of :email. We antwoorden binnen één maand.',
            ]],
            ['title' => 'Klacht', 'paragraphs' => [
                'Als je vindt dat je rechten niet worden gerespecteerd, kun je een klacht indienen bij de Gegevensbeschermingsautoriteit (GBA/APD), Drukpersstraat 35, 1000 Brussel, gegevensbeschermingsautoriteit.be.',
            ]],
            ['title' => 'Beveiliging en minderjarigen', 'paragraphs' => [
                'Wachtwoorden zijn versleuteld, het verkeer is beveiligd met HTTPS en bewijsstukken staan buiten het openbare deel van de server. Accounts zijn voorbehouden aan personen vanaf 16 jaar.',
                'We kunnen dit beleid bijwerken; de datum van de laatste update staat bovenaan de pagina.',
            ]],
        ],
    ],

    'terms' => [
        'meta_title' => 'Gebruiksvoorwaarden',
        'meta_description' => 'Regels voor het gebruik van Doggys: gratis dienst, accounts, beoordelingen, melden van illegale inhoud, aansprakelijkheid en Belgisch recht.',
        'breadcrumb' => 'Gebruiksvoorwaarden',
        'title' => 'Gebruiksvoorwaarden',
        'intro' => 'De eenvoudige regels waarmee Doggys gratis, betrouwbaar en vriendelijk blijft.',
        'sections' => [
            ['title' => 'Voorwerp en aanvaarding', 'paragraphs' => [
                'Deze voorwaarden regelen het gebruik van doggys.be, uitgegeven door Erwan Goossens (Novexa Digital, zie de wettelijke vermeldingen). Door de site te gebruiken of een account aan te maken aanvaard je ze.',
            ], 'links' => ['notice' => 'Wettelijke vermeldingen']],
            ['title' => 'Een gratis dienst', 'paragraphs' => [
                'De gids raadplegen, een account aanmaken, een beoordeling plaatsen en de fiche van een bedrijf beheren is gratis. Doggys verkoopt geen ranking en toont geen reclame. De dienst wordt geleverd "zoals hij is" en kan veranderen of onderbroken worden.',
            ]],
            ['title' => 'Accounts', 'paragraphs' => [
                'Je moet juiste informatie geven, minstens 16 jaar zijn en je wachtwoord beschermen. Een account is persoonlijk. Je kunt het op elk moment verwijderen vanuit je accountomgeving. We kunnen een account schorsen of verwijderen bij fraude, misbruik of schending van deze voorwaarden.',
                'Een professional die een fiche opeist, moet het bedrijf werkelijk vertegenwoordigen en waarheidsgetrouwe informatie geven.',
            ]],
            ['title' => 'Beoordelingen', 'paragraphs' => [
                'Je mag alleen een echte, persoonlijke ervaring beoordelen, zonder beledigende, discriminerende of lasterlijke taal en zonder persoonsgegevens van derden. Professionals kunnen hun eigen fiche niet beoordelen. De volledige regels, moderatie en recht van antwoord staan op onderstaande pagina.',
            ], 'links' => ['reviews' => 'Hoe beoordelingen worden gecontroleerd']],
            ['title' => 'Illegale inhoud melden', 'paragraphs' => [
                'In overeenstemming met de Europese Digitaledienstenverordening (DSA) kan iedereen ons inhoud melden die hij illegaal acht, via de knoppen "Melden" of het contactformulier. Vermeld het adres van de pagina, waarom de inhoud illegaal is en je contactgegevens.',
                'We onderzoeken elke melding zorgvuldig, verwijderen of verbergen kennelijk illegale inhoud en informeren de auteur over de beslissing en de redenen. De auteur kan ze betwisten door ons te schrijven.',
            ], 'links' => ['contact' => 'Contact']],
            ['title' => 'Aansprakelijkheid', 'paragraphs' => [
                'Fiches en beoordelingen worden ter informatie verstrekt. We streven naar juiste informatie, maar garanderen niet dat ze volledig of actueel is, en we bevelen geen enkele professional aan. Controleer altijd kwalificaties en prijzen bij de professional. Bij een noodgeval voor je dier contacteer je rechtstreeks een dierenarts.',
                'Doggys is geen partij in en niet verantwoordelijk voor de relaties tussen gebruikers en professionals. Beoordelingen binden enkel hun auteurs. Niets in deze voorwaarden beperkt een aansprakelijkheid die wettelijk niet beperkt mag worden, met name bij opzet of bedrog.',
            ]],
            ['title' => 'Intellectuele eigendom', 'paragraphs' => [
                'De redactionele inhoud en het ontwerp van de site zijn beschermd. Je behoudt je rechten op je beoordelingen en geeft Doggys een gratis, niet-exclusieve licentie om ze te tonen zolang ze gepubliceerd zijn. Kaartgegevens vallen onder de licenties in de wettelijke vermeldingen.',
            ]],
            ['title' => 'Persoonsgegevens', 'paragraphs' => [
                'De verwerking van je gegevens staat beschreven in het privacybeleid.',
            ], 'links' => ['privacy' => 'Privacy', 'cookies' => 'Cookies']],
            ['title' => 'Toepasselijk recht en geschillen', 'paragraphs' => [
                'Op deze voorwaarden is Belgisch recht van toepassing. Contacteer ons vóór elke stap: we zoeken altijd een minnelijke oplossing. Consumenten kunnen ook het Europese onlineplatform voor geschillenbeslechting gebruiken. Bij gebrek aan akkoord zijn alleen de bevoegde Belgische rechtbanken bevoegd, onverminderd de dwingende regels ter bescherming van consumenten.',
            ]],
        ],
    ],

    'cookies' => [
        'meta_title' => 'Cookies',
        'meta_description' => 'Doggys gebruikt alleen essentiële cookies (sessie, beveiliging): een toestemmingsbanner is niet nodig.',
        'breadcrumb' => 'Cookies',
        'title' => 'Cookies',
        'intro' => 'Doggys gebruikt alleen de cookies die strikt nodig zijn om de site te laten werken. Geen tracking, dus geen banner om weg te klikken.',
        'sections' => [
            ['title' => 'Waarom geen banner?', 'paragraphs' => [
                'De Belgische wet (wet van 13 juni 2005 betreffende de elektronische communicatie) en de ePrivacyrichtlijn vereisen je toestemming voor cookies, behalve voor cookies die strikt nodig zijn voor een dienst die je vraagt. Doggys gebruikt geen andere: toestemming is dus niet nodig en een banner zou zinloos zijn.',
            ]],
            ['title' => 'Gebruikte cookies', 'list' => [
                'Sessiecookie: houdt je ingelogd en bevat bevestigingsberichten. Duur: maximaal 2 uur inactiviteit.',
                'XSRF-TOKEN: beschermt formulieren tegen vervalste verzoeken (CSRF). Duur: die van de sessie.',
                'Cookie "onthoud mij": alleen geplaatst als je dat vakje bij het inloggen aanvinkt.',
            ]],
            ['title' => 'Wat we niet gebruiken', 'paragraphs' => [
                'Geen reclamecookies, geen bezoekersmeting, geen pixels van sociale netwerken, geen video of lettertype van derden. Kaarten tonen OpenStreetMap-tegels, zonder cookie van onze kant.',
                'Als de anti-botcontrole Cloudflare Turnstile op formulieren actief is, dient ze enkel om robots te blokkeren.',
            ]],
            ['title' => 'Beheren', 'paragraphs' => [
                'Je kunt cookies verwijderen of blokkeren in de instellingen van je browser; inloggen werkt dan niet meer. De verwerking van je gegevens staat in het privacybeleid.',
            ], 'links' => ['privacy' => 'Privacy']],
        ],
    ],
];
