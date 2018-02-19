<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcAdvancedCleaner, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2018 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

if (!$core->auth->isSuperAdmin()){return;}

$page_title = __('Advanced cleaner');

# Lists
function drawDcAdvancedCleanerLists($core,$type)
{
	$combo_funcs = array(
		'settings' => array('dcAdvancedCleaner','getSettings'),
		'tables' => array('dcAdvancedCleaner','getTables'),
		'plugins' => array('dcAdvancedCleaner','getPlugins'),
		'themes' => array('dcAdvancedCleaner','getThemes'),
		'caches' => array('dcAdvancedCleaner','getCaches'),
		'versions' => array('dcAdvancedCleaner','getVersions')
	);
	$combo_actions = array(
		'settings' => array(
			__('delete global settings') => 'delete_global',
			__('delete blog settings') => 'delete_local',
			__('delete all settings') =>'delete_all'
		),
		'tables' => array(
			__('delete') => 'delete',
			__('empty') => 'empty'
		),
		'plugins' => array(
			__('delete') => 'delete',
			__('empty') => 'empty'
		),
		'themes' => array(
			__('delete') => 'delete',
			__('empty') => 'empty'
		),
		'caches' => array(
			__('delete') => 'delete',
			__('empty') => 'empty'
		),
		'versions' => array(
			__('delete') => 'delete'
		)
	);
	$combo_help = array(
		'settings' => __('Namespaces registered in dcSettings'),
		'tables' => __('All database tables of Dotclear'),
		'plugins' => __('Folders from plugins directories'),
		'themes' => __('Folders from blog themes directory'),
		'caches' => __('Folders from cache directory'),
		'versions' => __('Versions registered in table "version" of Dotclear')
	);

	if (!isset($combo_funcs[$type])) return '';

	$rs = call_user_func($combo_funcs[$type],$core);

	echo 
	'<div class="listDcAdvancedCleaner">'.
	'<p class="form-note">'.$combo_help[$type].'</p>';
	
	if (empty($rs)) {
		echo 
		'<p>'.sprintf(__('There is no %s'),__(substr($type,0,-1))).'</p>';
	} else {

		echo
		'<p>'.sprintf(__('There are %s %s'),count($rs),__($type)).'</p>'.
		'<form method="post" action="plugin.php?p=dcAdvancedCleaner&amp;tab=lists&amp;part='.$type.'">'.
		'<table><thead><tr>'.
		'<th>'.__('Name').'</th><th>'.__('Objects').'</th>'.
		'</tr></thead><tbody>';

		foreach($rs as $k => $v)
		{
			$offline = in_array($v['key'],dcAdvancedCleaner::$dotclear[$type]);

			if ($core->blog->settings->dcAdvancedCleaner->dcAdvancedCleaner_dcproperty_hide && $offline) continue;

			echo 
			'<tr class="line'.
			($offline ? ' offline' : '').
			'">'.
			'<td class="nowrap"><label class="classic">'.
			form::checkbox(array('entries['.$k.']'),html::escapeHTML($v['key'])).' '.$v['key'].'</label></td>'.
			'<td class="nowrap">'.$v['value'].'</td>'.
			'</tr>';
		}

		echo
		'</tbody></table>'.
		'<p>'.__('Action on selected rows:').'<br />'.
		form::combo(array('action'),$combo_actions[$type]).
		'<input type="submit" value="'.__('ok').'" />'.
		form::hidden(array('p'),'dcAdvancedCleaner').
		form::hidden(array('tab'),'lists').
		form::hidden(array('part'),$type).
		form::hidden(array('type'),$type).
		$core->formNonce().'</p>'.
		'</form>';
	}
	echo 
	'<div>';
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
$msg = isset($_GET['msg']) ? true : false;
$tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'dcac';
$part = isset($_REQUEST['part']) ? $_REQUEST['part'] : 'caches';
$entries = isset($_POST['entries']) ? $_POST['entries'] : '';
$action = isset($_POST['action']) ? $_POST['action'] : '';
$type = isset($_POST['type']) ? $_POST['type'] : '';
$s = $core->blog->settings->dcAdvancedCleaner;

# Combos
$combo_title = array(
	'settings' => __('Settings'),
	'tables' => __('Tables'),
	'plugins' => __('Extensions'),
	'themes' => __('Themes'),
	'caches' => __('Cache'),
	'versions' => __('Versions')
);
	
$combo_type = array(
	'settings' => array('delete_global','delete_local','delete_all'),
	'tables' => array('empty','delete'),
	'plugins' => array('empty','delete'),
	'themes' => array('empty','delete'),
	'caches' => array('empty','delete'),
	'versions' => array('delete')
);

# This plugin settings
if ($tab == 'dcac' && $action == 'dcadvancedcleaner_settings')
{
	try {
		$s->put('dcAdvancedCleaner_behavior_active',isset($_POST['dcadvancedcleaner_behavior_active']),'boolean');
		$s->put('dcAdvancedCleaner_dcproperty_hide',isset($_POST['dcadvancedcleaner_dcproperty_hide']),'boolean');

		http::redirect($p_url.'&tab=dcac&part=dcac&part=&msg=done');
	}
	catch(Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Actions
if ($tab == 'lists' && !empty($entries) 
 && isset($combo_type[$type]) 
 && in_array($action,$combo_type[$type])) {

	try {
		foreach($entries as $v) {
			dcAdvancedCleaner::execute($core,$type,$action,$v);
		}

		http::redirect($p_url.'&tab=lists&part='.$part.'&msg=done');
	}
	catch(Exception $e) {
		$core->error->add($e->getMessage());
	}
}

echo '
<html><head>
<title>'.$page_title.'</title>
<link rel="stylesheet" type="text/css" href="index.php?pf=dcAdvancedCleaner/style.css" />'.
dcPage::jsToolBar().
dcPage::jsPageTabs($tab).'
</style>';

# --BEHAVIOR-- dcAdvancedCleanerAdminHeader
$core->callBehavior('dcAdvancedCleanerAdminHeader',$core,$p_url,$tab);

echo '
</head><body>'.
	dcPage::breadcrumb(
    array(
    html::escapeHTML($core->blog->name) => '',
    '<span class="page-title">'.$page_title.'</span>' => ''
    ));
echo
'<p class="error">'.__('Beware: All actions done here are irreversible and are directly applied').'</p>';

# Information message
if (!empty($msg)) {
  dcPage::success(__('Action successfully done'));
}

echo '<div class="multi-part" id="lists" title="'.__('Records and folders').'">'.
'<p>';
foreach($combo_title as $k => $v)
{
	echo '<a class="button" href="'.$p_url.'&amp;tab=lists&part='.$k.'">'.$v.'</a> ';
}
echo '</p>';
	
# Load "part" page
if (isset($combo_title[$part]))
{
	echo '<fieldset><legend>'.$combo_title[$part].'</legend>';
	drawDcAdvancedCleanerLists($core,$part);
	echo '</fieldset>';
}
if ($s->dcAdvancedCleaner_dcproperty_hide)
{
	echo '<p class="info">'.__('Default values of Dotclear are hidden. You can change this in settings tab').'</p>';
}
echo '</div>';

# --BEHAVIOR-- dcAdvancedCleanerAdminTabs
$core->callBehavior('dcAdvancedCleanerAdminTabs',$core,$p_url);

echo '
<div class="multi-part" id="dcac" title="'.__('This plugin settings').'">
<fieldset><legend>'.__('This plugin settings').'</legend>
<form method="post" action="'.$p_url.'&amp;tab=dcac&part=">
<p><label class="classic" for="dcadvancedcleaner_behavior_active">'.
form::checkbox('dcadvancedcleaner_behavior_active',1,
$s->dcAdvancedCleaner_behavior_active).
__('Activate behaviors').'</label></p>
<p class="form-note">'.__('Enable actions set in _uninstall.php files.').'</p>
<p><label class="classic" for="dcadvancedcleaner_dcproperty_hide">'.
form::checkbox('dcadvancedcleaner_dcproperty_hide',1,
$s->dcAdvancedCleaner_dcproperty_hide).
__('Hide Dotclear default properties in actions tabs').'
</label></p>
<p class="form-note">'.__('Prevent from deleting Dotclear important properties.').'</p>
<p><input type="submit" name="submit" value="'.__('Save').'" />'.
form::hidden(array('p'),'dcAdvancedCleaner').
form::hidden(array('tab'),'dcac').
form::hidden(array('part'),'').
form::hidden(array('action'),'dcadvancedcleaner_settings').
$core->formNonce().'</p>
</form>
</fieldset>
</div>';

dcPage::helpBlock('dcAdvancedCleaner');
echo '
<hr class="clear"/>
<p class="right">
<a class="button" href="'.$p_url.'&amp;part=dcac">'.__('settings').'</a> - 
dcAdvancedCleaner - '.$core->plugins->moduleInfo('dcAdvancedCleaner','version').'&nbsp;
<img alt="dcMiniUrl" src="index.php?pf=dcAdvancedCleaner/icon.png" />
</p>
</body></html>';
?>