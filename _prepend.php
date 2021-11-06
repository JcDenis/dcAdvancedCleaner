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
if (!defined('DC_CONTEXT_ADMIN')) {
    return null;
}

$d = dirname(__FILE__) . '/inc/';

# dcac class
$__autoload['advancedCleaner']                          = $d . 'class.advanced.cleaner.php';
$__autoload['dcAdvancedCleaner']                        = $d . 'class.dc.advanced.cleaner.php';
$__autoload['behaviorsDcAdvancedCleaner']               = $d . 'lib.dc.advanced.cleaner.behaviors.php';
$__autoload['dcUninstaller']                            = $d . 'class.dc.uninstaller.php';
$__autoload['dcAdvancedCleanerActivityReportBehaviors'] = $d . 'lib.dc.advanced.cleaner.activityreport.php';

# cleaners class
$__autoload['advancedCleanerVersions'] = $d . 'lib.advanced.cleaner.php';
$__autoload['advancedCleanerSettings'] = $d . 'lib.advanced.cleaner.php';
$__autoload['advancedCleanerTables']   = $d . 'lib.advanced.cleaner.php';
$__autoload['advancedCleanerThemes']   = $d . 'lib.advanced.cleaner.php';
$__autoload['advancedCleanerPlugins']  = $d . 'lib.advanced.cleaner.php';
$__autoload['advancedCleanerCaches']   = $d . 'lib.advanced.cleaner.php';
$__autoload['advancedCleanerVars']     = $d . 'lib.advanced.cleaner.php';

$core->addBehavior('advancedCleanerAdd', ['advancedCleanerVersions', 'create']);
$core->addBehavior('advancedCleanerAdd', ['advancedCleanerSettings', 'create']);
$core->addBehavior('advancedCleanerAdd', ['advancedCleanerTables', 'create']);
$core->addBehavior('advancedCleanerAdd', ['advancedCleanerThemes', 'create']);
$core->addBehavior('advancedCleanerAdd', ['advancedCleanerPlugins', 'create']);
$core->addBehavior('advancedCleanerAdd', ['advancedCleanerCaches', 'create']);
$core->addBehavior('advancedCleanerAdd', ['advancedCleanerVars', 'create']);

# dcac behaviors
$core->addBehavior('adminDashboardFavorites', ['behaviorsDcAdvancedCleaner', 'adminDashboardFavorites']);
$core->addBehavior('pluginsToolsTabs', ['behaviorsDcAdvancedCleaner', 'pluginsToolsTabs']);
$core->addBehavior('adminModulesListDoActions', ['behaviorsDcAdvancedCleaner', 'adminModulesListDoActions']);
$core->addBehavior('pluginsBeforeDelete', ['behaviorsDcAdvancedCleaner', 'pluginsBeforeDelete']);
$core->addBehavior('themeBeforeDelete', ['behaviorsDcAdvancedCleaner', 'themeBeforeDelete']);

if (defined('ACTIVITY_REPORT')) {
    dcAdvancedCleanerActivityReportBehaviors::add($core);
}
