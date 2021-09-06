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

if (!defined('DC_CONTEXT_ADMIN')){return;}

$_menu['Plugins']->addItem(
    __('Advanced cleaner'),
    'plugin.php?p=dcAdvancedCleaner',
    'index.php?pf=dcAdvancedCleaner/icon.png',
    preg_match('/plugin.php\?p=dcAdvancedCleaner(&.*)?$/',$_SERVER['REQUEST_URI']),
    $core->auth->isSuperAdmin()
);

$core->addBehavior('adminDashboardFavorites','dcAdvancedCleanerDashboardFavorites');

function dcAdvancedCleanerDashboardFavorites($core,$favs)
{
    $favs->register('dcAdvancedCleaner', array(
        'title' => __('Advanced cleaner'),
        'url' => 'plugin.php?p=dcAdvancedCleaner',
        'small-icon' => 'index.php?pf=dcAdvancedCleaner/icon.png',
        'large-icon' => 'index.php?pf=dcAdvancedCleaner/icon-big.png',
        'permissions' => 'usage,contentadmin'
    ));
}