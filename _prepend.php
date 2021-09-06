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

$d = dirname(__FILE__) . '/inc/';

$core->blog->settings->addNamespace('dcAdvancedCleaner');

$__autoload['dcAdvancedCleaner'] = $d . 'class.dc.advanced.cleaner.php';
$__autoload['behaviorsDcAdvancedCleaner'] = $d . 'lib.dc.advanced.cleaner.behaviors.php';
$__autoload['dcUninstaller'] = $d . 'class.dc.uninstaller.php';
$__autoload['dcAdvancedCleanerActivityReportBehaviors'] = $d . 'lib.dc.advanced.cleaner.activityreport.php';

$core->addBehavior('pluginsToolsTabs',
    ['behaviorsDcAdvancedCleaner', 'pluginsToolsTabs']);
$core->addBehavior('pluginsBeforeDelete',
    ['behaviorsDcAdvancedCleaner', 'pluginsBeforeDelete']);
$core->addBehavior('themeBeforeDelete',
    ['behaviorsDcAdvancedCleaner', 'themeBeforeDelete']);
$core->addBehavior('dcAdvancedCleanerAdminTabs',
    ['behaviorsDcAdvancedCleaner', 'dcAdvancedCleanerAdminTabs']);

if (defined('ACTIVITY_REPORT')) {
    dcAdvancedCleanerActivityReportBehaviors::add($core);
}