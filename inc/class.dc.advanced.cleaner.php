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
if (!defined('DC_ADMIN_CONTEXT')) {
    return null;
}

class dcAdvancedCleaner
{
    protected $cleaners = [];

    public function __construct()
    {
        $cleaners = new arrayObject();

        try {
            dcCore::app()->callBehavior('advancedCleanerAdd', $cleaners);

            foreach ($cleaners as $cleaner) {
                if ($cleaner instanceof advancedCleaner && !isset($this->cleaners[$cleaner->id])) {
                    $this->cleaners[$cleaner->id] = $cleaner;
                }
            }
        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());
        }
    }

    public function get($type = null, $silent = false)
    {
        if (null === $type) {
            return $this->cleaners;
        }
        if (isset($this->cleaners[$type])) {
            return $this->cleaners[$type];
        }
        if ($silent) {
            return false;
        }

        throw new exception(sprintf(__('unknow cleaner type %s'), $type));
    }

    public function set($type, $action, $ns)
    {
        if (!isset($this->cleaners[$type])) {
            throw new exception(sprintf(__('unknow cleaner type %s'), $type));
        }
        if (strtolower($ns) == 'dcadvancedcleaner') {
            throw new exception(__("dcAdvancedCleaner can't remove itself"));
        }

        # BEHAVIOR dcAdvancedCleanerBeforeAction
        dcCore::app()->callBehavior('dcAdvancedCleanerBeforeAction', $type, $action, $ns);

        $ret = $this->cleaners[$type]->set($action, $ns);

        if ($ret === false) {
            $msg = $this->cleaners[$type]->error($action);

            throw new Exception($msg ?? __('Unknow error'));
        }

        return true;
    }
}
