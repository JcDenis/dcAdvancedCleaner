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
declare(strict_types=1);

namespace Dotclear\Plugin\dcAdvancedCleaner;

use dcCore;
use dcNsProcess;

class Prepend extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_CONTEXT_ADMIN')
            && My::phpCompliant()
            && dcCore::app()->auth?->isSuperAdmin();

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        if (defined('ACTIVITY_REPORT_V2')) {
            dcCore::app()->activityReport->addGroup(
                My::id(),
                __('Plugin dcAdvancedCleaner')
            );

            dcCore::app()->activityReport->addAction(
                My::id(),
                'maintenance',
                __('Maintenance'),
                __('New action from dcAdvancedCleaner has been made with type="%s", action="%s", ns="%s".'),
                'dcAdvancedCleanerBeforeAction',
                function ($type, $action, $ns) {
                    dcCore::app()->activityReport->addLog(My::id(), 'maintenance', [$type,$action, $ns]);
                }
            );
        }

        return true;
    }
}
