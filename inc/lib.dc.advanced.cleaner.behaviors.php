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

class behaviorsDcAdvancedCleaner
{
    public static function adminDashboardFavorites(dcCore $core, $favs)
    {
        $favs->register('dcAdvancedCleaner', [
            'title'       => __('Advanced cleaner'),
            'url'         => $core->adminurl->get('admin.plugin.dcAdvancedCleaner'),
            'small-icon'  => dcPage::getPF('dcAdvancedCleaner/icon.png'),
            'large-icon'  => dcPage::getPF('dcAdvancedCleaner/icon-big.png'),
            'permissions' => $core->auth->isSuperAdmin()
        ]);
    }

    public static function pluginsBeforeDelete($plugin)
    {
        self::moduleBeforeDelete($plugin, 'plugins.php?removed=1');
    }

    public static function themeBeforeDelete($theme)
    {
        self::moduleBeforeDelete($theme, 'blog_theme.php?del=1');
    }

    // Generic module before delete
    public static function moduleBeforeDelete($module, $redir)
    {
        global $core;
        $done = false;

        if (!$core->blog->settings->dcAdvancedCleaner->dcAdvancedCleaner_behavior_active) {
            return null;
        }
        $uninstaller = new dcUninstaller($core);
        $uninstaller->loadModule($module['root']);

        $m_callbacks = $uninstaller->getDirectCallbacks($module['id']);
        $m_actions   = $uninstaller->getDirectActions($module['id']);

        foreach ($m_callbacks as $k => $callback) {
            if (!isset($callback['func']) || !is_callable($callback['func'])) {
                continue;
            }
            call_user_func($callback['func'], $module);
            $done = true;
        }
        foreach ($m_actions as $type => $actions) {
            foreach ($actions as $v) {
                $uninstaller->execute($type, $v['action'], $v['ns']);
                $done = true;
            }
        }
        if ($done) {
            http::redirect($redir);
        }
    }

    public static function pluginsToolsTabs($core)
    {
        self::modulesTabs($core, DC_PLUGINS_ROOT, $core->adminurl->get('admin.plugins') . '#uninstaller');
    }

    public static function modulesTabs($core, $path, $redir, $title = '')
    {
        if (!$core->blog->settings->dcAdvancedCleaner->dcAdvancedCleaner_behavior_active) {
            return null;
        }
        $title = empty($title) ? __('Advanced uninstall') : $title;

        $uninstaller = new dcUninstaller($core);
        $uninstaller->loadModules($path);
        $modules = $uninstaller->getModules();
        $props   = $uninstaller->getAllowedActions();

        echo '<div class="multi-part" id="uninstaller" title="' . __($title) . '"><h3>' . __($title) . '</h3>';

        if (!count($modules)) {
            echo '<p>' . __('There is no module with uninstall features') . '</p></div>';

            return null;
        }

        echo
        '<p>' . __('List of modules with advanced uninstall features') . '</p>' .
        '<form method="post" action="' . $redir . '">' .
        '<table class="clear"><tr>' .
        '<th colspan="2">' . __('module') . '</th>';

        foreach ($props as $pro_id => $prop) {
            echo '<th>' . __($pro_id) . '</th>';
        }

        echo '<th>' . __('other') . '</th>' . '</tr>';

        $i = 0;
        foreach ($modules as $module_id => $module) {
            echo
            '<tr class="line">' .
            '<td class="nowrap">' . $module_id . '</td>' .
            '<td class="nowrap">' . $module['version'] . '</td>';

            $actions = $uninstaller->getUserActions($module_id);

            foreach ($props as $prop_id => $prop) {
                echo '<td class="nowrap">';

                if (!isset($actions[$prop_id])) {
                    echo '--</td>';

                    continue;
                }

                $j = 0;
                foreach ($actions[$prop_id] as $action_id => $action) {
                    if (!isset($props[$prop_id][$action['action']])) {
                        continue;
                    }
                    $ret = base64_encode(serialize([
                        'type'   => $prop_id,
                        'action' => $action['action'],
                        'ns'     => $action['ns']
                    ]));

                    echo '<label class="classic">' .
                    form::checkbox(['actions[' . $module_id . '][' . $j . ']'], $ret) .
                    ' ' . $action['desc'] . '</label><br />';

                    $j++;
                }
                echo '</td>';
            }

            echo '<td class="nowrap">';

            $callbacks = $uninstaller->getUserCallbacks($module_id);

            if (empty($callbacks)) {
                echo '--';
            }

            $k = 0;
            foreach ($callbacks as $callback_id => $callback) {
                $ret = base64_encode(serialize($callback['func']));

                echo '<label class="classic">' .
                form::checkbox(['extras[' . $module_id . '][' . $k . ']'], $ret) .
                ' ' . $callback['desc'] . '</label><br />';
            }

            echo '</td></tr>';
        }
        echo
        '</table>' .
        '<p>' .
        $core->formNonce() .
        form::hidden(['path'], $path) .
        form::hidden(['redir'], $redir) .
        form::hidden(['action'], 'uninstall') .
        '<input type="submit" name="submit" value="' . __('Perform selected actions') . '" /> ' .
        '</p>' .
        '</form>';

        echo '</div>';
    }

    public static function adminModulesListDoActions($list, $modules, $type)
    {
        if (!$list->core->blog->settings->dcAdvancedCleaner->dcAdvancedCleaner_behavior_active) {
            return null;
        }

        if (!isset($_POST['action']) || $_POST['action'] != 'uninstall'
            || (empty($_POST['extras']) && empty($_POST['actions']))
        ) {
            return null;
        }

        $uninstaller = new dcUninstaller($list->core);
        $uninstaller->loadModules($_POST['path']);
        $modules = $uninstaller->getModules();
        $props   = $uninstaller->getAllowedActions();

        try {
            // Extras
            if (!empty($_POST['extras'])) {
                foreach ($_POST['extras'] as $module_id => $extras) {
                    foreach ($extras as $k => $sentence) {
                        $extra = @unserialize(@base64_decode($sentence));

                        if (!$extra || !is_callable($extra)) {
                            continue;
                        }
                        call_user_func($extra, $modul_id);
                    }
                }
            }
            // Actions
            if (!empty($_POST['actions'])) {
                foreach ($_POST['actions'] as $module_id => $actions) {
                    foreach ($actions as $k => $sentence) {
                        $action = @unserialize(@base64_decode($sentence));

                        if (!$action
                            || !isset($action['type'])
                            || !isset($action['action'])
                            || !isset($action['ns'])
                        ) {
                            continue;
                        }
                        $uninstaller->execute($action['type'], $action['action'], $action['ns']);
                    }
                }
            }
            dcPage::addSuccessNotice(__('Action successfuly excecuted'));
            http::redirect($_POST['redir']);
        } catch (Exception $e) {
            $list->core->error->add($e->getMessage());
        }
    }
}
