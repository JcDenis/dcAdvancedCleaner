<?php

/**
 * @file
 * @brief       The plugin dcAdvancedCleaner definition
 * @ingroup     dcAdvancedCleaner
 *
 * @defgroup    dcAdvancedCleaner Plugin dcAdvancedCleaner.
 *
 * Make a huge cleaning of dotclear.
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

$this->registerModule(
    'Advanced cleaner',
    'Make a huge cleaning of dotclear',
    'Jean-Christian Denis and Contributors',
    '1.6.1',
    [
        'requires' => [
            ['core', '2.28'],
            ['Uninstaller', '1.0'],
        ],
        'permissions' => 'My',
        'type'        => 'plugin',
        'support'     => 'https://git.dotclear.watch/JcDenis/' . basename(__DIR__) . '/issues',
        'details'     => 'https://git.dotclear.watch/JcDenis/' . basename(__DIR__) . '/src/branch/master/README.md',
        'repository'  => 'https://git.dotclear.watch/JcDenis/' . basename(__DIR__) . '/raw/branch/master/dcstore.xml',
    ]
);
