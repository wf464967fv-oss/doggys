<?php

return [
    'greeting' => 'Hello :name,',
    'salutation' => 'See you soon on Doggys!',

    'verify' => [
        'subject' => 'Confirm your email address',
        'intro' => 'Welcome to Doggys! Click the button below to confirm your email address and activate your account.',
        'action' => 'Confirm my address',
        'outro' => 'If you did not create an account, you can safely ignore this email.',
    ],

    'reset' => [
        'subject' => 'Reset your password',
        'intro' => 'You asked to reset the password of your Doggys account.',
        'action' => 'Choose a new password',
        'expire' => 'This link is valid for :count minutes.',
        'outro' => 'If you did not make this request, simply ignore this email.',
    ],
    'new_email' => [
        'subject' => 'Confirm your new email address',
        'intro' => 'You asked to use this address for your Doggys account. Click the button below to confirm it.',
        'action' => 'Confirm my new address',
        'outro' => 'This link is valid for 60 minutes. If you did not make this request, ignore this email.',
    ],
    'email_change' => [
        'subject' => 'Email address change request',
        'intro' => 'A request was made to replace your Doggys account address with :email. Your current address stays active until the new one is confirmed.',
        'outro' => 'Wasn’t you? Change your password right away.',
        'action' => 'Change my password',
    ],
];
