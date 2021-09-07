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
if (!$core->auth->isSuperAdmin()) {
    return null;
}

# Localized l10n
__('Settings'); __('settings'); __('setting');
__('Tables'); __('tables'); __('table');
__('Plugins'); __('plugins'); __('plugin');
__('Themes'); __('themes'); __('theme');
__('Caches'); __('caches'); __('cache');
__('Versions'); __('versions'); __('version');
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

# vars
$tab = $_REQUEST['tab'] ?? 'lists';
$part = $_REQUEST['part'] ?? 'caches';
$entries = $_POST['entries'] ?? [];
$action = $_POST['action'] ?? '';
$s = $core->blog->settings->dcAdvancedCleaner;
$p_url = $core->adminurl->get('admin.plugin.dcAdvancedCleaner', ['tab' => $tab, 'part' => $part]);

# Combos
$combo_title = [
    'settings' => __('Settings'),
    'tables' => __('Tables'),
    'plugins' => __('Extensions'),
    'themes' => __('Themes'),
    'caches' => __('Cache'),
    'versions' => __('Versions')
];
$combo_type = [
    'settings' => ['delete_global', 'delete_local', 'delete_all'],
    'tables' => ['empty', 'delete'],
    'plugins' => ['empty', 'delete'],
    'themes' => ['empty', 'delete'],
    'caches' => ['empty', 'delete'],
    'versions' => ['delete']
];
$combo_funcs = [
    'settings' => ['dcAdvancedCleaner', 'getSettings'],
    'tables' => ['dcAdvancedCleaner', 'getTables'],
    'plugins' => ['dcAdvancedCleaner', 'getPlugins'],
    'themes' => ['dcAdvancedCleaner', 'getThemes'],
    'caches' => ['dcAdvancedCleaner', 'getCaches'],
    'versions' => ['dcAdvancedCleaner', 'getVersions']
];
$combo_actions = [
    'settings' => [
        __('delete global settings') => 'delete_global',
        __('delete blog settings') => 'delete_local',
        __('delete all settings') =>'delete_all'
    ],
    'tables' => [
        __('delete') => 'delete',
        __('empty') => 'empty'
    ],
    'plugins' => [
        __('delete') => 'delete',
        __('empty') => 'empty'
    ],
    'themes' => [
        __('delete') => 'delete',
        __('empty') => 'empty'
    ],
    'caches' => [
        __('delete') => 'delete',
        __('empty') => 'empty'
    ],
    'versions' => [
        __('delete') => 'delete'
    ]
];
$combo_help = [
    'settings' => __('Namespaces registered in dcSettings'),
    'tables' => __('All database tables of Dotclear'),
    'plugins' => __('Folders from plugins directories'),
    'themes' => __('Folders from blog themes directory'),
    'caches' => __('Folders from cache directory'),
    'versions' => __('Versions registered in table "version" of Dotclear')
];

# Actions
if ($tab == 'lists' 
    && !empty($entries) 
    && isset($combo_type[$part]) 
    && in_array($action, $combo_type[$part])
) {
    try {
        foreach($entries as $v) {
            dcAdvancedCleaner::execute($core, $part, $action, $v);
        }
        dcPage::addSuccessNotice(__('Action successfuly excecuted'));
        $core->adminurl->redirect(
            'admin.plugin.dcAdvancedCleaner', 
            ['tab' => 'lists', 'part' => $part]
        );
    }
    catch(Exception $e) {
        $core->error->add($e->getMessage());
    }
}

echo '<html><head><title>' . __('Advanced cleaner') . '</title>' .
dcPage::cssLoad('index.php?pf=dcAdvancedCleaner/style.css') .
dcPage::jsPageTabs($tab);

# --BEHAVIOR-- dcAdvancedCleanerAdminHeader
$core->callBehavior('dcAdvancedCleanerAdminHeader', $core, $p_url, $tab);

echo '</head><body>' .
dcPage::breadcrumb([
    html::escapeHTML($core->blog->name) => '',
    '<span class="page-title">' . __('Advanced cleaner') . '</span>' => ''
]) . 
dcPage::notices() . '
<div class="multi-part" id="lists" title="' . __('Records and folders') . '">
<h3>'. __('Records and folders') .'</h3><p>';

foreach($combo_title as $k => $v) {
    echo sprintf(
        '<a class="button" href="%s">%s</a>',
        $core->adminurl->get(
            'admin.plugin.dcAdvancedCleaner', 
            ['tab' => 'lists', 'part' => $k]
        ),
        $v
    );
}
echo '</p>';

if (isset($combo_funcs[$part])) {
    echo '<h4>' . $combo_title[$part] . '</h4><p>' . $combo_help[$part] . '</p>';

    $rs = call_user_func($combo_funcs[$part], $core);

    if (empty($rs)) {
        echo '<p>' . sprintf(__('There is no %s'), __(substr($part, 0, -1))) . '</p>';
    } else {
        echo
        '<p>' . sprintf(__('There are %s %s'), count($rs), __($part)) . '</p>' .
        '<form method="post" action="' . $p_url . '">' .
        '<div class="table-outer">' .
        '<table><caption class="hidden">' . __($part) . '</caption><thead><tr>' .
        '<th>' . __('Name') . '</th><th>' . __('Objects') . '</th>' .
        '</tr></thead><tbody>';

        $official = dcAdvancedCleaner::getOfficial($part);
        foreach($rs as $k => $v) {
            $offline = in_array($v['key'], $official);

            if ($s->dcAdvancedCleaner_dcproperty_hide && $offline) {
                continue;
            }
            echo 
            '<tr class="line' .
            ($offline ? ' offline' : '') .
            '">' .
            '<td class="nowrap"><label class="classic">' .
            form::checkbox(
                ['entries[' . $k . ']'], 
                html::escapeHTML($v['key'])
            ) . ' ' . $v['key'] . '</label></td>' .
            '<td class="nowrap">' . $v['value'] . '</td>' .
            '</tr>';
        }
        echo
        '</tbody></table></div>' .
        '<p class="field">' . __('Action on selected rows:') . ' ' .
        form::combo(['action'], $combo_actions[$part]) .
        '<input type="submit" value="' . __('Save') . '" />' .
        form::hidden(['p'], 'dcAdvancedCleaner') .
        form::hidden(['tab'], 'lists') .
        form::hidden(['part'], $part) .
        $core->formNonce() . '</p>' .
        '<p class="info">' . 
        __('Beware: All actions done here are irreversible and are directly applied') . 
        '</p>' .
        '</form>';
    }
}
if ($s->dcAdvancedCleaner_dcproperty_hide) {
    echo '<p class="info">' . 
    __('Default values of Dotclear are hidden. You can change this in settings tab') . 
    '</p>';
}

echo '</div>';

# --BEHAVIOR-- dcAdvancedCleanerAdminTabs
$core->callBehavior('dcAdvancedCleanerAdminTabs', $core);

dcPage::helpBlock('dcAdvancedCleaner');

echo '</body></html>';