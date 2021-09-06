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

if (!defined('DC_CONTEXT_ADMIN')){return;}

# Get new version
$new_version = $core->plugins->moduleInfo('dcAdvancedCleaner','version');
$old_version = $core->getVersion('dcAdvancedCleaner');

# Compare versions
if (version_compare($old_version,$new_version,'>=')) {return;}

# Install or update
try
{
    # Check DC version
    if (version_compare(str_replace("-r","-p",DC_VERSION),'2.2-alpha','<'))
    {
        throw new Exception('dcAdvancedCleaner requires Dotclear 2.2');
    }

    # Settings
    $core->blog->settings->addNamespace('dcAdvancedCleaner');
    $core->blog->settings->dcAdvancedCleaner->put('dcAdvancedCleaner_behavior_active',true,'boolean','',false,true);
    $core->blog->settings->dcAdvancedCleaner->put('dcAdvancedCleaner_dcproperty_hide',true,'boolean','',false,true);

    # Version
    $core->setVersion('dcAdvancedCleaner',$new_version);

    return true;
}
catch (Exception $e)
{
    $core->error->add($e->getMessage());
    return false;
}