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

# Localized l10n
__('delete table');
__('delete cache files');
__('delete plugin files');
__('delete theme files');
__('delete the version number');
__('Uninstall extensions');
__('delete %s blog settings');
__('delete %s global settings');
__('delete all %s settings');
__('delete %s table');
__('delete %s version number');
__('delete %s plugin files');
__('delete %s theme file');
__('delete %s cache files');

/**
 * @brief Modules uninstall features handler
 *
 * Provides an object to handle modules uninstall features
 * (themes or plugins).
 * This class used dcAdvancedCleaner.
 */
class dcUninstaller
{
    protected $path;

    protected $modules   = [];
    protected $actions   = ['user' => [], 'callback' => []];
    protected $callbacks = ['user' => [], 'callback' => []];

    protected $id    = null;
    protected $mroot = null;

    private $ac;
    private $allowed_actions = null;

    /**
     * Object constructor.
     */
    public function __construct()
    {
        $this->ac = new dcAdvancedCleaner();

        $res = [];
        foreach ($this->ac->get() as $cleaner) {
            $res[$cleaner->id] = $cleaner->getActions();
        }
        $this->allowed_actions = $res;
    }

    public function getAllowedActions()
    {
        return $this->allowed_actions;
    }

    /**
     * Loads modules.
     *
     * Files _defines.php and _uninstall.php must be present on module
     * to be recognized.
     * (path separator depends on your OS).
     *
     * @param   string  $path   Separated list of paths
     */
    public function loadModules($path)
    {
        $this->path = explode(PATH_SEPARATOR, $path);

        foreach ($this->path as $root) {
            if (!is_dir($root) || !is_readable($root)) {
                continue;
            }
            if (substr($root, -1) != '/') {
                $root .= '/';
            }
            if (($d = @dir($root)) === false) {
                continue;
            }
            while (($entry = $d->read()) !== false) {
                $full_entry = $root . '/' . $entry;

                if ($entry != '.' && $entry != '..' && is_dir($full_entry)) {
                    $this->loadModule($full_entry);
                }
            }
            $d->close();
        }

        # Sort modules by name
        uasort($this->modules, [$this, 'sortModules']);
    }

    /**
     * Load one module.
     *
     * Files _defines.php and _uninstall.php must be present on module
     * to be recognized.
     *
     * @param   string  $root   path of module
     */
    public function loadModule($root)
    {
        if (file_exists($root . '/_define.php')
         && file_exists($root . '/_uninstall.php')) {
            $this->id    = basename($root);
            $this->mroot = $root;

            require $root . '/_define.php';
            require $root . '/_uninstall.php';

            $this->id    = null;
            $this->mroot = null;
        }
    }

    /**
     * This method registers a module in modules list.
     *
     * @param   string  $name       Module name
     * @param   string  $desc       Module description
     * @param   string  $author     Module author name
     * @param   string  $version    Module version
     */
    public function registerModule($name, $desc, $author, $version, $properties = [])
    {
        if ($this->id) {
            $this->modules[$this->id] = [
                'root'          => $this->mroot,
                'name'          => $name,
                'desc'          => $desc,
                'author'        => $author,
                'version'       => $version,
                'root_writable' => is_writable($this->mroot),
            ];
        }
    }

    /**
     * Returns all modules associative array or only one module if <var>$id</var>
     * is present.
     *
     * @param   string  $id     Optionnal module ID
     *
     * @return  array   Modules
     */
    public function getModules($id = null)
    {
        if ($id && isset($this->modules[$id])) {
            return $this->modules[$id];
        }

        return $this->modules;
    }

    /**
     * Returns true if the module with ID <var>$id</var> exists.
     *
     * @param   string  $id     Module ID
     *
     * @return  boolean     Success
     */
    public function moduleExists($id)
    {
        return isset($this->modules[$id]);
    }

    /**
     * Add a predefined action to unsintall features.
     *
     * This action is set in _uninstall.php.
     *
     * @param   string  $type       Type of action (from $allowed_actions)
     * @param   string  $action     Action (from $allowed_actions)
     * @param   string  $ns         Name of setting related to module.
     * @param   string  $desc       Description of action
     */
    protected function addUserAction($type, $action, $ns, $desc = '')
    {
        $this->addAction('user', $type, $action, $ns, $desc);
    }

    protected function addDirectAction($type, $action, $ns, $desc = '')
    {
        $this->addAction('direct', $type, $action, $ns, $desc);
    }

    private function addAction($group, $type, $action, $ns, $desc)
    {
        $group = self::group($group);

        if (null === $this->id) {
            return null;
        }
        if (empty($type) || empty($ns)) {
            return null;
        }
        if (!isset($this->allowed_actions[$type][$action])) {
            return null;
        }
        if (empty($desc)) {
            $desc = __($action);
        }
        $this->actions[$group][$this->id][$type][] = [
            'ns'     => $ns,
            'action' => $action,
            'desc'   => $desc,
        ];
    }

    /**
     * Returns modules <var>$id</var> predefined actions associative array
     *
     * @param   string  $id     Optionnal module ID
     * @return  array   Modules id
     */
    public function getUserActions($id)
    {
        return $this->getActions('user', $id);
    }

    public function getDirectActions($id)
    {
        return $this->getActions('direct', $id);
    }

    protected function getActions($group, $id)
    {
        $group = self::group($group);

        if (!isset($this->actions[$group][$id])) {
            return [];
        }
        $res = [];
        foreach ($this->allowed_actions as $k => $v) {
            if (!isset($this->actions[$group][$id][$k])) {
                continue;
            }
            $res[$k] = $this->actions[$group][$id][$k];
        }

        return $res;
    }

    /**
     * Add a callable function for unsintall features.
     *
     * This action is set in _uninstall.php.
     *
     * @param   string  $func   Callable function
     * @param   string  $desc   Description of action
     */
    protected function addUserCallback($func, $desc = '')
    {
        $this->addCallback('user', $func, $desc);
    }

    protected function addDirectCallback($func, $desc = '')
    {
        $this->addCallback('direct', $func, $desc);
    }

    private function addCallback($group, $func, $desc)
    {
        $group = self::group($group);

        if (null === $this->id) {
            return null;
        }
        if (empty($desc)) {
            $desc = __('extra action');
        }
        if (!is_callable($func)) {
            return null;
        }
        $this->callbacks[$group][$this->id][] = [
            'func' => $func,
            'desc' => $desc,
        ];
    }

    /**
     * Returns modules <var>$id</var> callback actions associative array

     * @param   string  $id     Optionnal module ID
     *
     * @return  array   Modules id
     */
    public function getUserCallbacks($id)
    {
        return $this->getCallbacks('user', $id);
    }

    public function getDirectCallbacks($id)
    {
        return $this->getCallbacks('direct', $id);
    }

    protected function getCallbacks($group, $id)
    {
        $group = self::group($group);

        if (!isset($this->callbacks[$group][$id])) {
            return [];
        }

        return $this->callbacks[$group][$id];
    }

    /**
     * Execute a predifined action.
     *
     * This function call dcAdvancedCleaner to do actions.
     *
     * @param   string      $type       Type of action (from $allowed_actions)
     * @param   string      $action     Action (from $allowed_actions)
     * @param   string      $ns         Name of setting related to module.
     *
     * @return boolean      Success
     */
    public function execute($type, $action, $ns)
    {
        if (!isset($this->allowed_actions[$type][$action]) || empty($ns)) {
            return false;
        }
        $this->ac->set($type, $action, $ns);

        return true;
    }

    private function sortModules($a, $b)
    {
        return strcasecmp($a['name'], $b['name']);
    }

    private function group($group)
    {
        return in_array($group, ['user','direct']) ? $group : null;
    }
}
