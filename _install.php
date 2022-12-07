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
if (!defined('DC_CONTEXT_ADMIN')) {
    return null;
}

try {
    if (version_compare(
        dcCore::app()->getVersion('dcAdvancedCleaner'), 
        dcCore::app()->plugins->moduleInfo('dcAdvancedCleaner', 'version'), 
        '>='
    )) {
        return null;
    }

    dcCore::app()->blog->settings->addNamespace('dcAdvancedCleaner');

    dcCore::app()->blog->settings->dcAdvancedCleaner->put(
        'dcAdvancedCleaner_behavior_active',
        true,
        'boolean',
        '',
        false,
        true
    );
    dcCore::app()->blog->settings->dcAdvancedCleaner->put(
        'dcAdvancedCleaner_dcproperty_hide',
        true,
        'boolean',
        '',
        false,
        true
    );

    return true;
} catch (Exception $e) {
    dcCore::app()->error->add($e->getMessage());

    return false;
}
