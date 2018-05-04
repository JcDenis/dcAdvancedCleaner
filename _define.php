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

$this->registerModule(
	/* Name */			"Advanced cleaner",
	/* Description*/		"Make a huge cleaning of dotclear",
	/* Author */			"JC Denis",
	/* Version */			'0.7.3',
	/* Properties */
	array(
		'permissions' => null,
		'type' => 'plugin',
		'dc_min' => '2.9',
		'support' => 'https://forum.dotclear.org/viewtopic.php?id=40381',
		'details' => 'http://plugins.dotaddict.org/dc2/details/dcAdvancedCleaner'
		)
);
	/* date */		#20180213