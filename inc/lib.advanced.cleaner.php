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
class advancedCleanerSettings extends advancedCleaner
{
    protected function init(): bool
    {
        $this->setProperties([
            'id'   => 'settings',
            'name' => __('Settings'),
            'desc' => __('Namespaces registered in dcSettings'),
        ]);

        $this->setActions([
            'delete_global' => __('delete global settings'),
            'delete_local'  => __('delete blog settings'),
            'delete_all'    => __('delete all settings'),
        ]);

        return true;
    }

    public function error($action): string
    {
        if ($action == 'delete_global') {
            return __('Failed to delete global settings');
        }
        if ($action == 'delete_local') {
            return __('Failed to delete local settings');
        }
        if ($action == 'delete_all') {
            return __('Failed to delete all settings');
        }

        return '';
    }

    public function official(): array
    {
        return [
            'akismet',
            'antispam',
            'breadcrumb',
            'dcckeditor',
            'dclegacyeditor',
            'maintenance',
            'pages',
            'pings',
            'system',
            'themes',
            'widgets',
        ];
    }

    public function get(): array
    {
        $res = dcCore::app()->con->select(
            'SELECT setting_ns ' .
            'FROM ' . dcCore::app()->prefix . 'setting ' .
            'WHERE blog_id IS NULL ' .
            'OR blog_id IS NOT NULL ' .
            'GROUP BY setting_ns'
        );

        $rs = [];
        $i  = 0;
        while ($res->fetch()) {
            $rs[$i]['key']   = $res->setting_ns;
            $rs[$i]['value'] = dcCore::app()->con->select(
                'SELECT count(*) FROM ' . dcCore::app()->prefix . 'setting ' .
                "WHERE setting_ns = '" . $res->setting_ns . "' " .
                'AND (blog_id IS NULL OR blog_id IS NOT NULL) ' .
                'GROUP BY setting_ns '
            )->f(0);
            $i++;
        }

        return $rs;
    }

    public function set($action, $ns): bool
    {
        if ($action == 'delete_global') {
            dcCore::app()->con->execute(
                'DELETE FROM ' . dcCore::app()->prefix . 'setting ' .
                'WHERE blog_id IS NULL ' .
                "AND setting_ns = '" . dcCore::app()->con->escape($ns) . "' "
            );

            return true;
        }
        if ($action == 'delete_local') {
            dcCore::app()->con->execute(
                'DELETE FROM ' . dcCore::app()->prefix . 'setting ' .
                "WHERE blog_id = '" . dcCore::app()->con->escape(dcCore::app()->blog->id) . "' " .
                "AND setting_ns = '" . dcCore::app()->con->escape($ns) . "' "
            );

            return true;
        }
        if ($action == 'delete_all') {
            dcCore::app()->con->execute(
                'DELETE FROM ' . dcCore::app()->prefix . 'setting ' .
                "WHERE setting_ns = '" . dcCore::app()->con->escape($ns) . "' " .
                "AND (blog_id IS NULL OR blog_id != '') "
            );

            return true;
        }

        return false;
    }
}

class advancedCleanerTables extends advancedCleaner
{
    protected function init(): bool
    {
        $this->setProperties([
            'id'   => 'tables',
            'name' => __('Tables'),
            'desc' => __('All database tables of Dotclear'),
        ]);

        $this->setActions([
            'delete' => __('delete'),
            'empty'  => __('empty'),
        ]);

        return true;
    }

    public function error($action): string
    {
        if ($action == 'empty') {
            return __('Failed to empty table');
        }
        if ($action == 'delete') {
            return __('Failed to delete table');
        }

        return '';
    }

    public function official(): array
    {
        return [
            'blog',
            'category',
            'comment',
            'link',
            'log',
            'media',
            'meta',
            'permissions',
            'ping',
            'post',
            'post_media',
            'pref',
            'session',
            'setting',
            'spamrule',
            'user',
            'version',
        ];
    }

    public function get(): array
    {
        $object = dbSchema::init(dcCore::app()->con);
        $res    = $object->getTables();

        $rs = [];
        $i  = 0;
        foreach ($res as $k => $v) {
            if ('' != dcCore::app()->prefix) {
                if (!preg_match('/^' . preg_quote(dcCore::app()->prefix) . '(.*?)$/', $v, $m)) {
                    continue;
                }
                $v = $m[1];
            }
            $rs[$i]['key']   = $v;
            $rs[$i]['value'] = dcCore::app()->con->select('SELECT count(*) FROM ' . $res[$k])->f(0);
            $i++;
        }

        return $rs;
    }

    public function set($action, $ns): bool
    {
        if (in_array($action, ['empty', 'delete'])) {
            dcCore::app()->con->execute(
                'DELETE FROM ' . dcCore::app()->con->escapeSystem(dcCore::app()->prefix . $ns)
            );
        }
        if ($action == 'empty') {
            return true;
        }
        if ($action == 'delete') {
            dcCore::app()->con->execute(
                'DROP TABLE ' . dcCore::app()->con->escapeSystem(dcCore::app()->prefix . $ns)
            );

            return true;
        }

        return false;
    }
}

class advancedCleanerVersions extends advancedCleaner
{
    protected function init(): bool
    {
        $this->setProperties([
            'id'   => 'versions',
            'name' => __('Versions'),
            'desc' => __('Versions registered in table "version" of Dotclear'),
        ]);

        $this->setActions([
            'delete' => __('delete'),
        ]);

        return true;
    }

    public function error($action): string
    {
        if ($action == 'delete') {
            return __('Failed to delete version');
        }

        return '';
    }

    public function official(): array
    {
        return [
            'antispam',
            'blogroll',
            'blowupConfig',
            'core',
            'dcCKEditor',
            'dcLegacyEditor',
            'pages',
            'pings',
            'simpleMenu',
            'tags',
            'widgets',
        ];
    }

    public function get(): array
    {
        $res = dcCore::app()->con->select('SELECT * FROM ' . dcCore::app()->prefix . 'version');

        $rs = [];
        $i  = 0;
        while ($res->fetch()) {
            $rs[$i]['key']   = $res->module;
            $rs[$i]['value'] = $res->version;
            $i++;
        }

        return $rs;
    }

    public function set($action, $ns): bool
    {
        if ($action == 'delete') {
            dcCore::app()->con->execute(
                'DELETE FROM  ' . dcCore::app()->prefix . 'version ' .
                "WHERE module = '" . dcCore::app()->con->escape($ns) . "' "
            );

            return true;
        }

        return false;
    }
}

class advancedCleanerPlugins extends advancedCleaner
{
    protected function init(): bool
    {
        $this->setProperties([
            'id'   => 'plugins',
            'name' => __('Plugins'),
            'desc' => __('Folders from plugins directories'),
        ]);

        $this->setActions([
            'delete' => __('delete'),
            'empty'  => __('empty'),
        ]);

        return true;
    }

    public function error($action): string
    {
        if ($action == 'empty') {
            return __('Failed to empty plugin folder');
        }
        if ($action == 'delete') {
            return __('Failed to delete plugin folder');
        }

        return '';
    }

    public function official(): array
    {
        return explode(',', DC_DISTRIB_PLUGINS);
    }

    public function get(): array
    {
        $res = self::getDirs(explode(PATH_SEPARATOR, DC_PLUGINS_ROOT));
        sort($res);

        return $res;
    }

    public function set($action, $ns): bool
    {
        if ($action == 'empty') {
            $res = explode(PATH_SEPARATOR, DC_PLUGINS_ROOT);
            self::delDir($res, $ns, false);

            return true;
        }
        if ($action == 'delete') {
            $res = explode(PATH_SEPARATOR, DC_PLUGINS_ROOT);
            self::delDir($res, $ns, true);

            return true;
        }

        return false;
    }
}

class advancedCleanerThemes extends advancedCleaner
{
    protected function init(): bool
    {
        $this->setProperties([
            'id'   => 'themes',
            'name' => __('Themes'),
            'desc' => __('Folders from blog themes directory'),
        ]);

        $this->setActions([
            'delete' => __('delete'),
            'empty'  => __('empty'),
        ]);

        return true;
    }

    public function error($action): string
    {
        if ($action == 'empty') {
            return __('Failed to empty themes folder');
        }
        if ($action == 'delete') {
            return __('Failed to delete themes folder');
        }

        return '';
    }

    public function official(): array
    {
        return explode(',', DC_DISTRIB_THEMES);
    }

    public function get(): array
    {
        $res = self::getDirs(dcCore::app()->blog->themes_path);
        sort($res);

        return $res;
    }

    public function set($action, $ns): bool
    {
        if ($action == 'empty') {
            self::delDir(dcCore::app()->blog->themes_path, $ns, false);

            return true;
        }
        if ($action == 'delete') {
            self::delDir(dcCore::app()->blog->themes_path, $ns, true);

            return true;
        }

        return false;
    }
}

class advancedCleanerCaches extends advancedCleaner
{
    protected function init(): bool
    {
        $this->setProperties([
            'id'   => 'caches',
            'name' => __('Cache'),
            'desc' => __('Folders from cache directory'),
        ]);

        $this->setActions([
            'delete' => __('delete'),
            'empty'  => __('empty'),
        ]);

        return true;
    }

    public function error($action): string
    {
        if ($action == 'empty') {
            return __('Failed to empty cache folder');
        }
        if ($action == 'delete') {
            return __('Failed to delete cache folder');
        }

        return '';
    }

    public function official(): array
    {
        return ['cbfeed', 'cbtpl', 'dcrepo', 'versions'];
    }

    public function get(): array
    {
        return self::getDirs(DC_TPL_CACHE);
    }

    public function set($action, $ns): bool
    {
        if ($action == 'empty') {
            self::delDir(DC_TPL_CACHE, $ns, false);

            return true;
        }
        if ($action == 'delete') {
            self::delDir(DC_TPL_CACHE, $ns, true);

            return true;
        }

        return false;
    }
}

class advancedCleanerVars extends advancedCleaner
{
    protected function init(): bool
    {
        $this->setProperties([
            'id'   => 'vars',
            'name' => __('Var'),
            'desc' => __('Folders from Dotclear VAR directory'),
        ]);

        $this->setActions([
            'delete' => __('delete'),
        ]);

        return true;
    }

    public function error($action): string
    {
        if ($action == 'delete') {
            return __('Failed to delete var folder');
        }

        return '';
    }

    public function official(): array
    {
        return [];
    }

    public function get(): array
    {
        return self::getDirs(DC_VAR);
    }

    public function set($action, $ns): bool
    {
        if ($action == 'delete') {
            self::delDir(DC_VAR, $ns, true);

            return true;
        }

        return false;
    }
}
