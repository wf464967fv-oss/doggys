<?php

return [
    'greeting' => 'Bonjour :name,',
    'salutation' => 'À bientôt sur Doggys !',

    'verify' => [
        'subject' => 'Confirmez votre adresse e-mail',
        'intro' => 'Bienvenue sur Doggys ! Cliquez sur le bouton ci-dessous pour confirmer votre adresse e-mail et activer votre compte.',
        'action' => 'Confirmer mon adresse',
        'outro' => 'Si vous n’avez pas créé de compte, vous pouvez ignorer ce message.',
    ],

    'reset' => [
        'subject' => 'Réinitialisation de votre mot de passe',
        'intro' => 'Vous avez demandé à réinitialiser le mot de passe de votre compte Doggys.',
        'action' => 'Choisir un nouveau mot de passe',
        'expire' => 'Ce lien est valable pendant :count minutes.',
        'outro' => 'Si vous n’êtes pas à l’origine de cette demande, ignorez simplement ce message.',
    ],
    'new_email' => [
        'subject' => 'Confirmez votre nouvelle adresse e-mail',
        'intro' => 'Vous avez demandé à utiliser cette adresse pour votre compte Doggys. Cliquez sur le bouton ci-dessous pour la confirmer.',
        'action' => 'Confirmer ma nouvelle adresse',
        'outro' => 'Ce lien est valable 60 minutes. Si vous n’êtes pas à l’origine de cette demande, ignorez ce message.',
    ],
    'email_change' => [
        'subject' => 'Demande de changement d’adresse e-mail',
        'intro' => 'Une demande a été faite pour remplacer l’adresse de votre compte Doggys par :email. Votre adresse actuelle reste active tant que la nouvelle n’est pas confirmée.',
        'outro' => 'Ce n’est pas vous ? Changez immédiatement votre mot de passe.',
        'action' => 'Changer mon mot de passe',
    ],
];
