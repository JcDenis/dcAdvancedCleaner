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

if (!defined('DC_RC_PATH')) return;

global $__autoload, $core;
$core->blog->settings->addNamespace('dcAdvancedCleaner');

# Main class
$__autoload['dcAdvancedCleaner'] = 
	dirname(__FILE__).'/inc/class.dc.advanced.cleaner.php';

# Behaviors class
$__autoload['behaviorsDcAdvancedCleaner'] = 
	dirname(__FILE__).'/inc/lib.dc.advanced.cleaner.behaviors.php';

# Unsintaller class
$__autoload['dcUninstaller'] = 
	dirname(__FILE__).'/inc/class.dc.uninstaller.php';

# Add tab on plugin admin page
$core->addBehavior('pluginsToolsTabs',
	array('behaviorsDcAdvancedCleaner','pluginsToolsTabs'));

# Action on plugin deletion
$core->addBehavior('pluginsBeforeDelete',
	array('behaviorsDcAdvancedCleaner','pluginsBeforeDelete'));

# Action on theme deletion
$core->addBehavior('themeBeforeDelete',
	array('behaviorsDcAdvancedCleaner','themeBeforeDelete'));

# Tabs of dcAvdancedCleaner admin page
$core->addBehavior('dcAdvancedCleanerAdminTabs',
	array('behaviorsDcAdvancedCleaner','dcAdvancedCleanerAdminTabs'));

# Add dcac events on plugin activityReport
if (defined('ACTIVITY_REPORT'))
{
	require_once dirname(__FILE__).'/inc/lib.dc.advanced.cleaner.activityreport.php';
}