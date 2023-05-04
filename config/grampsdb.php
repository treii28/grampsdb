<?php

return [
    'database' => [
        'default' => env('GRAMPSDB_CONNECTION', 'grampsdb'),

        'connections' => [
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