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

use Dotclear\Core\Process;
use Dotclear\Plugin\activityReport\{
    Action,
    ActivityReport,
    Group
};
use Dotclear\Plugin\Uninstaller\Uninstaller;

class Prepend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::PREPEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        // log plugin Uninstaller actions
        if (defined('ACTIVITY_REPORT') && ACTIVITY_REPORT == 3) {
            $group = new Group(My::id(), My::name());
            $group->add(new Action(
                'uninstaller',
                __('Uninstalling module'),
                '%s',
                'UninstallerBeforeAction',
                function (string $id, string $action, string $ns): void {
                    $success = Uninstaller::instance()->cleaners->get($id)?->get($action)?->success;
                    if (!is_null($success)) {
                        ActivityReport::instance()->addLog(My::id(), 'uninstaller', [sprintf($success, $ns)]);
                    }
                }
            ));
            ActivityReport::instance()->groups->add($group);
        }

        return true;
    }
}
