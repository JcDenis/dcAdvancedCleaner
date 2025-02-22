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
    '1.7',
    [
        'requires' => [
            ['core', '2.33'],
            ['Uninstaller', '1.0'],
        ],
        'permissions' => 'My',
        'type'        => 'plugin',
        'support'     => 'https://github.com/JcDenis/' . basename(__DIR__) . '/issues',
        'details'     => 'https://github.com/JcDenis/' . basename(__DIR__) . '/src/branch/master/README.md',
        'repository'  => 'https://github.com/JcDenis/' . basename(__DIR__) . '/raw/branch/master/dcstore.xml',
        'date'        => '2025-02-22T10:10:10+00:00',
    ]
);
