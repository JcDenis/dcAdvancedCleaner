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

# dcac class
Clearbricks::lib()->autoload(['advancedCleaner' => __DIR__ . '/inc/class.advanced.cleaner.php']);
Clearbricks::lib()->autoload(['dcAdvancedCleaner' => __DIR__ . '/inc/class.dc.advanced.cleaner.php']);
Clearbricks::lib()->autoload(['behaviorsDcAdvancedCleaner' => __DIR__ . '/inc/lib.dc.advanced.cleaner.behaviors.php']);
Clearbricks::lib()->autoload(['dcUninstaller' => __DIR__ . '/inc/class.dc.uninstaller.php']);
Clearbricks::lib()->autoload(['dcAdvancedCleanerActivityReportBehaviors' => __DIR__ . '/inc/lib.dc.advanced.cleaner.activityreport.php']);

# cleaners class
Clearbricks::lib()->autoload(['advancedCleanerVersions' => __DIR__ . '/inc/lib.advanced.cleaner.php']);
Clearbricks::lib()->autoload(['advancedCleanerSettings' => __DIR__ . '/inc/lib.advanced.cleaner.php']);
Clearbricks::lib()->autoload(['advancedCleanerTables' => __DIR__ . '/inc/lib.advanced.cleaner.php']);
Clearbricks::lib()->autoload(['advancedCleanerThemes' => __DIR__ . '/inc/lib.advanced.cleaner.php']);
Clearbricks::lib()->autoload(['advancedCleanerPlugins' => __DIR__ . '/inc/lib.advanced.cleaner.php']);
Clearbricks::lib()->autoload(['advancedCleanerCaches' => __DIR__ . '/inc/lib.advanced.cleaner.php']);
Clearbricks::lib()->autoload(['advancedCleanerVars' => __DIR__ . '/inc/lib.advanced.cleaner.php']);

dcCore::app()->addBehavior('advancedCleanerAdd', ['advancedCleanerVersions', 'create']);
dcCore::app()->addBehavior('advancedCleanerAdd', ['advancedCleanerSettings', 'create']);
dcCore::app()->addBehavior('advancedCleanerAdd', ['advancedCleanerTables', 'create']);
dcCore::app()->addBehavior('advancedCleanerAdd', ['advancedCleanerThemes', 'create']);
dcCore::app()->addBehavior('advancedCleanerAdd', ['advancedCleanerPlugins', 'create']);
dcCore::app()->addBehavior('advancedCleanerAdd', ['advancedCleanerCaches', 'create']);
dcCore::app()->addBehavior('advancedCleanerAdd', ['advancedCleanerVars', 'create']);

# dcac behaviors
dcCore::app()->addBehavior('adminDashboardFavoritesV2', ['behaviorsDcAdvancedCleaner', 'adminDashboardFavorites']);
dcCore::app()->addBehavior('pluginsToolsTabsV2', ['behaviorsDcAdvancedCleaner', 'pluginsToolsTabs']);
dcCore::app()->addBehavior('adminModulesListDoActions', ['behaviorsDcAdvancedCleaner', 'adminModulesListDoActions']);
dcCore::app()->addBehavior('pluginsBeforeDelete', ['behaviorsDcAdvancedCleaner', 'pluginsBeforeDelete']);
dcCore::app()->addBehavior('themeBeforeDelete', ['behaviorsDcAdvancedCleaner', 'themeBeforeDelete']);

if (defined('ACTIVITY_REPORT_V2')) {
    dcAdvancedCleanerActivityReportBehaviors::add();
}
