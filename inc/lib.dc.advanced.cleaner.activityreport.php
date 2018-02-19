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

if (!defined('DC_RC_PATH')){return;}

# This file is used with plugin activityReport
$core->activityReport->addGroup('dcadvancedcleaner',__('Plugin dcAdvancedCleaner'));

# from BEHAVIOR dcAdvancedCleanerBeforeAction 
# in dcAdvancedCleaner/inc/class.dc.advanced.cleaner.php
$core->activityReport->addAction(
	'dcadvancedcleaner',
	'maintenance',
	__('Maintenance'),
	__('New action from dcAdvancedCleaner has been made with type="%s", action="%s", ns="%s".'),
	'dcAdvancedCleanerBeforeAction',
	array('dcAdvancedCleanerActivityReportBehaviors','maintenance')
);

class dcAdvancedCleanerActivityReportBehaviors
{
	public static function maintenance($type,$action,$ns)
	{
		$logs = array($type,$action,$ns);

		$GLOBALS['core']->activityReport->addLog('dcadvancedcleaner','maintenance',$logs);
	}
}