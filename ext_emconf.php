<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'T3S Vars - Global variables',
    'description' => 'Global variables for TYPO3 v14: maintain company, address, phone, email etc. once as site settings and use them as {{placeholders}} in RTE, Fluid and TypoScript. CKEditor 5 dropdown, automatic tel/mailto/URL links and an "Address" content element with schema.org markup and optional Google Maps (route button, map with two-click privacy).',
    'category' => 'fe',
    'state' => 'stable',
    'author' => 'Helmut Hackbarth',
    'author_email' => '',
    'author_company' => 'T3Solution',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'php' => '8.2.0-8.5.99',
            'typo3' => '14.3.0-14.99.99',
            'frontend' => '14.3.0-14.99.99',
            'rte_ckeditor' => '14.3.0-14.99.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'iconpack' => '',
            'iconpack_fontawesome' => '',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'T3S\\T3sVars\\' => 'Classes',
        ],
    ],
];
