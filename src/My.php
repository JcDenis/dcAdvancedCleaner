<?php

declare(strict_types=1);

namespace Dotclear\Plugin\dcAdvancedCleaner;

use Dotclear\App;
use Dotclear\Module\MyPlugin;

/**
 * @brief   dcAdvancedCleaner My helper.
 * @ingroup dcAdvancedCleaner
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class My extends MyPlugin
{
    public static function checkCustomContext(int $context): ?bool
    {
        return match ($context) {
            // Limit to super admin
            self::MODULE => App::auth()->isSuperAdmin(),
            default      => null,
        };
    }
}
