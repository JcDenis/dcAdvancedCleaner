/*global $, dotclear */
'use strict';

$(function () {
  $('#parts_menu input[type=submit]').hide();
  $('#parts_menu #select_part').on('change', function () {this.form.submit();});
  dotclear.condSubmit('#form-funcs td input[type=checkbox]', '#form-funcs #do-action');
});