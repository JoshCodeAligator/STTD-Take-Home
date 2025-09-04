<?php
return [
    // category precedence for tie-breaks
    'priority' => ['Outage','Security','Bug','Access','Billing','Feature Request','Orders','Onboarding','Performance','Other'],

    // keywords: arrays of exact fragments or regexes (prefixed with /.../)
    'rules' => [
        'Billing' => [
            'invoice','refund','charge','charged','payment','billing','credit','debit','chargeback','overcharged','receipt','subscription','plan','proration','invoice id'
        ],
        'Bug' => [
            'bug','error','crash','broken','fail','stacktrace','exception','npe','500','404','null reference','segfault'
        ],
        'Access' => [
            'login','log in','sign in','signin','auth','authenticate','password','reset','forgot password','locked','unlock','2fa','mfa','otp','account access','permission','role'
        ],
        'Outage' => [
            'down','downtime','unavailable','outage','latency','timeout','504','degraded','incident','sla breach','service unavailable'
        ],
        'Feature Request' => [
            'feature','request','enhancement','improve','would be great','roadmap','support for','can you add'
        ],
        'Orders' => [
            'order','shipment','shipping','tracking','deliver','return','rma'
        ],
        'Onboarding' => [
            'setup','install','installation','configure','configuration','getting started','onboarding'
        ],
        'Performance' => [
            'slow','sluggish','lag','perf','performance','high cpu','memory leak'
        ],
        'Security' => [
            'breach','hacked','phishing','compromise','xss','csrf','sql injection','security','vulnerability','pwn'
        ],
        'Other' => []
    ],

    // simple negation tokens to reduce false positives
    'negations' => ["not ", "no ", "never ", "can't ", "cannot ", "isn't ", "without "],

    // scoring
    'subject_weight' => 2.0,
    'min_score_for_category' => 2,   // otherwise return Uncategorized
];