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

dcCore::app()->blog->settings->addNamespace('dcAdvancedCleaner');

dcCore::app()->menu[dcAdmin::MENU_PLUGINS]->addItem(
    __('Advanced cleaner'),
    dcCore::app()->adminurl->get('admin.plugin.dcAdvancedCleaner'),
    dcPage::getPF('dcAdvancedCleaner/icon.svg'),
    preg_match(
        '/' . preg_quote(dcCore::app()->adminurl->get('admin.plugin.dcAdvancedCleaner')) . '(&.*)?$/',
        $_SERVER['REQUEST_URI']
    ),
    dcCore::app()->auth->isSuperAdmin()
);
