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

use Dotclear\Plugin\Uninstaller\{
    CleanerParent,
    CleanersStack,
    Uninstaller
};
use Exception;

class ManageVars
{
    /**
     * @var ManageVars self instance
     */
    private static $container;

    public readonly CleanersStack $cleaners;
    public readonly ?CleanerParent $cleaner;
    public readonly string $related;
    public readonly array $entries;
    public readonly string $action;
    public readonly array $combo;

    protected function __construct()
    {
        $this->cleaners = Uninstaller::instance()->cleaners;

        $related = $_REQUEST['related'] ?? '';
        $entries = $_REQUEST['entries'] ?? [];
        $action  = $_POST['action']     ?? '';

        $cleaner = null;
        $combo   = [];
        foreach ($this->cleaners as $k) {
            $combo[$k->name] = $k->id;
            if ($k->id == ($_REQUEST['part'] ?? '/')) {
                $cleaner = $k;
            }
        }
        if ($cleaner === null) {
            $related = '';
            if (!($cleaner = $this->cleaners->get('caches'))) {
                throw new Exception(__('Failed to load cleaner'));
            }
        }

        $this->cleaner = $cleaner;
        $this->related = $related;
        $this->entries = is_array($entries) ? $entries : [];
        $this->action  = is_string($action) ? $action : '';
        $this->combo   = $combo;
    }

    public static function init(): ManageVars
    {
        if (!(self::$container instanceof self)) {
            self::$container = new self();
        }

        return self::$container;
    }
}
