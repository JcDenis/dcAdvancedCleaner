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
use Dotclear\Module\MyPlugin;

class My extends MyPlugin
{
    public static function checkCustomContext(int $context): ?bool
    {
        return $context === self::PREPEND ? dcCore::app()->auth->isSuperAdmin() : null;
    }
}
