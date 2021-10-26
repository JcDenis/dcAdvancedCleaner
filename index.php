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

dcPage::checkSuper();

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
$part    = $_REQUEST['part'] ?? 'caches';
$entries = $_POST['entries'] ?? [];
$action  = $_POST['action'] ?? '';

# Combos
$combo_title = [
    'settings' => __('Settings'),
    'tables'   => __('Tables'),
    'plugins'  => __('Extensions'),
    'themes'   => __('Themes'),
    'caches'   => __('Cache'),
    'versions' => __('Versions')
];
$combo_type = [
    'settings' => ['delete_global', 'delete_local', 'delete_all'],
    'tables'   => ['empty', 'delete'],
    'plugins'  => ['empty', 'delete'],
    'themes'   => ['empty', 'delete'],
    'caches'   => ['empty', 'delete'],
    'versions' => ['delete']
];
$combo_funcs = [
    'settings' => ['dcAdvancedCleaner', 'getSettings'],
    'tables'   => ['dcAdvancedCleaner', 'getTables'],
    'plugins'  => ['dcAdvancedCleaner', 'getPlugins'],
    'themes'   => ['dcAdvancedCleaner', 'getThemes'],
    'caches'   => ['dcAdvancedCleaner', 'getCaches'],
    'versions' => ['dcAdvancedCleaner', 'getVersions']
];
$combo_actions = [
    'settings' => [
        __('delete global settings') => 'delete_global',
        __('delete blog settings')   => 'delete_local',
        __('delete all settings')   =>'delete_all'
    ],
    'tables'   => [
        __('delete') => 'delete',
        __('empty')  => 'empty'
    ],
    'plugins'  => [
        __('delete') => 'delete',
        __('empty')  => 'empty'
    ],
    'themes'   => [
        __('delete') => 'delete',
        __('empty')  => 'empty'
    ],
    'caches'   => [
        __('delete') => 'delete',
        __('empty')  => 'empty'
    ],
    'versions' => [
        __('delete') => 'delete'
    ]
];
$combo_help = [
    'settings' => __('Namespaces registered in dcSettings'),
    'tables'   => __('All database tables of Dotclear'),
    'plugins'  => __('Folders from plugins directories'),
    'themes'   => __('Folders from blog themes directory'),
    'caches'   => __('Folders from cache directory'),
    'versions' => __('Versions registered in table "version" of Dotclear')
];

# Actions
if (!empty($entries) 
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
            ['part' => $part]
        );
    }
    catch(Exception $e) {
        $core->error->add($e->getMessage());
    }
}

echo '<html><head><title>' . __('Advanced cleaner') . '</title>' .
dcPage::cssLoad(dcPage::getPF('dcAdvancedCleaner/style.css')) .
dcPage::jsLoad(dcPage::getPF('dcAdvancedCleaner/js/index.js'));

# --BEHAVIOR-- dcAdvancedCleanerAdminHeader
$core->callBehavior('dcAdvancedCleanerAdminHeader', $core);

echo '</head><body>' .
dcPage::breadcrumb([
    __('Plugins') => '',
    __('Advanced cleaner') => ''
]) . 
dcPage::notices();

# select menu list
echo
'<form method="get" action="' . $core->adminurl->get('admin.plugin.dcAdvancedCleaner') . '" id="parts_menu">' .
'<p class="anchor-nav"><label for="part" class="classic">' . __('Goto:') . ' </label>' .
form::combo('part', array_flip($combo_title), $part) . ' ' .
'<input type="submit" value="' . __('Ok') . '" />' .
form::hidden('p', 'dcAdvancedCleaner') . '</p>' .
'</form>';

if (isset($combo_funcs[$part])) {
    echo '<h3>' . $combo_title[$part] . '</h3><p>' . $combo_help[$part] . '</p>';

    $rs = call_user_func($combo_funcs[$part], $core);

    if (empty($rs)) {
        echo '<p>' . sprintf(__('There is no %s'), __(substr($part, 0, -1))) . '</p>';
    } else {
        echo
        '<form method="post" action="' . $core->adminurl->get('admin.plugin.dcAdvancedCleaner') . '" id="form-funcs">' .
        '<div class="table-outer">' .
        '<table><caption>' . sprintf(__('There are %s %s'), count($rs), __($part)) . '</caption><thead><tr>' .
        '<th colspan="2">' . __('Name') . '</th><th>' . __('Objects') . '</th>' .
        '</tr></thead><tbody>';

        $official = dcAdvancedCleaner::getOfficial($part);
        foreach($rs as $k => $v) {
            $offline = in_array($v['key'], $official);

            if ($offline && $core->blog->settings->dcAdvancedCleaner->dcAdvancedCleaner_dcproperty_hide) {
                continue;
            }
            echo 
            '<tr class="line' . ($offline ? ' offline' : '') . '">' .
            '<td class="nowrap">' .
                form::checkbox(
                    ['entries[' . $k . ']', 'entries_' . $k], 
                    html::escapeHTML($v['key'])
                ) . '</td> ' .
            '<td class="nowrap"><label for="entries_' . $k . '" class="classic">' . $v['key'] . '</label></td>' .
            '<td class="nowrap maximal">' . $v['value'] . '</td>' .
            '</tr>';
        }
        echo
        '</tbody></table></div>' .
        '<p class="field">' . __('Action on selected rows:') . ' ' .
        form::combo(['action'], $combo_actions[$part]) .
        '<input id="do-action" type="submit" value="' . __('ok') . '" />' .
        form::hidden(['p'], 'dcAdvancedCleaner') .
        form::hidden(['part'], $part) .
        $core->formNonce() . '</p>' .
        '<p class="info">' . 
        __('Beware: All actions done here are irreversible and are directly applied') . 
        '</p>' .
        '</form>';
    }
}
if ($core->blog->settings->dcAdvancedCleaner->dcAdvancedCleaner_dcproperty_hide) {
    echo '<p class="info">' . 
    __('Default values of Dotclear are hidden. You can change this in settings') . 
    '</p>';
}

dcPage::helpBlock('dcAdvancedCleaner');

echo '</body></html>';