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

use dcAdmin;
use dcCore;
use dcFavorites;
use dcNsProcess;
use dcMenu;
use dcPage;

class Backend extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_CONTEXT_ADMIN')
            && My::phpCompliant();

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init || !dcCore::app()->plugins->moduleExists('Uninstaller')) {
            return false;
        }

        if (!is_null(dcCore::app()->auth)
            && !is_null(dcCore::app()->adminurl)
            && (dcCore::app()->menu[dcAdmin::MENU_PLUGINS] instanceof dcMenu)
        ) {
            dcCore::app()->menu[dcAdmin::MENU_PLUGINS]->addItem(
                My::name(),
                dcCore::app()->adminurl->get('admin.plugin.' . My::id()),
                dcPage::getPF(My::id() . '/icon.svg'),
                preg_match(
                    '/' . preg_quote(dcCore::app()->adminurl->get('admin.plugin.' . My::id())) . '(&.*)?$/',
                    $_SERVER['REQUEST_URI']
                ),
                dcCore::app()->auth->isSuperAdmin()
            );
        }

        dcCore::app()->addBehaviors([
            'adminDashboardFavoritesV2' => function (dcFavorites $favs): void {
                $favs->register(My::id(), [
                    'title'      => My::name(),
                    'url'        => dcCore::app()->adminurl?->get('admin.plugin.' . My::id()),
                    'small-icon' => dcPage::getPF(My::id() . '/icon.svg'),
                    'large-icon' => dcPage::getPF(My::id() . '/icon-big.svg'),
                    //'permissions' => dcCore::app()->auth?->isSuperAdmin(),
                ]);
            },
        ]);

        return true;
    }
}
