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
        // Limit to backend and super admin
        return App::task()->checkContext('BACKEND') && App::auth()->isSuperAdmin();
    }
}
