<?php

/**
 * @file
 * @brief   dcAdvancedCleaner resources helper.
 * @ingroup dcAdvancedCleaner
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
\Dotclear\App::backend()->resources()->set('help', 'dcAdvancedCleaner-cleaners', __DIR__ . '/help/cleaners.html');
\Dotclear\App::backend()->resources()->set('help', 'dcAdvancedCleaner-related', __DIR__ . '/help/related.html');
