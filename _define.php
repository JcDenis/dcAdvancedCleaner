<?php
/**
 * @brief dcAdvancedCleaner, a plugin for Dotclear 2
 * 
 * @package Dotclear
 * @subpackage Plugin
 * 
 * @author Jean-Christian Denis and Contributors
 * 
 * @copyright Jean-Christian Denis
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('DC_RC_PATH')) {
    return null;
}

$this->registerModule(
    'Advanced cleaner',
    'Make a huge cleaning of dotclear',
    'Jean-Christian Denis and Contributors',
    '0.8',
    [
        'requires' => [['core', '2.19']],
        'permissions' => null,
        'type' => 'plugin',
        'support' => 'https://github.com/JcDenis/dcAdvancedCleaner',
        'details' => 'https://plugins.dotaddict.org/dc2/details/dcAdvancedCleaner',
        'repository' => 'https://raw.githubusercontent.com/JcDenis/dcAdvancedCleaner/master/dcstore.xml'
    ]
);