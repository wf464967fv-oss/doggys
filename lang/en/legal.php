<?php

/*
 * Legal pages (see resources/views/pages/legal.blade.php). Placeholders: :bce :address :email.
 */
return [
    'updated_on' => '2026-10-15',
    'updated' => 'Last updated: :date',
    'toc' => 'Contents',
    'placeholder' => '[to be completed]',
    'labels' => ['purpose' => 'Purpose', 'basis' => 'Legal basis', 'retention' => 'Retention period', 'recipients' => 'Recipients'],
    'cta_title' => 'A question about your data or the site?',
    'cta_text' => 'Write to us: we answer every request within one month at most.',
    'cta' => 'Contact us',

    'notice' => [
        'meta_title' => 'Legal notice',
        'meta_description' => 'Publisher, host and credits of Doggys, the free Belgian directory of dog professionals.',
        'breadcrumb' => 'Legal notice',
        'title' => 'Legal notice',
        'intro' => 'Who publishes Doggys, who hosts it and where the data shown comes from.',
        'sections' => [
            ['title' => 'Publisher', 'paragraphs' => [
                'The website doggys.be is published by Erwan Goossens, self-employed in a supplementary capacity, trading as Novexa Digital.',
            ], 'list' => [
                'Company number (BCE/KBO): :bce',
                'Address: :address',
                'E-mail: :email',
                'Publication director: Erwan Goossens',
            ]],
            ['title' => 'Host', 'paragraphs' => [
                'The site is hosted by o2switch SAS, 222-224 Boulevard Gustave Flaubert, 63000 Clermont-Ferrand, France (o2switch.fr). Data is stored in the European Union.',
            ]],
            ['title' => 'Nature of the service', 'paragraphs' => [
                'Doggys is a free, ad-free directory of dog professionals in Belgium. Doggys is neither a veterinarian nor a care provider, and is not a party to the relationship between visitors and professionals.',
                'Professional listings are built from open sources and from information supplied by the professionals themselves. A professional can ask for a correction or removal of their listing from the listing page itself.',
            ]],
            ['title' => 'Intellectual property', 'paragraphs' => [
                'The Doggys name, logo, layout and editorial texts (guides included) are protected by copyright. Reproduction without prior written consent is forbidden, apart from a short quotation crediting the source.',
                'Reviews belong to their authors, who grant Doggys the right to display them on the site.',
            ]],
            ['title' => 'Credits and data licences', 'paragraphs' => [
                'Maps and part of the location data come from OpenStreetMap, © OpenStreetMap contributors, available under the ODbL licence (openstreetmap.org/copyright).',
                'Postal codes, municipalities and coordinates come from GeoNames (geonames.org), under the Creative Commons Attribution 4.0 licence (CC BY 4.0).',
            ]],
            ['title' => 'Reporting content', 'paragraphs' => [
                'To report illegal content or an error, use the report buttons on the site or the contact form. The procedure is described in our terms of use.',
            ], 'links' => ['terms' => 'Terms of use', 'privacy' => 'Privacy', 'contact' => 'Contact']],
        ],
    ],

    'privacy' => [
        'meta_title' => 'Privacy policy',
        'meta_description' => 'What data Doggys collects, why, for how long, and how to exercise your GDPR rights. No trackers.',
        'breadcrumb' => 'Privacy',
        'title' => 'Privacy policy',
        'intro' => 'Doggys collects as little data as possible, hosts it in the European Union and uses no advertising or analytics trackers.',
        'sections' => [
            ['title' => 'Data controller', 'paragraphs' => [
                'The data controller is Erwan Goossens (trading as Novexa Digital), self-employed in a supplementary capacity. Company number: :bce. Address: :address. Contact: :email or through the contact form.',
                'This policy applies the General Data Protection Regulation (GDPR, EU Regulation 2016/679) and the Belgian Act of 30 July 2018.',
            ]],
            ['title' => 'Data processing', 'paragraphs' => [
                'For each processing activity: why we use your data, on what basis, how long we keep it and who can access it.',
            ], 'treatments' => [
                ['title' => 'User account', 'purpose' => 'create and manage your account (name, e-mail, hashed password, role), let you sign in and write to you about your account.', 'basis' => 'performance of the service you request (Art. 6(1)(b) GDPR).', 'retention' => 'until you delete your account, which you can do at any time.', 'recipients' => 'the publisher; the host o2switch (processor).'],
                ['title' => 'Dog profiles', 'purpose' => 'let you keep a private record of your dogs (name, breed, birth date, vaccinations, health notes, chip). This information is never public.', 'basis' => 'performance of the service (Art. 6(1)(b)).', 'retention' => 'until you delete it or delete your account.', 'recipients' => 'you only; the host (processor).'],
                ['title' => 'Reviews', 'purpose' => 'publish your review of a professional, moderate abuse and compute ratings. Your review is shown under your account name.', 'basis' => 'performance of the service (Art. 6(1)(b)) and legitimate interest in reliable reviews (Art. 6(1)(f)).', 'retention' => 'until the review or the account is deleted.', 'recipients' => 'the public (published review); the professional concerned, who may reply; the publisher.'],
                ['title' => 'Listing claims and proofs', 'purpose' => 'check that a person really represents the business (BCE number, position, supporting document sent to private storage).', 'basis' => 'legitimate interest in preventing impersonation (Art. 6(1)(f)).', 'retention' => 'rejected documents: deleted after 90 days; accepted documents: while the listing is managed, then deleted with the listing or the account, or on request.', 'recipients' => 'the publisher only (never public); the host (processor).'],
                ['title' => 'Reports', 'purpose' => 'handle reports of errors, listing removal requests and abusive reviews or content.', 'basis' => 'legitimate interest and legal obligation to handle reports of illegal content (Art. 6(1)(f) and 6(1)(c), DSA).', 'retention' => 'up to 12 months after the report is handled.', 'recipients' => 'the publisher; the host (processor).'],
                ['title' => 'Contact messages', 'purpose' => 'answer your message (name, e-mail, content).', 'basis' => 'pre-contractual steps and legitimate interest in replying (Art. 6(1)(b) and 6(1)(f)).', 'retention' => 'up to 12 months after the message is handled.', 'recipients' => 'the publisher; the host and its mail service (processors).'],
                ['title' => 'Technical logs and security', 'purpose' => 'keep the site secure, detect abuse and diagnose failures (IP address, date, requested page, errors). An anti-bot check may be used on forms.', 'basis' => 'legitimate interest in security (Art. 6(1)(f)).', 'retention' => '14 days for logs; the sign-in session expires after 2 hours of inactivity.', 'recipients' => 'the publisher; the host (processor); if the Cloudflare Turnstile check is enabled, Cloudflare receives your IP address when you submit a form.'],
                ['title' => 'Professional listings from open sources', 'purpose' => 'inform the public: business name, activity, address and public business contact details, taken from OpenStreetMap, Belgian public registers and information published by the professionals. They mostly concern legal entities; for a sole trader these are business contact details.', 'basis' => 'legitimate interest in running a directory and informing the public (Art. 6(1)(f)). You may object.', 'retention' => 'as long as the business operates and until a removal request.', 'recipients' => 'the public; the host (processor).'],
            ]],
            ['title' => 'Hosting and transfers outside the EU', 'paragraphs' => [
                'The site and database are hosted by o2switch in France. No account data is transferred outside the European Union.',
                'Maps display OpenStreetMap tiles: when a map is visible, your browser contacts the OpenStreetMap Foundation servers, which see your IP address. Fonts are served from our own server.',
            ]],
            ['title' => 'No trackers', 'paragraphs' => [
                'Doggys uses no advertising, no audience measurement, no social network pixel and no tracking cookie. We neither sell nor rent any data.',
            ], 'links' => ['cookies' => 'Cookies']],
            ['title' => 'Your rights', 'paragraphs' => [
                'You have the right of access, rectification, erasure, restriction, objection and portability.',
            ], 'list' => [
                'Users: from your account, "My data" exports all your data to a file, and deleting your account erases your personal data. You can also edit your profile, dogs and reviews at any time.',
                'Professionals: to correct or remove a listing, or to object to it being displayed, use the report form at the bottom of each listing.',
                'Any other request: contact form or :email. We answer within one month.',
            ]],
            ['title' => 'Complaints', 'paragraphs' => [
                'If you believe your rights are not respected, you can lodge a complaint with the Belgian Data Protection Authority (APD/GBA), Rue de la Presse 35, 1000 Brussels, dataprotectionauthority.be.',
            ]],
            ['title' => 'Security and minors', 'paragraphs' => [
                'Passwords are hashed, traffic is protected by HTTPS and supporting documents are kept outside the public area of the server. Accounts are reserved for people aged 16 or over.',
                'We may update this policy; the date of the last update is shown at the top of the page.',
            ]],
        ],
    ],

    'terms' => [
        'meta_title' => 'Terms of use',
        'meta_description' => 'Rules for using Doggys: free service, accounts, reviews, reporting illegal content, liability and Belgian law.',
        'breadcrumb' => 'Terms of use',
        'title' => 'Terms of use',
        'intro' => 'The simple rules that keep Doggys free, trustworthy and kind.',
        'sections' => [
            ['title' => 'Purpose and acceptance', 'paragraphs' => [
                'These terms govern the use of doggys.be, published by Erwan Goossens (trading as Novexa Digital, see the legal notice). By using the site or creating an account you accept them.',
            ], 'links' => ['notice' => 'Legal notice']],
            ['title' => 'A free service', 'paragraphs' => [
                'Browsing the directory, creating an account, posting a review and managing a business listing are free. Doggys does not sell rankings and shows no advertising. The service is provided "as is" and may change or be interrupted.',
            ]],
            ['title' => 'Accounts', 'paragraphs' => [
                'You must provide accurate information, be at least 16 and protect your password. An account is personal. You can delete it at any time from your account area. We may suspend or delete an account in case of fraud, abuse or breach of these terms.',
                'A professional claiming a listing must genuinely represent the business and provide truthful information.',
            ]],
            ['title' => 'Reviews', 'paragraphs' => [
                'You may only review a real, personal experience, without insulting, discriminatory or defamatory language and without third-party personal data. Professionals cannot rate their own listing. The full rules, moderation and right of reply are detailed on the page below.',
            ], 'links' => ['reviews' => 'How reviews are verified']],
            ['title' => 'Reporting illegal content', 'paragraphs' => [
                'In line with the EU Digital Services Act (DSA), anyone can notify us of content they consider illegal, using the "Report" buttons or the contact form. Give the page address, the reasons why the content is illegal and your contact details.',
                'We examine each notice diligently, remove or hide manifestly illegal content and inform the author of the decision and its reasons. The author can contest it by writing to us.',
            ], 'links' => ['contact' => 'Contact']],
            ['title' => 'Liability', 'paragraphs' => [
                'Listings and reviews are provided for information. We strive to keep information accurate but do not guarantee it is complete or up to date, and we do not recommend any professional. Always check qualifications and prices with the professional. In an emergency for your animal, contact a veterinarian directly.',
                'Doggys is not a party to, nor responsible for, relationships between users and professionals. Reviews bind only their authors. Nothing in these terms limits liability that cannot lawfully be limited, in particular in case of intentional fault or fraud.',
            ]],
            ['title' => 'Intellectual property', 'paragraphs' => [
                'The editorial content and design of the site are protected. You keep your rights over your reviews and grant Doggys a free, non-exclusive licence to display them while they are published. Map data is subject to the licences listed in the legal notice.',
            ]],
            ['title' => 'Personal data', 'paragraphs' => [
                'How your data is processed is described in the privacy policy.',
            ], 'links' => ['privacy' => 'Privacy', 'cookies' => 'Cookies']],
            ['title' => 'Governing law and disputes', 'paragraphs' => [
                'These terms are governed by Belgian law. Before any action, contact us: we always look for an amicable solution. Consumers may also use the EU online dispute resolution platform. Failing agreement, the competent Belgian courts have sole jurisdiction, without prejudice to mandatory consumer protection rules.',
            ]],
        ],
    ],

    'cookies' => [
        'meta_title' => 'Cookies',
        'meta_description' => 'Doggys only uses essential cookies (session, security): no consent banner is needed.',
        'breadcrumb' => 'Cookies',
        'title' => 'Cookies',
        'intro' => 'Doggys only uses the cookies strictly necessary for the site to work. No tracking, so no banner to click.',
        'sections' => [
            ['title' => 'Why no banner?', 'paragraphs' => [
                'Belgian law (Act of 13 June 2005 on electronic communications) and the ePrivacy Directive require your consent for cookies, except those strictly necessary for a service you request. Doggys uses no others: no consent is required, and a banner would be pointless.',
            ]],
            ['title' => 'Cookies we use', 'list' => [
                'Session cookie: keeps you signed in and carries confirmation messages. Lifetime: 2 hours of inactivity at most.',
                'XSRF-TOKEN: protects forms against forged requests (CSRF). Lifetime: that of the session.',
                '"Remember me" cookie: set only if you tick that box when signing in.',
            ]],
            ['title' => 'What we do not use', 'paragraphs' => [
                'No advertising cookie, no audience measurement, no social network pixel, no video or font loaded from a third party. Maps display OpenStreetMap tiles, without any cookie from us.',
                'If the Cloudflare Turnstile anti-bot check is enabled on forms, it is used only to block robots.',
            ]],
            ['title' => 'Managing them', 'paragraphs' => [
                'You can delete or block cookies in your browser settings; signing in will then stop working. How your data is processed is described in the privacy policy.',
            ], 'links' => ['privacy' => 'Privacy']],
        ],
    ],
];
