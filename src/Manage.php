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
declare(strict_types=1);

namespace Dotclear\Plugin\dcAdvancedCleaner;

use dcCore;
use dcNsProcess;
use dcPage;
use Dotclear\Helper\Html\Form\{
    Checkbox,
    Div,
    Form,
    Hidden,
    Label,
    Note,
    Para,
    Select,
    Submit,
    Text
};
use Dotclear\Helper\Html\Html;
use Exception;

class Manage extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_CONTEXT_ADMIN')
            && dcCore::app()->auth?->isSuperAdmin()
            && My::phpCompliant();

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init || !dcCore::app()->plugins->moduleExists('Uninstaller')) {
            return false;
        }

        $vars = ManageVars::init();

        if (null === $vars->cleaner) {
            return true;
        }

        if (!empty($_POST['option-action'])) {
            dcCore::app()->blog?->settings->get(My::id())->dropEvery(
                'dcproperty_hide'
            );
            dcCore::app()->blog?->settings->get(My::id())->put(
                'dcproperty_hide',
                !empty($_POST['dcproperty_hide']),
                'boolean',
                'Hide Dotclear default properties',
                true,
                true
            );
            dcPage::addSuccessNotice(__('Configuration successfully updated.'));
            dcCore::app()->adminurl?->redirect(
                'admin.plugin.' . My::id(),
                ['part' => $vars->cleaner->id]
            );
        }

        if (!empty($vars->entries) && !empty($vars->action)) {
            try {
                foreach ($vars->entries as $ns) {
                    $vars->cleaners->execute($vars->cleaner->id, $vars->action, $ns);
                }
                dcPage::addSuccessNotice(__('Action successfuly excecuted'));
                dcCore::app()->adminurl?->redirect(
                    'admin.plugin.' . My::id(),
                    ['part' => $vars->cleaner->id]
                );
            } catch (Exception $e) {
                dcCore::app()->error->add($e->getMessage());
            }
        }

        return true;
    }

    public static function render(): void
    {
        if (!static::$init) {
            return;
        }

        $vars = ManageVars::init();

        dcPage::openModule(
            My::name(),
            dcPage::cssModuleLoad(My::id() . '/css/backend.css') .
            dcPage::jsModuleLoad(My::id() . '/js/backend.js')
        );

        # --BEHAVIOR-- dcAdvancedCleanerAdminHeader
        dcCore::app()->callBehavior('dcAdvancedCleanerAdminHeader');

        echo
        dcPage::breadcrumb([
            __('Plugins') => '',
            My::name()    => '',
        ]) .
        dcPage::notices();

        if (null === $vars->cleaner) {
            echo (new Text('p', __('There is nothing to display')))->class('error')->render();
            dcPage::closeModule();

            return;
        }

        echo
        (new Form('parts_menu'))->method('get')->action(dcCore::app()->adminurl?->get('admin.plugin.' . My::id()))->fields([
            (new Para())->class('anchor-nav')->items([
                (new Label(__('Goto:'), Label::OUTSIDE_LABEL_BEFORE))->for('part')->class('classic'),
                (new Select(['part', 'select_part']))->default($vars->cleaner->id)->items($vars->combo),
                (new Submit('go'))->value(__('Ok')),
                (new Hidden(['p'], My::id())),
            ]),
        ])->render() .

        '<h3>' . $vars->cleaner->name . '</h3><p>' . $vars->cleaner->desc . '</p>';

        $rs = $vars->cleaner->values();

        if (empty($rs)) {
            echo (new Text('p', __('There is nothing to display')))->class('error')->render();
        } else {
            $combo_actions = [];
            foreach ($vars->cleaner->actions as $descriptor) {
                // exception
                if ($descriptor->id == 'delete_related') {
                    continue;
                }
                $combo_actions[$descriptor->select] = $descriptor->id;
            }

            echo
            '<form method="post" action="' . dcCore::app()->adminurl?->get('admin.plugin.' . My::id()) . '" id="form-funcs">' .
            '<div class="table-outer">' .
            '<table><caption>' . sprintf(__('There are %s entries'), count($rs)) . '</caption><thead><tr>' .
            '<th colspan="2">' . __('Name') . '</th><th colspan="2">' . __('Objects') . '</th>' .
            '</tr></thead><tbody>';

            foreach ($rs as $k => $v) {
                $distrib = in_array($v['key'], $vars->cleaner->distributed());

                if ($distrib && dcCore::app()->blog?->settings->get(My::id())->get('dcproperty_hide')) {
                    continue;
                }
                echo
                '<tr class="line' . ($distrib ? ' offline' : '') . '">' .
                '<td class="nowrap">' .
                    (new Checkbox(['entries[' . $k . ']', 'entries_' . $k]))->value(Html::escapeHTML($v['key']))->render() .
                '</td> ' .
                '<td class="nowrap">' .
                    (new Label($v['key'], Label::OUTSIDE_LABEL_AFTER))->for('entries_' . $k)->class('classic')->render() .
                '</td>' .
                '<td class="nowrap">' . $v['value'] . '</td>' .
                '<td class="module-distrib maximal">' . ($distrib ?
                    '<img src="images/dotclear-leaf.svg" alt="' .
                    __('Values from official distribution') . '" title="' .
                    __('Values from official distribution') . '" />'
                : '') . '</td>' .
                '</tr>';
            }

            echo
            '</tbody></table></div>' .
            (new Para())->items([
                (new Label(__('Action on selected rows:'), Label::OUTSIDE_LABEL_BEFORE))->for('select_action'),
                (new Select(['action', 'select_action']))->items($combo_actions),
                (new Submit('do-action'))->value(__('ok')),
                (new Hidden(['p'], My::id())),
                (new Hidden(['part'], $vars->cleaner->id)),
                dcCore::app()->formNonce(false),
            ])->render() .
            '<p class="warning">' .
            __('Beware: All actions done here are irreversible and are directly applied') .
            '</p>' .
            '</form>';
        }

        echo
        (new Form('option'))->method('post')->action(dcCore::app()->adminurl?->get('admin.plugin.' . My::id()))->fields([
            (new Para())->items([
                (new Submit('option-action'))->value(dcCore::app()->blog?->settings->get(My::id())->get('dcproperty_hide') ? __('Show Dotclear default properties') : __('Hide Dotclear default properties')),
                (new Hidden('dcproperty_hide', (string) (int) !dcCore::app()->blog->settings->get(My::id())->get('dcproperty_hide'))),
                (new Hidden(['p'], My::id())),
                (new Hidden(['part'], $vars->cleaner->id)),
                dcCore::app()->formNonce(false),
            ]),
        ])->render();

        dcPage::closeModule();
    }
}
