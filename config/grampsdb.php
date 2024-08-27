<?php
return [
    'database' => [
        'default' => env('GRAMPSDB_CONNECTION', 'sqlite'),

        'connections' => [
            'grampsdb' => [
                'driver' => 'sqlite',
                'url' => env('GRAMPSDB_URL'),
                'database' => env('GRAMPS_SQLITE', grampsdb_laravel_database('/data/grampsdb.sqlite')),
                'prefix' => '',
                'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            ]
        ]
    ]
];