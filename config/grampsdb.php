<?php
$dbpath = realpath(__DIR__.'/../database');
return [
    'database' => [
        'default' => env('GRAMPSDB_CONNECTION', 'woodgen'),

        'connections' => [
            'grampsdb' => [
                'driver' => 'sqlite',
                'url' => env('GRAMPSDB_URL'),
                'database' => env('GRAMPS_SQLITE', $dbpath.'/data/grampsdb.sqlite'),
                'prefix' => '',
                'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            ]
        ]
    ]
];