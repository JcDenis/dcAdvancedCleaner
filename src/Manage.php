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
use Dotclear\Core\Process;
use Dotclear\Core\Backend\{
    Notices,
    Page
};
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

class Manage extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::MANAGE));
    }

    public static function process(): bool
    {
        if (!self::status() || !dcCore::app()->plugins->moduleExists('Uninstaller')) {
            return false;
        }

        $vars = ManageVars::init();

        if (null === $vars->cleaner) {
            return true;
        }

        if (!empty($_POST['option-action'])) {
            My::settings()->dropEvery(
                'dcproperty_hide'
            );
            My::settings()->put(
                'dcproperty_hide',
                !empty($_POST['dcproperty_hide']),
                'boolean',
                'Hide Dotclear default properties',
                true,
                true
            );
            Notices::addSuccessNotice(__('Configuration successfully updated.'));
            My::redirect(['part' => $vars->cleaner->id]);
        }

        if (!empty($vars->entries) && !empty($vars->action)) {
            try {
                // special related
                if (!empty($vars->related) && $vars->action == 'delete_related') {
                    $ns = '';
                    foreach ($vars->entries as $id) {
                        $ns .= $vars->related . ':' . $id . ';';
                    }
                    $vars->cleaners->execute($vars->cleaner->id, $vars->action, $ns);
                // other actions
                } elseif ($vars->action != 'delete_related') {
                    foreach ($vars->entries as $ns) {
                        $vars->cleaners->execute($vars->cleaner->id, $vars->action, $ns);
                    }
                }

                Notices::addSuccessNotice(__('Action successfuly excecuted'));
                My::redirect(['part' => $vars->cleaner->id]);
            } catch (Exception $e) {
                dcCore::app()->error->add($e->getMessage());
            }
        }

        return true;
    }

    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        $vars = ManageVars::init();

        Page::openModule(
            My::name(),
            Page::jsJson('dcAdvancedCleaner', ['confirm_delete' => __('Are you sure you perform these ations?')]) .
            My::cssLoad('backend') .
            My::jsLoad('backend')
        );

        # --BEHAVIOR-- dcAdvancedCleanerAdminHeader
        dcCore::app()->callBehavior('dcAdvancedCleanerAdminHeader');

        $breadcrumb = [
            __('Plugins') => '',
            My::name()    => '',
        ];

        // something went wrong !
        if (null === $vars->cleaner) {
            echo
            Page::breadcrumb($breadcrumb) .
            Notices::getNotices();
            echo (new Text('p', __('There is nothing to display')))->class('error')->render();
            Page::closeModule();

            return;
        }

        $breadcrumb[My::name()]           = My::manageUrl();
        $breadcrumb[$vars->cleaner->name] = '';

        if (!empty($vars->related)) {
            $breadcrumb[$vars->cleaner->name] = My::manageUrl(['part' => $vars->cleaner->id]);
            $breadcrumb[$vars->related]       = '';
        }

        echo
        Page::breadcrumb($breadcrumb) .
        Notices::getNotices();

        if (empty($vars->related)) {
            echo
            (new Form('parts_menu'))->method('get')->action(dcCore::app()->admin->getPageURL())->fields([
                (new Para())->class('anchor-nav')->items([
                    (new Label(__('Goto:'), Label::OUTSIDE_LABEL_BEFORE))->for('part')->class('classic'),
                    (new Select(['part', 'select_part']))->default($vars->cleaner->id)->items($vars->combo),
                    (new Submit('go'))->value(__('Ok')),
                    ... My::hiddenFields(),
                ]),
            ])->render() .

            '<h3>' . $vars->cleaner->name . '</h3><p>' . $vars->cleaner->desc . '</p>';

            $rs = $vars->cleaner->values();
            if (empty($rs)) {
                echo (new Text('p', __('There is nothing to display')))->class('error')->render();
            } else {
                $combo_actions = [];
                $has_related   = false;
                foreach ($vars->cleaner->actions as $descriptor) {
                    // exception
                    if ($descriptor->id == 'delete_related') {
                        $has_related = true;

                        continue;
                    }
                    $combo_actions[$descriptor->select] = $descriptor->id;
                }

                echo
                '<form method="post" action="' . dcCore::app()->admin->getPageURL() . '" id="form-funcs">' .
                '<div class="table-outer">' .
                '<table><caption>' . sprintf(__('There are %s entries'), count($rs)) . '</caption><thead><tr>' .
                '<th colspan="2">' . __('Name') . '</th><th colspan="2">' . __('Objects') . '</th>' .
                '<th></th>' .
                '</tr></thead><tbody>';

                foreach ($rs as $key => $value) {
                    $distrib = in_array($value->ns, $vars->cleaner->distributed());

                    if ($distrib && My::settings()->get('dcproperty_hide')) {
                        continue;
                    }
                    echo
                    '<tr class="line' . ($distrib ? ' offline' : '') . '">' .
                    '<td class="nowrap">' .
                        (new Checkbox(['entries[' . $key . ']', 'entries_' . $key]))->value(Html::escapeHTML($value->ns))->render() .
                    '</td> ' .
                    '<td class="nowrap">' .
                        (new Label($value->ns, Label::OUTSIDE_LABEL_AFTER))->for('entries_' . $key)->class('classic')->render() .
                    '</td>' .
                    '<td class="nowrap">' . ($value->id != '' ? $value->id : $value->count) . '</td>' .
                    '<td class="module-distrib">' . ($distrib ?
                        '<img src="images/dotclear-leaf.svg" alt="' .
                        __('Values from official distribution') . '" title="' .
                        __('Values from official distribution') . '" />'
                    : '') . '</td>' .
                    '<td class="maximal">' . ($has_related ? ' <a href="' .
                        My::manageUrl(['part' => $vars->cleaner->id, 'related' => $value->ns]) .
                    '">' . __('Details') . '<a>' : '') . '</td>' .
                    '</tr>';
                }

                echo
                '</tbody></table></div>' .
                (new Para())->items([
                    (new Label(__('Action on selected rows:'), Label::OUTSIDE_LABEL_BEFORE))->for('select_action'),
                    (new Select(['action', 'select_action']))->items($combo_actions),
                    (new Submit('do-action'))->class('delete')->value(__('I understand and I am want to delete this')),
                    (new Hidden(['part'], $vars->cleaner->id)),
                    ... My::hiddenFields(),
                ])->render() .
                '<p class="warning">' .
                __('Beware: All actions done here are irreversible and are directly applied') .
                '</p>' .
                '</form>';
            }

            echo
            (new Form('option'))->method('post')->action(dcCore::app()->admin->getPageURL())->fields([
                (new Para())->items([
                    (new Submit('option-action'))->value(My::settings()->get('dcproperty_hide') ? __('Show Dotclear default properties') : __('Hide Dotclear default properties')),
                    (new Hidden('dcproperty_hide', (string) (int) My::settings()->get('dcproperty_hide'))),
                    (new Hidden(['part'], $vars->cleaner->id)),
                    ... My::hiddenFields(),
                ]),
            ])->render();
        } else {
            echo
            '<p><a class="back" href="' . My::manageUrl(['part' => $vars->cleaner->id]) . '">' . __('Back') . '</a></p>' .
            '<h3>' . $vars->cleaner->name . ' : ' . $vars->related . '</h3><p>' . $vars->cleaner->desc . '</p>';

            $distrib = in_array($vars->related, $vars->cleaner->distributed());
            $rs      = $vars->cleaner->related($vars->related);
            if (empty($rs)) {
                echo (new Text('p', __('There is nothing to display')))->class('error')->render();
            } else {
                echo
                '<form method="post" action="' . dcCore::app()->admin->getPageURL() . '" id="form-funcs">' .
                '<div class="table-outer">' .
                '<table><caption>' . sprintf(__('There are %s related entries for the group "%s"'), count($rs), $vars->related) . '</caption><thead><tr>' .
                '<th colspan="2">' . __('Name') . '</th><th>' . __('Objects') . '</th>' .
                '</tr></thead><tbody>';

                foreach ($rs as $key => $value) {
                    echo
                    '<tr class="line">' .
                    '<td class="nowrap">' .
                        (new Checkbox(['entries[' . $key . ']', 'entries_' . $key]))->value(Html::escapeHTML($value->id))->render() .
                    '</td> ' .
                    '<td class="nowrap">' .
                        (new Label($value->id, Label::OUTSIDE_LABEL_AFTER))->for('entries_' . $key)->class('classic')->render() .
                    '</td>' .
                    '<td class="nowrap maximal">' . $value->count . '</td>' .
                    '</tr>';
                }

                echo
                '</tbody></table></div>' .
                (new Para())->items([
                    (new Submit('do-action'))->class('delete')->value(__('I understand and I am want to delete this')),
                    (new Hidden(['related'], $vars->related)),
                    (new Hidden(['part'], $vars->cleaner->id)),
                    (new Hidden(['action'], 'delete_related')),
                    ... My::hiddenFields(),
                ])->render() .
                '<p class="warning">' .
                __('Beware: All actions done here are irreversible and are directly applied') .
                '</p>' .
                '</form>';
            }
        }

        Page::closeModule();
    }
}
