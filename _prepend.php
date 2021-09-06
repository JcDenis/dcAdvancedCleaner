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