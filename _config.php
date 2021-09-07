<?php


if (!defined('DC_CONTEXT_MODULE')) {
    return null;
}
if (!$core->auth->isSuperAdmin()) {
    return null;
}
if (!empty($_POST['save'])) {
    try {
        $core->blog->settings->dcAdvancedCleaner->dropEvery(
            'dcAdvancedCleaner_behavior_active'
        );
        $core->blog->settings->dcAdvancedCleaner->put(
            'dcAdvancedCleaner_behavior_active', 
            !empty($_POST['behavior_active']), 
            'boolean',
            null,
            true,
            true
        );
        $core->blog->settings->dcAdvancedCleaner->dropEvery(
            'dcAdvancedCleaner_dcproperty_hide'
        );
        $core->blog->settings->dcAdvancedCleaner->put(
            'dcAdvancedCleaner_dcproperty_hide', 
            !empty($_POST['dcproperty_hide']), 
            'boolean',
            null,
            true,
            true
        );
        dcPage::addSuccessNotice(
            __('Configuration successfully updated.')
        );
        $core->adminurl->redirect(
            'admin.plugins', 
            [
                'module' => 'dcAdvancedCleaner', 
                'conf' => 1, 
                'redir' => empty($_REQUEST['redir']) ? $list->getURL() . '#plugins' : $_REQUEST['redir']
            ]
        );
    }
    catch(Exception $e) {
        $core->error->add($e->getMessage());
    }
}
echo '
<p><label class="classic" for="behavior_active">' .
form::checkbox(
    'behavior_active', 
    1, 
    $core->blog->settings->dcAdvancedCleaner->dcAdvancedCleaner_behavior_active
) . ' ' . __('Activate behaviors') . '</label></p>
<p class="form-note">' . __('Enable actions set in _uninstall.php files.') . '</p>
<p><label class="classic" for="dcproperty_hide">' .
form::checkbox(
    'dcproperty_hide', 
    1, 
    $core->blog->settings->dcAdvancedCleaner->dcAdvancedCleaner_dcproperty_hide
) . ' ' . __('Hide Dotclear default properties in actions tabs') . '</label></p>
<p class="form-note">' . 
__('Prevent from deleting Dotclear important properties.') . '</p>';