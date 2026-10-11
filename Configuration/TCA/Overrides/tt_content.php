<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die();

$lll = 'LLL:EXT:t3s_vars/Resources/Private/Language/locallang.xlf:';

// Layout is stored in the existing field pi_flexform – no own DB field
ExtensionManagementUtility::addRecordType(
    [
        'label' => $lll . 'ce.address.title',
        'description' => $lll . 'ce.address.description',
        'value' => 't3s_address',
        'icon' => 't3s-vars-address',
        'group' => 'default',
    ],
    '
        --palette--;;headers,
        pi_flexform;' . $lll . 'field.layout,
        --div--;core.form.tabs:appearance,
            --palette--;;frames,
            --palette--;;appearanceLinks,
        --div--;core.form.tabs:categories,
            categories,
    ',
    [
        'columnsOverrides' => [
            'pi_flexform' => [
                'config' => [
                    'ds' => 'FILE:EXT:t3s_vars/Configuration/FlexForms/Address.xml',
                ],
            ],
        ],
    ]
);
