<?php

/*
 * Legal identity of the publisher, shown on the legal notice. Never invented: empty values are displayed
 * as a "to be completed" marker until the .env keys are filled in.
 */
return [
    'legal' => [
        'bce' => env('DOGGYS_LEGAL_BCE'),
        'address' => env('DOGGYS_LEGAL_ADDRESS'),
        'email' => env('DOGGYS_LEGAL_EMAIL'),
    ],
];
