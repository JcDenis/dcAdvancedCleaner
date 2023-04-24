/*global $, dotclear */
'use strict';

Object.assign(dotclear.msg, dotclear.getData('dcAdvancedCleaner'));

$(() => {
  $('#parts_menu input[type=submit]').hide();
  $('#parts_menu #select_part').on('change', function () {this.form.submit();});
  dotclear.condSubmit('#form-funcs td input[type=checkbox]', '#form-funcs #do-action');
  $('#form-funcs').on('submit', function () {
      return window.confirm(dotclear.msg.confirm_delete);
  });
});