<?php

declare(strict_types=1);

namespace Dotclear\Plugin\dcAdvancedCleaner;

use Dotclear\Plugin\Uninstaller\{
    CleanerParent,
    CleanersStack,
    Uninstaller
};
use Exception;

/**
 * @brief   dcAdvancedCleaner vars definition class.
 * @ingroup dcAdvancedCleaner
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class ManageVars
{
    /**
     * self instance.
     *
     * @var     ManageVars  $container
     */
    private static $container;

    /**
     * The cleaners stack.
     *
     * @var     CleanersStack   $cleaners
     */
    public readonly CleanersStack $cleaners;

    /**
     * The post form cleaner.
     *
     * @var     null|CleanerParent  $cleaner
     */
    public readonly ?CleanerParent $cleaner;

    /**
     * The post form related action id.
     *
     * @var     string  $related
     */
    public readonly string $related;

    /**
     * The post form selected ns.
     *
     * @var     array<int,string>   $entries
     */
    public readonly array $entries;

    /**
     * The post form action id.
     *
     * @var     string  $action
     */
    public readonly string $action;

    /**
     * The form actions combo.
     *
     * @var     array<string,string>    $combo
     */
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
