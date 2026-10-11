<?php

defined('TYPO3') || die();

// Global Fluid namespace: <vars:get key="phone" />, <vars:replace>…</vars:replace>
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['vars'][] = 'T3S\\T3sVars\\ViewHelpers';
