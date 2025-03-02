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
    '1.7.1',
    [
        'requires' => [
            ['core', '2.33'],
            ['Uninstaller', '1.0'],
        ],
        'permissions' => 'My',
        'type'        => 'plugin',
        'support'     => 'https://github.com/JcDenis/' . $this->id . '/issues',
        'details'     => 'https://github.com/JcDenis/' . $this->id . '/',
        'repository'  => 'https://raw.githubusercontent.com/JcDenis/' . $this->id . '/master/dcstore.xml',
        'date'        => '2025-02-24T23:31:12+00:00',
    ]
);
