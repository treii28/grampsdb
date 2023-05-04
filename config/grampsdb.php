<?php

return [
    'database' => [
        'default' => env('GRAMPSDB_CONNECTION', 'grampsdb'),

        'connections' => [
            'woodgen' => [
                'driver' => 'sqlite',
                'url' => env('WOODGENDB_URL'),
                'database' => database_path(env('WOODGEN_SQLITE', 'data/woodgen.sqlite')),
                'prefix' => '',
                'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            ],
            'grampsdb' => [
                'driver' => 'sqlite',
                'url' => env('GRAMPSDB_URL'),
                'database' => database_path(env('GRAMPS_SQLITE', 'data/grampsdb.sqlite')),
                'prefix' => '',
                'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            ]
        ]
    ]
];