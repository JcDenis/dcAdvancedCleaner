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

class dcAdvancedCleanerActivityReportBehaviors
{
    public static function maintenance($type, $action, $ns)
    {
        dcCore::app()->activityReport->addLog('dcadvancedcleaner', 'maintenance', [$type,$action, $ns]);
    }

    public static function add()
    {
        // This file is used with plugin activityReport
        dcCore::app()->activityReport->addGroup(
            'dcadvancedcleaner',
            __('Plugin dcAdvancedCleaner')
        );

        // from BEHAVIOR dcAdvancedCleanerBeforeAction
        // in dcAdvancedCleaner/inc/class.dc.advanced.cleaner.php
        dcCore::app()->activityReport->addAction(
            'dcadvancedcleaner',
            'maintenance',
            __('Maintenance'),
            __('New action from dcAdvancedCleaner has been made with type="%s", action="%s", ns="%s".'),
            'dcAdvancedCleanerBeforeAction',
            ['dcAdvancedCleanerActivityReportBehaviors', 'maintenance']
        );
    }
}
