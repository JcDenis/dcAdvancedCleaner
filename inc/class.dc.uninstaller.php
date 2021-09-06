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

if (!defined('DC_ADMIN_CONTEXT')){return;}

/**
@brief Modules uninstall features handler

Provides an object to handle modules uninstall features
(themes or plugins). 
This class used dcAdvancedCleaner.
*/
class dcUninstaller
{
    protected $path;

    protected $modules = array();    ///< <b>array</b> Modules informations array
    protected $actions = array('user'=>array(),'callback'=>array());
    protected $callbacks = array('user'=>array(),'callback'=>array());

    protected $id = null;
    protected $mroot = null;

    /**
    Array of all allowed properties to uninstall parts of modules.
    'settings' : settings set on dcSettings,
    'tables' : if module creates table,
    'plugins' : if module has files on plugin path,
    'themes' : if module has files on theme path, (on current blog)
    'caches' : if module has files on DC caches path,
    'versions' : if module set a versions on DC table 'version' 
    */
    protected static $allowed_properties = array(
        'versions' => array(
            'delete' => 'delete version in dc'
        ),
        'settings' => array(
            'delete_global' => 'delete global settings',
            'delete_local' => 'delete local settings',
            'delete_all' => 'delete all settings'
        ),
        'tables' => array(
            'empty' => 'empty table',
            'delete' => 'delete table'
        ),
        'plugins' => array(
            'empty' => 'empty plugin folder',
            'delete' => 'delete plugin folder'
        ),
        'themes' => array(
            'empty' => 'empty theme folder',
            'delete' => 'delete theme folder'
        ),
        'caches' => array(
            'empty' => 'empty cache folder',
            'delete' => 'delete cache folder'
        )
    );

    protected static $priority_properties = array(
        'versions','settings','tables','themes','plugins','caches'
    );

    public $core;    ///< <b>dcCore</b>    dcCore instance

    /**
    Object constructor.

    @param    core        <b>dcCore</b>    dcCore instance
    */
    public function __construct($core)
    {
        $this->core =& $core;
    }

    public static function getAllowedProperties()
    {
        return self::$allowed_properties;
    }

    /**
    Loads modules.
    Files _defines.php and _uninstall.php must be present on module 
    to be recognized.
    (path separator depends on your OS).

    @param    path            <b>string</b>        Separated list of paths
    */
    public function loadModules($path)
    {
        $this->path = explode(PATH_SEPARATOR,$path);

        foreach ($this->path as $root)
        {
            if (!is_dir($root) || !is_readable($root)) continue;

            if (substr($root,-1) != '/') $root .= '/';

            if (($d = @dir($root)) === false) continue;

            while (($entry = $d->read()) !== false)
            {
                $full_entry = $root.'/'.$entry;

                if ($entry != '.' && $entry != '..' && is_dir($full_entry))
                {
                    $this->loadModule($full_entry);
                }
            }
            $d->close();
        }

        # Sort modules by name
        uasort($this->modules,array($this,'sortModules'));
    }

    /**
    Load one module.
    Files _defines.php and _uninstall.php must be present on module 
    to be recognized.

    @param    root            <b>string</b>        path of module
    */
    public function loadModule($root)
    {
        if (file_exists($root.'/_define.php')
         && file_exists($root.'/_uninstall.php')) {

            $this->id = basename($root);
            $this->mroot = $root;

            require $root.'/_define.php';
            require $root.'/_uninstall.php';

            $this->id = null;
            $this->mroot = null;
        }
    }

    /**
    This method registers a module in modules list. You should use 
    this to register a new module.

    @param    name            <b>string</b>        Module name
    @param    desc            <b>string</b>        Module description
    @param    author        <b>string</b>        Module author name
    @param    version        <b>string</b>        Module version
    */
    public function registerModule($name,$desc,$author,$version)
    {
        if ($this->id) {
            $this->modules[$this->id] = array(
            'root' => $this->mroot,
            'name' => $name,
            'desc' => $desc,
            'author' => $author,
            'version' => $version,
            'root_writable' => is_writable($this->mroot)
            );
        }
    }

    /**
    Returns all modules associative array or only one module if <var>$id</var>
    is present.

    @param    id        <b>string</b>        Optionnal module ID
    @return    <b>array</b>
    */
    public function getModules($id=null)
    {
        if ($id && isset($this->modules[$id])) {
            return $this->modules[$id];
        }
        return $this->modules;
    }

    /**
    Returns true if the module with ID <var>$id</var> exists.

    @param    id        <b>string</b>        Module ID
    @return    <b>boolean</b>
    */
    public function moduleExists($id)
    {
        return isset($this->modules[$id]);
    }

    /**
    Add a predefined action to unsintall features.
    This action is set in _uninstall.php.

    @param    type        <b>string</b>        Type of action (from $allowed_properties)
    @param    action    <b>string</b>        Action (from $allowed_properties)
    @param    ns        <b>string</b>        Name of setting related to module.
    @param    desc        <b>string</b>        Description of action
    */
    protected function addUserAction($type,$action,$ns,$desc='')
    {
        $this->addAction('user',$type,$action,$ns,$desc);
    }

    protected function addDirectAction($type,$action,$ns,$desc='')
    {
        $this->addAction('direct',$type,$action,$ns,$desc);
    }

    private function addAction($group,$type,$action,$ns,$desc)
    {
        $group = self::group($group);

        if (null === $this->id) return;

        if (empty($type) || empty($ns)) return;

        if (!isset(self::$allowed_properties[$type][$action])) return;

        if (empty($desc)) $desc = __($action);

        $this->actions[$group][$this->id][$type][] = array(
            'ns' => $ns,
            'action' => $action,
            'desc' => $desc
        );
    }

    /**
    Returns modules <var>$id</var> predefined actions associative array
    ordered by priority

    @param    id        <b>string</b>        Optionnal module ID
    @return    <b>array</b>
    */
    public function getUserActions($id)
    {
        return $this->getActions('user',$id);
    }

    public function getDirectActions($id)
    {
        return $this->getActions('direct',$id);
    }

    protected function getActions($group,$id)
    {
        $group = self::group($group);

        if (!isset($this->actions[$group][$id])) return array();

        $res = array();
        foreach(self::$priority_properties as $k => $v)
        {
            if (!isset($this->actions[$group][$id][$v])) continue;
            $res[$v] = $this->actions[$group][$id][$v];
        }

        return $res;
    }

    /**
    Add a callable function for unsintall features.
    This action is set in _uninstall.php.

    @param    func        <b>string</b>        Callable function
    @param    desc        <b>string</b>        Description of action
    */
    protected function addUserCallback($func,$desc='')
    {
        $this->addCallback('user',$func,$desc);
    }

    protected function addDirectCallback($func,$desc='')
    {
        $this->addCallback('direct',$func,$desc);
    }

    private function addCallback($group,$func,$desc)
    {
        $group = self::group($group);

        if (null === $this->id) return;

        if (empty($desc)) $desc = __('extra action');

        if (!is_callable($func)) return;

        $this->callbacks[$group][$this->id][] = array(
            'func' => $func,
            'desc' => $desc
        );
    }

    /**
    Returns modules <var>$id</var> callback actions associative array

    @param    id        <b>string</b>        Optionnal module ID
    @return    <b>array</b>
    */
    public function getUserCallbacks($id)
    {
        return $this->getCallbacks('user',$id);
    }

    public function getDirectCallbacks($id)
    {
        return $this->getCallbacks('direct',$id);
    }

    protected function getCallbacks($group,$id)
    {
        $group = self::group($group);

        if (!isset($this->callbacks[$group][$id])) return array();

        return $this->callbacks[$group][$id];
    }

    /**
    Execute a predifined action. This function call dcAdvancedCleaner 
    to do actions.

    @param    type        <b>string</b>        Type of action (from $allowed_properties)
    @param    action    <b>string</b>        Action (from $allowed_properties)
    @param    ns        <b>string</b>        Name of setting related to module.
    @return    <b>array</b>
    */
    public function execute($type,$action,$ns)
    {
        $prop = $this->getAllowedProperties();

        if (!isset($prop[$type][$action]) || empty($ns)) return;

        dcAdvancedCleaner::execute($this->core,$type,$action,$ns);
    }

    private function sortModules($a,$b)
    {
        return strcasecmp($a['name'],$b['name']);
    }

    private function group($group)
    {
        return in_array($group,array('user','direct')) ? $group : null;
    }
}