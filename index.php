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

$ac = new dcAdvancedCleaner();

$cleaner     = false;
$select_menu = [];
foreach ($ac->get() as $k) {
    $select_menu[$k->name] = $k->id;
    if ($k->id == $_REQUEST['part']) {
        $cleaner = $k;
    }
}
if (!$cleaner) {
    if (!($cleaner = $ac->get('caches', true))) {
        return false;
    }
}

# Actions
if (!empty($_POST['entries']) && !empty($_POST['action'])) {
    try {
        foreach ($_POST['entries'] as $ns) {
            $ac->set($cleaner->id, $_POST['action'], $ns);
        }
        dcAdminNotices::addSuccessNotice(__('Action successfuly excecuted'));
        dcCore::app()->adminurl->redirect(
            'admin.plugin.dcAdvancedCleaner',
            ['part' => $cleaner->id]
        );
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
}

# Display
echo '<html><head><title>' . __('Advanced cleaner') . '</title>' .
dcPage::cssLoad(dcPage::getPF('dcAdvancedCleaner/style.css')) .
dcPage::jsLoad(dcPage::getPF('dcAdvancedCleaner/js/index.js'));

# --BEHAVIOR-- dcAdvancedCleanerAdminHeader
dcCore::app()->callBehavior('dcAdvancedCleanerAdminHeader');

echo '</head><body>' .
dcPage::breadcrumb([
    __('Plugins')          => '',
    __('Advanced cleaner') => '',
]) .
dcPage::notices() .

'<form method="get" action="' . dcCore::app()->adminurl->get('admin.plugin.dcAdvancedCleaner') . '" id="parts_menu">' .
'<p class="anchor-nav"><label for="part" class="classic">' . __('Goto:') . ' </label>' .
form::combo('part', $select_menu, $cleaner->id) . ' ' .
'<input type="submit" value="' . __('Ok') . '" />' .
form::hidden('p', 'dcAdvancedCleaner') . '</p>' .
'</form>' .

'<h3>' . $cleaner->name . '</h3><p>' . $cleaner->desc . '</p>';

$rs = $cleaner->get();

if (empty($rs)) {
    echo '<p>' . __('There is nothing to display') . '</p>';
} else {
    echo
    '<form method="post" action="' . dcCore::app()->adminurl->get('admin.plugin.dcAdvancedCleaner') . '" id="form-funcs">' .
    '<div class="table-outer">' .
    '<table><caption>' . sprintf(__('There are %s %s'), count($rs), __($cleaner->id)) . '</caption><thead><tr>' .
    '<th colspan="2">' . __('Name') . '</th><th>' . __('Objects') . '</th>' .
    '</tr></thead><tbody>';

    foreach ($rs as $k => $v) {
        $offline = in_array($v['key'], $cleaner->official());

        if ($offline && dcCore::app()->blog->settings->dcAdvancedCleaner->dcAdvancedCleaner_dcproperty_hide) {
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
    form::combo(['action'], array_flip($cleaner->getActions())) .
    '<input id="do-action" type="submit" value="' . __('ok') . '" />' .
    form::hidden(['p'], 'dcAdvancedCleaner') .
    form::hidden(['part'], $cleaner->id) .
    dcCore::app()->formNonce() . '</p>' .
    '<p class="info">' .
    __('Beware: All actions done here are irreversible and are directly applied') .
    '</p>' .
    '</form>';
}

if (dcCore::app()->blog->settings->dcAdvancedCleaner->dcAdvancedCleaner_dcproperty_hide) {
    echo '<p class="info">' .
    __('Default values of Dotclear are hidden. You can change this in settings') .
    '</p>';
}

dcPage::helpBlock('dcAdvancedCleaner');

echo '</body></html>';
