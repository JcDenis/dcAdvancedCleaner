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

$_menu['Plugins']->addItem(
	__('Advanced cleaner'),
	'plugin.php?p=dcAdvancedCleaner',
	'index.php?pf=dcAdvancedCleaner/icon.png',
	preg_match('/plugin.php\?p=dcAdvancedCleaner(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->isSuperAdmin()
);

$core->addBehavior('adminDashboardFavorites','dcAdvancedCleanerDashboardFavorites');

function dcAdvancedCleanerDashboardFavorites($core,$favs)
{
	$favs->register('dcAdvancedCleaner', array(
		'title' => __('Advanced cleaner'),
		'url' => 'plugin.php?p=dcAdvancedCleaner',
		'small-icon' => 'index.php?pf=dcAdvancedCleaner/icon.png',
		'large-icon' => 'index.php?pf=dcAdvancedCleaner/icon-big.png',
		'permissions' => 'usage,contentadmin'
	));
}