<?php

declare(strict_types=1);

namespace Dotclear\Plugin\dcAdvancedCleaner;

use Dotclear\Helper\Process\TraitProcess;
use Dotclear\Plugin\activityReport\{
    Action,
    ActivityReport,
    Group
};
use Dotclear\Plugin\Uninstaller\Uninstaller;

/**
 * @brief       dcAdvancedCleaner plugin activityReport class.
 * @ingroup     dcAdvancedCleaner
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class ActivityReportAction
{
    use TraitProcess;

    public static function init(): bool
    {
        return self::status(true);
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        $group = new Group(My::id(), My::name());

        $group->add(new Action(
            'uninstall',
            __('Uninstalling module'),
            '%s',
            'UninstallerBeforeAction',
            function (string $id, string $action, string $ns): void {
                $success = Uninstaller::instance()->cleaners->get($id)?->get($action)?->success;
                if (!is_null($success)) {
                    ActivityReport::instance()->addLog(My::id(), 'uninstall', [sprintf($success, $ns)]);
                }
            }
        ));

        ActivityReport::instance()->groups->add($group);

        return true;
    }
}
