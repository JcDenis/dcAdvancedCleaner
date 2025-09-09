<?php

declare(strict_types=1);

namespace Dotclear\Plugin\dcAdvancedCleaner;

use Dotclear\App;
use Dotclear\Helper\Process\TraitProcess;
use Dotclear\Core\Backend\{
    Notices,
    Page
};
use Dotclear\Helper\Html\Form\{
    Caption,
    Checkbox,
    Div,
    Form,
    Hidden,
    Img,
    Label,
    Link,
    Note,
    Para,
    Select,
    Submit,
    Table,
    Tbody,
    Td,
    Th,
    Thead,
    Text,
    Tr
};
use Dotclear\Helper\Html\Html;
use Exception;

/**
 * @brief   dcAdvancedCleaner manage class.
 * @ingroup dcAdvancedCleaner
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Manage
{
    use TraitProcess;

    public static function init(): bool
    {
        return self::status(My::checkContext(My::MANAGE));
    }

    public static function process(): bool
    {
        if (!self::status() || !App::plugins()->moduleExists('Uninstaller')) {
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
                App::error()->add($e->getMessage());
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
        App::behavior()->callBehavior('dcAdvancedCleanerAdminHeader');

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
            echo (new Div())
                ->items([
                    (new Form('parts_menu'))->method('get')->action(App::backend()->getPageURL())->fields([
                        (new Para())->class('anchor-nav')->items([
                            (new Label(__('Goto:'), Label::OUTSIDE_LABEL_BEFORE))->for('part')->class('classic'),
                            (new Select(['part', 'select_part']))->default($vars->cleaner->id)->items($vars->combo),
                            (new Submit('go'))->value(__('Ok')),
                            ... My::hiddenFields(),
                        ]),
                    ]),
                    (new Text('h3', $vars->cleaner->name)),
                    (new Text('p', $vars->cleaner->desc)),
                ])
                ->render();

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

                $lines = [];
                foreach ($rs as $key => $value) {
                    $distrib = in_array($value->ns, $vars->cleaner->distributed());

                    if ($distrib && My::settings()->getGlobal('dcproperty_hide')) {
                        continue;
                    }
                    $lines[] = (new Tr())
                        ->class('line')
                        ->cols([
                            (new Td())
                                ->class('nowrap')
                                ->items([
                                    (new Checkbox(['entries[' . $key . ']', 'entries_' . $key]))->value(Html::escapeHTML($value->ns)),
                                ]),
                            (new Td())
                                ->class('nowrap')
                                ->items([
                                    (new Label($value->ns, Label::OUTSIDE_LABEL_AFTER))->for('entries_' . $key)->class('classic'),
                                ]),
                            (new Td())
                                ->class('nowrap')
                                ->text((string) ($value->id != '' ? $value->id : $value->count)),
                            (new Td())
                                ->class('module-distrib')
                                ->items($distrib ? [
                                    (new Img('images/dotclear-leaf.svg'))
                                        ->alt(__('Values from official distribution')),
                                ] : []),
                            (new Td())
                                ->class('maximal')
                                ->items($has_related ? [
                                    (new Link())
                                        ->href(My::manageUrl(['part' => $vars->cleaner->id, 'related' => $value->ns]))
                                        ->text(__('Details')),
                                ] : []),
                        ]);
                }

                echo (new Form('form-funcs'))
                    ->method('post')
                    ->action(App::backend()->getPageURL())
                    ->fields([
                        (new Div())
                            ->class('table-outer')
                            ->items([
                                (new Table())
                                    ->caption(new Caption(sprintf(__('There are %s entries'), count($rs))))
                                    ->thead(
                                        (new Thead())
                                            ->rows([
                                                (new Tr())
                                                    ->cols([
                                                        (new Th())
                                                            ->colspan(2)
                                                            ->text(__('Name')),
                                                        (new Th())
                                                            ->colspan(3)
                                                            ->text(__('Objects')),
                                                    ]),
                                            ])
                                    )
                                    ->tbody(
                                        (new Tbody())
                                            ->rows($lines)
                                    ),
                            ]),
                        (new Para())->items([
                            (new Label(__('Action on selected rows:'), Label::OUTSIDE_LABEL_BEFORE))->for('select_action'),
                            (new Select(['action', 'select_action']))->items($combo_actions),
                            (new Submit('do-action'))->class('delete')->value(__('I understand and I am want to delete this')),
                            (new Hidden(['part'], $vars->cleaner->id)),
                            ... My::hiddenFields(),
                        ]),
                        (new Text('p', __('Beware: All actions done here are irreversible and are directly applied')))
                            ->class('warning'),
                    ])
                    ->render();
            }

            echo
            (new Form('option'))->method('post')->action(App::backend()->getPageURL())->fields([
                (new Para())->items([
                    (new Submit('option-action'))->value(My::settings()->getGlobal('dcproperty_hide') ? __('Show Dotclear default properties') : __('Hide Dotclear default properties')),
                    (new Hidden('dcproperty_hide', My::settings()->getGlobal('dcproperty_hide') ? '0' : '1')),
                    (new Hidden(['part'], $vars->cleaner->id)),
                    ... My::hiddenFields(),
                ]),
            ])->render();
        } else {
            echo (new Div())
                ->items([
                    (new Para())
                        ->items([
                            (new Link())
                                ->class('back')
                                ->href(My::manageUrl(['part' => $vars->cleaner->id]))
                                ->text(__('Back'))
                        ]),
                    (new Text('h3', $vars->cleaner->name . ' : ' . $vars->related)),
                    (new Text('p', $vars->cleaner->desc)),
                ])
                ->render();

            $distrib = in_array($vars->related, $vars->cleaner->distributed());
            $rs      = $vars->cleaner->related($vars->related);
            if (empty($rs)) {
                echo (new Text('p', __('There is nothing to display')))->class('error')->render();
            } else {
                $lines = [];
                foreach ($rs as $key => $value) {
                    $lines[] = (new Tr())
                        ->class('line')
                        ->cols([
                            (new Td())
                                ->class('nowrap')
                                ->items([
                                    (new Checkbox(['entries[' . $key . ']', 'entries_' . $key]))->value(Html::escapeHTML($value->id))
                                ]),
                            (new Td())
                                ->class('nowrap')
                                ->items([
                                    (new Label($value->id, Label::OUTSIDE_LABEL_AFTER))->for('entries_' . $key)->class('classic')
                                ]),
                            (new Td())
                                ->class(['nowrap', 'maximal'])
                                ->text((string) $value->count),
                        ]);
                }

                echo (new Form('form-funcs'))
                    ->method('post')
                    ->action(App::backend()->getPageURL())
                    ->fields([
                        (new Div())
                            ->class('table-outer')
                            ->items([
                                (new Table())
                                    ->caption(new Caption(sprintf(__('There are %s related entries for the group "%s"'), count($rs), $vars->related)))
                                    ->thead(
                                        (new Thead())
                                            ->rows([
                                                (new Tr())
                                                    ->cols([
                                                        (new Th())
                                                            ->colspan(2)
                                                            ->text(__('Name')),
                                                        (new Th())
                                                            ->text(__('Objects')),
                                                    ]),
                                            ])
                                    )
                                    ->tbody(
                                        (new Tbody())
                                            ->rows($lines)
                                    ),
                            ]),
                        (new Para())->items([
                            (new Submit('do-action'))->class('delete')->value(__('I understand and I am want to delete this')),
                            ... My::hiddenFields([
                                'related' => $vars->related,
                                'part'    => $vars->cleaner->id,
                                'action'  => 'delete_related',
                            ]),
                        ]),
                        (new Text('p', __('Beware: All actions done here are irreversible and are directly applied')))
                            ->class('warning'),
                    ])
                    ->render();
            }
        }

        Page::closeModule();
    }
}
