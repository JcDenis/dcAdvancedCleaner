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

class behaviorsDcAdvancedCleaner
{
    public static function pluginsBeforeDelete($plugin)
    {
        self::moduleBeforeDelete($plugin,'plugins.php?removed=1');
    }

    public static function themeBeforeDelete($theme)
    {
        self::moduleBeforeDelete($theme,'blog_theme.php?del=1');
    }

    # Generic module before delete
    public static function moduleBeforeDelete($module,$redir)
    {
        global $core;
        $done = false;

        if (!$core->blog->settings->dcAdvancedCleaner->dcAdvancedCleaner_behavior_active) return;

        $uninstaller = new dcUninstaller($core);
        $uninstaller->loadModule($module['root']);

        $m_callbacks = $uninstaller->getDirectCallbacks($module['id']);
        $m_actions = $uninstaller->getDirectActions($module['id']);

        foreach($m_callbacks as $k => $callback)
        {
            if (!isset($callback['func']) || !is_callable($callback['func'])) continue;

            call_user_func($callback['func'],$module);
            $done = true;
        }

        foreach($m_actions as $type => $actions)
        {
            foreach($actions as $v)
            {
                $uninstaller->execute($type,$v['action'],$v['ns']);
                $done = true;
            }
        }

        if ($done) {
            http::redirect($redir);
        }
    }

    public static function dcAdvancedCleanerAdminTabs($core,$p_url)
    {
        self::modulesTabs($core,DC_PLUGINS_ROOT,$p_url.'&tab=uninstaller');
    }

    public static function pluginsToolsTabs($core)
    {
        self::modulesTabs($core,DC_PLUGINS_ROOT,'plugins.php?tab=uninstaller');
    }

    # Generic module tabs
    public static function modulesTabs($core,$path,$redir,$title='')
    {
        if (!$core->blog->settings->dcAdvancedCleaner->dcAdvancedCleaner_behavior_active) return;

        $err = '';
        $title = empty($title) ? __('Advanced uninstall') : $title;

        $uninstaller = new dcUninstaller($core);
        $uninstaller->loadModules($path);
        $modules = $uninstaller->getModules();
        $props = $uninstaller->getAllowedProperties();

        # Execute
        if (isset($_POST['action']) && $_POST['action'] == 'uninstall'
        && (!empty($_POST['extras']) || !empty($_POST['actions']))) {

            try {
                # Extras
                if (!empty($_POST['extras'])) {
                    foreach($_POST['extras'] as $module_id => $extras)
                    {
                        foreach($extras as $k => $sentence)
                        {
                            $extra = @unserialize(@base64_decode($sentence));

                            if (!$extra || !is_callable($extra)) continue;

                            call_user_func($extra,$modul_id);
                        }
                    }
                }
                # Actions
                if (!empty($_POST['actions'])) {
                    foreach($_POST['actions'] as $module_id => $actions)
                    {
                        foreach($actions as $k => $sentence)
                        {
                            $action = @unserialize(@base64_decode($sentence));

                            if (!$action 
                             || !isset($action['type']) 
                             || !isset($action['action']) 
                             || !isset($action['ns'])) continue;

                            $uninstaller->execute($action['type'],$action['action'],$action['ns']);
                        }
                    }
                }
                http::redirect($redir.'&msg=1');
            }
            catch(Exception $e) {
                $err = $e->getMessage();
            }
        }

        echo 
        '<div class="multi-part" id="uninstaller" title="'.__($title).'">';

        if($err)
            echo '<p class="error">'.$err.'</p>';

        if(!count($modules)) {
            echo '<p>'.__('There is no module with uninstall features').'</p></div>';
            return;
        }

        echo
        '<p>'.__('List of modules with advanced uninstall features').'</p>'.
        '<form method="post" action="'.$redir.'">'.
        '<table class="clear"><tr>'.
        '<th>'.__('id').'</th>'.
        '<th>'.__('n°').'</th>';

        foreach($props as $pro_id => $prop) {
            echo '<th>'.__($pro_id).'</th>';
        }

        echo 
        '<th>'.__('extra').'</th>'.
        '</tr>';

        $i = 0;
        foreach($modules as $module_id => $module) {

            echo
            '<tr class="line">'.
            '<td class="nowrap">'.$module_id.'</td>'.
            '<td class="nowrap">'.$module['version'].'</td>';

            $actions = $uninstaller->getUserActions($module_id);

            foreach($props as $prop_id => $prop) {
                echo '<td class="nowrap">';

                if (!isset($actions[$prop_id])) {
                    echo '--</td>';
                    continue;
                }

                $j = 0;
                foreach($actions[$prop_id] as $action_id => $action) {

                    if (!isset($props[$prop_id][$action['action']])) continue;

                    $ret = base64_encode(serialize(array(
                        'type' => $prop_id,
                        'action'=>$action['action'],
                        'ns'=>$action['ns']
                    )));

                    echo '<label class="classic">'.
                    form::checkbox(array('actions['.$module_id.']['.$j.']'),$ret).
                    ' '.$action['desc'].'</label><br />';

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
            foreach($callbacks as $callback_id => $callback) {

                $ret = base64_encode(serialize($callback['func']));

                echo '<label class="classic">'.
                form::checkbox(array('extras['.$module_id.']['.$k.']'),$ret).
                ' '.$callback['desc'].'</label><br />';
            }

            echo '</td></tr>';
        }
        echo 
        '</table>'.
        '<p>'.
        $core->formNonce().
        form::hidden(array('redir'),$redir).
        form::hidden(array('action'),'uninstall').
        '<input type="submit" name="submit" value="'.__('Perform selected actions').'" /> '.
        '</p>'.
        '</form>'.
        '</div>';
    }
}