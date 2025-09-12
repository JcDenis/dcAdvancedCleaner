<?php

declare(strict_types=1);

namespace Dotclear\Plugin\dcAdvancedCleaner;

use Dotclear\App;
use Dotclear\Helper\Process\TraitProcess;
use Dotclear\Core\Backend\Favorites;

/**
 * @brief   dcAdvancedCleaner backend class.
 * @ingroup dcAdvancedCleaner
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Backend
{
    use TraitProcess;

    public static function init(): bool
    {
        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status() || !App::plugins()->moduleExists('Uninstaller')) {
            return false;
        }

        My::addBackendMenuItem();

        App::behavior()->addBehaviors([
            'adminDashboardFavoritesV2' => function (Favorites $favs): void {
                $favs->register(My::id(), [
                    'title'      => My::name(),
                    'url'        => My::manageUrl(),
                    'small-icon' => My::icons(),
                    'large-icon' => My::icons(),
                ]);
            },
        ]);

        return true;
    }
}
