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

$core->blog->settings->addNamespace('dcAdvancedCleaner');

$_menu['Plugins']->addItem(
    __('Advanced cleaner'),
    $core->adminurl->get('admin.plugin.dcAdvancedCleaner'),
    dcPage::getPF('dcAdvancedCleaner/icon.png'),
    preg_match(
        '/' . preg_quote($core->adminurl->get('admin.plugin.dcAdvancedCleaner')) . '(&.*)?$/', 
        $_SERVER['REQUEST_URI']
    ),
    $core->auth->isSuperAdmin()
);