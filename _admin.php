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
if (!defined('DC_CONTEXT_ADMIN')) {
    return null;
}

dcCore::app()->blog->settings->addNamespace('dcAdvancedCleaner');

dcCore::app()->menu[dcAdmin::MENU_PLUGINS]->addItem(
    __('Advanced cleaner'),
    dcCore::app()->adminurl->get('admin.plugin.dcAdvancedCleaner'),
    dcPage::getPF('dcAdvancedCleaner/icon.svg'),
    preg_match(
        '/' . preg_quote(dcCore::app()->adminurl->get('admin.plugin.dcAdvancedCleaner')) . '(&.*)?$/',
        $_SERVER['REQUEST_URI']
    ),
    dcCore::app()->auth->isSuperAdmin()
);

dcCore::app()->addBehavior('adminDashboardFavoritesV2', function ($favs) {
    $favs->register('dcAdvancedCleaner', [
        'title'       => __('Advanced cleaner'),
        'url'         => dcCore::app()->adminurl->get('admin.plugin.dcAdvancedCleaner'),
        'small-icon'  => dcPage::getPF('dcAdvancedCleaner/icon.png'),
        'large-icon'  => dcPage::getPF('dcAdvancedCleaner/icon-big.png'),
        //'permissions' => dcCore::app()->auth->isSuperAdmin(),
    ]);
});

dcCore::app()->addBehavior('pluginsToolsTabsV2', function () {
    $path = DC_PLUGINS_ROOT;
    $redir = dcCore::app()->adminurl->get('admin.plugins', [], '#uninstaller');
    $title = '';

    if (!dcCore::app()->blog->settings->dcAdvancedCleaner->dcAdvancedCleaner_behavior_active) {
        return null;
    }
    $title = empty($title) ? __('Advanced uninstall') : $title;

    $uninstaller = new dcUninstaller();
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
                    'ns'     => $action['ns'],
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
    dcCore::app()->formNonce() .
    form::hidden(['path'], $path) .
    form::hidden(['redir'], $redir) .
    form::hidden(['action'], 'uninstall') .
    '<input type="submit" name="submit" value="' . __('Perform selected actions') . '" /> ' .
    '</p>' .
    '</form>';

    echo '</div>';
});

dcCore::app()->addBehavior('adminModulesListDoActions', function ($list, $modules, $type) {
    if (!dcCore::app()->blog->settings->dcAdvancedCleaner->dcAdvancedCleaner_behavior_active) {
        return null;
    }

    if (!isset($_POST['action']) || $_POST['action'] != 'uninstall'
                                 || (empty($_POST['extras']) && empty($_POST['actions']))
    ) {
        return null;
    }

    $uninstaller = new dcUninstaller();
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
                    call_user_func($extra, $module_id);
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
        dcAdminNotices::addSuccessNotice(__('Action successfuly excecuted'));
        http::redirect($_POST['redir']);
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
});

dcCore::app()->addBehavior('pluginsBeforeDelete', function ($plugin) {
    dcAdvancedCleanerModuleBeforeDelete($plugin);
});

dcCore::app()->addBehavior('themeBeforeDelete', function ($theme) {
    dcAdvancedCleanerModuleBeforeDelete($theme);
});

function dcAdvancedCleanerModuleBeforeDelete($module)
{
    $done = false;

    if (!dcCore::app()->blog->settings->dcAdvancedCleaner->dcAdvancedCleaner_behavior_active) {
        return null;
    }
    $uninstaller = new dcUninstaller();
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
        if ('theme' == $module['type']) {
            dcCore::app()->adminurl->redirect('admin.blog.theme', ['del' => 1]);
        } else {
            dcCore::app()->adminurl->redirect('admin.plugins', ['removed' => 1]);
        }
    }
}
