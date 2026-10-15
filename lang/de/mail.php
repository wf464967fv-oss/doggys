<?php

return [
    'greeting' => 'Hallo :name,',
    'salutation' => 'Bis bald bei Doggys!',

    'verify' => [
        'subject' => 'Bestätigen Sie Ihre E-Mail-Adresse',
        'intro' => 'Willkommen bei Doggys! Klicken Sie auf die Schaltfläche unten, um Ihre E-Mail-Adresse zu bestätigen und Ihr Konto zu aktivieren.',
        'action' => 'Adresse bestätigen',
        'outro' => 'Falls Sie kein Konto erstellt haben, können Sie diese Nachricht ignorieren.',
    ],

    'reset' => [
        'subject' => 'Passwort zurücksetzen',
        'intro' => 'Sie haben das Zurücksetzen des Passworts für Ihr Doggys-Konto angefordert.',
        'action' => 'Neues Passwort wählen',
        'expire' => 'Dieser Link ist :count Minuten gültig.',
        'outro' => 'Falls Sie diese Anfrage nicht gestellt haben, ignorieren Sie diese Nachricht einfach.',
    ],
    'new_email' => [
        'subject' => 'Bestätigen Sie Ihre neue E-Mail-Adresse',
        'intro' => 'Sie möchten diese Adresse für Ihr Doggys-Konto verwenden. Klicken Sie auf die Schaltfläche unten, um sie zu bestätigen.',
        'action' => 'Neue Adresse bestätigen',
        'outro' => 'Dieser Link ist 60 Minuten gültig. Falls Sie diese Anfrage nicht gestellt haben, ignorieren Sie diese Nachricht.',
    ],
    'email_change' => [
        'subject' => 'Anfrage zur Änderung Ihrer E-Mail-Adresse',
        'intro' => 'Es wurde beantragt, die Adresse Ihres Doggys-Kontos durch :email zu ersetzen. Ihre aktuelle Adresse bleibt aktiv, bis die neue bestätigt ist.',
        'outro' => 'Das waren nicht Sie? Ändern Sie sofort Ihr Passwort.',
        'action' => 'Passwort ändern',
    ],
];
