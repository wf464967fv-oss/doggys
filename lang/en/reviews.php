<?php

return [
    'reply_from' => 'Reply from :name',

    'form' => [
        'title' => 'Share your experience',
        'rating' => 'Your rating',
        'rating_hint' => '1 star = very disappointed, 5 stars = delighted.',
        'stars' => '{1} :count star out of 5|[2,*] :count stars out of 5',
        'body' => 'Your experience',
        'body_hint' => 'Between 20 and 2,000 characters. Tell what happened, kindly and without personal data.',
        'published_as' => 'Your review goes live straight away, under the name “:name”.',
        'rules_link' => 'How reviews are checked',
        'submit' => 'Publish my review',
    ],

    'blocked' => [
        'guest' => 'Log in or create a free account to share your experience.',
        'unverified' => 'Please confirm your e-mail address before posting a review.',
        'unverified_link' => 'Resend the confirmation link',
        'own_listing' => 'You manage this listing: you cannot rate it, but you can reply to its reviews.',
        'own_listing_link' => 'Reply to reviews',
        'cooldown' => 'You already reviewed this professional in the last :days days. You can edit your review from “My reviews”.',
        'daily_limit' => 'You have posted :limit reviews in 24 hours, which is the maximum. Come back tomorrow!',
    ],

    'report' => [
        'button' => 'Report this review',
        'reason' => 'Why are you reporting this review?',
        'reason_hint' => 'Abusive language, fake review, personal data, conflict of interest… (at least 10 characters).',
        'submit' => 'Send the report',
        'done' => 'You reported this review. Thank you, we are looking into it.',
    ],

    'flash' => [
        'published' => 'Thank you! Your review is live.',
        'updated' => 'Your review has been updated.',
        'deleted' => 'Your review has been deleted.',
        'reported' => 'Thank you, your report has been passed on to our moderation team.',
        'already_reported' => 'You have already reported this review.',
    ],

    'mine' => [
        'tab' => 'My reviews',
        'title' => 'My reviews',
        'meta' => 'Find, edit or delete the reviews you have posted on Doggys.',
        'subtitle' => 'The reviews you have shared. You can edit or delete them at any time.',
        'empty_title' => 'No reviews yet',
        'empty_text' => 'Did your groomer, vet or trainer make a good impression? Help other families choose.',
        'empty_cta' => 'Find a professional',
        'provider_gone' => 'Listing removed',
        'hidden' => 'Hidden by moderation',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'delete_confirm' => 'Delete this review for good?',
    ],

    'edit' => [
        'title' => 'Edit my review',
        'subtitle' => 'Your review of :name.',
        'submit' => 'Save changes',
    ],

    'mail' => [
        'hidden_subject' => 'Your review has been hidden',
        'hidden_intro' => 'After review, Doggys moderation has hidden your review of “:name”.',
        'hidden_reason' => 'Reason: :reason',
        'hidden_default_reason' => 'the review does not follow our publication rules (abusive language, content unrelated to the actual experience, personal data or conflict of interest).',
        'hidden_redress' => 'You can challenge this decision by writing to us: we will look at your review again.',
        'hidden_action' => 'Challenge the decision',
    ],

    'policy' => [
        'meta_title' => 'How reviews are checked',
        'meta_description' => 'Who can post a review on Doggys, what we check, and how reviews are ranked, reported and moderated: our rules, in full transparency.',
        'breadcrumb' => 'Verified reviews',
        'eyebrow' => 'Transparency',
        'title' => 'How reviews are checked',
        'intro' => 'Reviews help families choose a professional with confidence. In line with the EU “Omnibus” Directive and the Digital Services Act (DSA), here is how we collect, check and moderate them.',
        'summary_title' => 'In short',
        'summary' => [
            'Only an account with a confirmed e-mail address can post a review.',
            'We do not check purchases or appointments: our reviews are not “verified purchase” reviews.',
            'Every review is published, positive or negative, from newest to oldest.',
            'Nobody can pay to hide, remove or promote a review.',
        ],
        'sections' => [
            [
                'title' => 'Who can post a review?',
                'paragraphs' => [
                    'Anyone with a free Doggys account whose e-mail address has been confirmed. A review is made of a rating from 1 to 5 stars and a text of 20 to 2,000 characters. It is published under the author’s first name and last initial (for example “Camille D.”).',
                ],
            ],
            [
                'title' => 'What we check, and what we do not',
                'paragraphs' => [
                    'We check that every review comes from an account with a confirmed e-mail address, and we apply limits against abuse: one review per professional and per account every 30 days, at most five reviews per account within 24 hours, and no review on a listing you manage yourself.',
                    'We do not check that the author actually used the professional’s services: we ask for no invoice or proof of appointment. Reviews reflect the experience reported by their authors.',
                    'We accept no paid reviews or reviews written in exchange for a benefit, and we do not ask for any.',
                ],
            ],
            [
                'title' => 'Publication, ranking and average rating',
                'paragraphs' => [
                    'Reviews are published immediately, with no prior filtering. They are shown from newest to oldest, whatever their rating.',
                    'The average rating of a listing is the simple average of all published reviews. It is recalculated whenever a review is added, edited, hidden or deleted.',
                    'A review is never removed because it is negative. No professional can pay to hide, remove or promote a review.',
                ],
            ],
            [
                'title' => 'Professionals’ right of reply',
                'paragraphs' => [
                    'A professional whose listing has been validated by our team can reply publicly to each review. The reply is shown under the review and can be edited.',
                ],
            ],
            [
                'title' => 'Reporting a review',
                'paragraphs' => [
                    'Under each review, the “Report this review” button lets anyone who is logged in flag content they believe is illegal or against our rules: abusive or discriminatory language, fake reviews, personal data, conflicts of interest or text unrelated to the actual experience. Without an account, use our contact form and include the link to the review.',
                ],
            ],
            [
                'title' => 'Moderation and statement of reasons',
                'paragraphs' => [
                    'Every report is examined by a person; no decision is automated. If a review breaks our rules, it is hidden and the average rating is recalculated. Its author receives an e-mail explaining the decision and the reason for it.',
                    'If the report is unfounded, the review stays online.',
                ],
            ],
            [
                'title' => 'Challenging a decision',
                'paragraphs' => [
                    'The author of a hidden review, as well as the person who reported it, can challenge the decision through our contact form, and we will review the case again. You can also turn to the Belgian FPS Economy or the competent courts.',
                ],
            ],
            [
                'title' => 'Your reviews belong to you',
                'paragraphs' => [
                    'You can edit or delete your reviews at any time from “My reviews” in your account. Deleting your account also deletes all your reviews.',
                ],
            ],
        ],
        'cta_title' => 'A question about a review?',
        'cta_text' => 'Write to us: we answer every message.',
        'cta' => 'Contact us',
    ],
];
