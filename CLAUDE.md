# dcAdvancedCleaner

Notes for AI assistants working on the Dotclear plugin dcAdvancedCleaner.

**Read first** the shared conventions of the author's Dotclear modules:
https://raw.githubusercontent.com/JcDenis/gh2da/master/CONVENTIONS.md
(if a web tool refuses the address, use `curl -s` on it from the shell).
The rules below take precedence for this module.

## Module

It help clean up Dotclear install, plugins, themes, settings, tables, cache, etc... A user guide with screenshots is available in French: [guide d'utilisation](https://github.com/JcDenis/dcAdvancedCleaner/blob/master/docs/fr/utilisation.md).

Admin page. Nothing on the public side.

Required Dotclear and PHP versions: see `_define.php`.

## Structure

* `src/ActivityReportAction.php`: plugin activityReport class.
* `src/Backend.php`: admin side: menu entry, favorites, behaviors registration.
* `src/Manage.php`: admin page of the module.
* `src/ManageVars.php`: admin page (vars).
* `src/My.php`: module helpers (`My::id()`, settings, permissions).
* `src/Uninstall.php`: cleaners for the plugin Uninstaller.
* `js/`: `backend.js`.
* Behaviors used: `adminDashboardFavoritesV2`.
* Requires Uninstaller (`requires` in `_define.php`).

## Pitfalls

* CI installs Uninstaller from the author's repositories, then from franck-paul's.

## Testing

* Check the admin page, the admin help, in English and French.
* CI browses the common admin and public pages; extra pages can be listed in `.github/dotclear-test.txt`.
* The user guide `docs/fr/utilisation.md` describes the features: update it, and its screenshots, when the interface changes.
