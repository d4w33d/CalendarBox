<?php

// =============================================================================

return [

    // -------------------------------------------------------------------------
    // Debug mode
    // Must be true for /db?action=... actions.

    'debug' => true,

    // -------------------------------------------------------------------------
    // Basic app settings

    'app' => [

        // The name of your application
        'name' => 'CalendarBox',

        // A sentence describing your app
        'baseline' => 'Team calendar',

        // Theme used by CalBox (see user/themes)
        'theme' => 'default',

    ],

    // -------------------------------------------------------------------------
    // Database

    'database' => [

        // Database connection settings.
        // If you are using sqlite, set username and password to null
        // and fill the filepath.
        // Available DBMS:
        //   - mysql (standard port: 3306)
        //   - pgsql (standard port: 5432)
        //   - sqlite (file: absolute path to a sqlite database file)
        'dbms' => 'mysql',
        'host' => 'localhost',
        'port' => 3306,
        'dbname' => 'foo',
        'username' => 'root',
        'password' => '',
        'filepath' => null,

        // The table names can be prefixed with that. Let's try "cb_"...
        'tables_prefix' => 'cb_',

        // Table names
        'tables' => [
            'event' => [
                'name' => 'events',
            ],
            'registration' => [
                'name' => 'registrations',
            ],
        ],

    ],

    // -------------------------------------------------------------------------
    // Internal URLs. You can modify URL rewriting rules to set your own
    // paths. Once it is done, set them here. Don't forget the
    // beginning slash "/" if the URL you want is on the server root.

    'routes' => [

        'index' => '',

        'create' => 'create',
        'edit' => 'edit',
        'delete' => 'delete',

        'addRegistration' => 'add-registration',
        'editRegistration' => 'edit-registration',
        'removeRegistration' => 'remove-registration',

        'db' => 'db',

    ],

    // -------------------------------------------------------------------------
    // Login/register and URLs behaviour

    'behaviour' => [

        // Options
        'options' => [

            // 'days' => [
            //     'label' => 'Disponibilité',
            //     'type' => 'days',
            // ],

            // 'foo' => [
            //     'label' => 'Foo',
            //     'type' => 'list',
            //     'nullable' => true,
            //     'default' => null,
            //     'choices' => [
            //         'First choice',
            //         'Second choice',
            //         'Third choice',
            //     ],
            // ],

        ],

        // Colors used for events in calendar
        // The first argument is the background color, and the second tell
        // if the text color must be light (l) - white - or dark (d) - black.
        'colors' => [
            [ '673c4f', 'l' ], [ '83b5d1', 'l' ], [ '726e97', 'l' ],
            [ '1a281f', 'l' ], [ 'ce7b91', 'l' ], [ 'b8d3d1', 'd' ],
            [ '635255', 'l' ], [ '247ba0', 'l' ], [ '70c1b3', 'd' ],
            [ 'f3ffbd', 'd' ], [ 'ff1654', 'l' ], [ '1985a1', 'l' ],
            [ 'e83f6f', 'l' ], [ 'ffbf00', 'd' ], [ '2274a5', 'l' ],
            [ '32936f', 'l' ], [ '6eeb83', 'd' ], [ 'e4ff1a', 'd' ],
            [ 'e8aa14', 'd' ], [ 'ff5714', 'l' ], [ '083d77', 'l' ],
            [ 'f4d35e', 'd' ], [ 'ee964b', 'd' ], [ 'f95738', 'l' ],
            [ '7ac74f', 'd' ], [ 'e87461', 'l' ], [ 'e0c879', 'd' ],
            [ 'f6511d', 'l' ], [ 'ffb400', 'd' ], [ '00a6ed', 'l' ],
            [ '7fb800', 'l' ], [ '0d2c54', 'l' ], [ 'e01a4f', 'l' ],
            [ 'f15946', 'l' ], [ '53b3cb', 'l' ], [ 'b10f2e', 'l' ],
            [ '570000', 'l' ], [ 'de7c5a', 'l' ], [ '3d1308', 'l' ],
        ],

    ],

];
