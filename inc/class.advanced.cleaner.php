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
abstract class advancedCleaner
{
    protected $core;

    private static $exclude = [
        '.', '..', '__MACOSX', '.svn', 'CVS', '.DS_Store', 'Thumbs.db'
    ];

    private $properties = [
        'id'   => '',
        'name' => '',
        'desc' => ''
    ];

    private $actions = [];

    final public function __construct(dcCore $core)
    {
        $this->core = $core;

        $this->init();
    }

    public static function create(arrayObject $o, dcCore $core)
    {
        $c = get_called_class();
        $o->append(new $c($core));
    }

    final public function __get(string $property)
    {
        return $this->getProperty($property);
    }

    final public function getProperty(string $property)
    {
        return $this->properties[$property] ?? null;
    }

    final protected function setProperties($property, $value = null): bool
    {
        $properties = is_array($property) ? $property : [$property => $value];
        foreach ($properties as $k => $v) {
            if (isset($this->properties[$k])) {
                $this->properties[$k] = (string) $v;
            }
        }

        return true;
    }

    final public function getActions()
    {
        return $this->actions;
    }

    final protected function setActions($action, $name = null): bool
    {
        $actions = is_array($action) ? $action : [$action => $name];
        foreach ($actions as $k => $v) {
            $this->actions[$k] = (string) $v;
        }

        return true;
    }

    abstract protected function init(): bool;

    abstract public function error($action): string;

    abstract public function official(): array;

    abstract public function get(): array;

    abstract public function set($action, $ns): bool;

    # helpers

    protected static function getDirs($roots)
    {
        if (!is_array($roots)) {
            $roots = [$roots];
        }
        $rs = [];
        $i  = 0;
        foreach ($roots as $root) {
            $dirs = files::scanDir($root);
            foreach ($dirs as $k) {
                if ('.' == $k || '..' == $k || !is_dir($root . '/' . $k)) {
                    continue;
                }
                $rs[$i]['key']   = $k;
                $rs[$i]['value'] = count(self::scanDir($root . '/' . $k));
                $i++;
            }
        }

        return $rs;
    }

    protected static function delDir($roots, $folder, $delfolder = true)
    {
        if (strpos($folder, '/')) {
            return false;
        }
        if (!is_array($roots)) {
            $roots = [$roots];
        }
        foreach ($roots as $root) {
            if (file_exists($root . '/' . $folder)) {
                return self::delTree($root . '/' . $folder, $delfolder);
            }
        }

        return false;
    }

    protected static function scanDir($path, $dir = '', $res = [])
    {
        $exclude = self::$exclude;

        $path = path::real($path);
        if (!is_dir($path) || !is_readable($path)) {
            return [];
        }
        $files = files::scandir($path);

        foreach ($files as $file) {
            if (in_array($file, $exclude)) {
                continue;
            }
            if (is_dir($path . '/' . $file)) {
                $res[] = $file;
                $res   = self::scanDir($path . '/' . $file, $dir . '/' . $file, $res);
            } else {
                $res[] = empty($dir) ? $file : $dir . '/' . $file;
            }
        }

        return $res;
    }

    protected static function delTree($dir, $delroot = true)
    {
        if (!is_dir($dir) || !is_readable($dir)) {
            return false;
        }
        if (substr($dir, -1) != '/') {
            $dir .= '/';
        }
        if (($d = @dir($dir)) === false) {
            return false;
        }
        while (($entryname = $d->read()) !== false) {
            if ($entryname != '.' && $entryname != '..') {
                if (is_dir($dir . '/' . $entryname)) {
                    if (!self::delTree($dir . '/' . $entryname)) {
                        return false;
                    }
                } else {
                    if (!@unlink($dir . '/' . $entryname)) {
                        return false;
                    }
                }
            }
        }
        $d->close();

        if ($delroot) {
            return @rmdir($dir);
        }

        return true;
    }
}
