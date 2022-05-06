# Grampsdb

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleci]][link-styleci]

Gramps sqlite3 database helper to allow accessing the grampsdb in laravel.

## Installation

Via Composer

``` bash
$ composer require treii28/grampsdb-laravel
```

## Usage

This uses a specified database configuration in package config/grampsdb.php

e.g.:

```php
    'database' => [
        'default' => env('GRAMPSDB_CONNECTION', 'grampsdb'),

        'connections' => [
            'grampsdb' => [
                'driver' => 'sqlite',
                'url' => env('GRAMPSDB_URL'),
                'database' => database_path(env('GRAMPS_SQLITE', 'database.sqlite')),
                'prefix' => '',
                'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            ]
        ]
    ]
```

Thus the environment setting for **GRAMPS_SQLITE** can be set to point to a different filename under the `./database/` project path.  This file should be the file from the grampsdb software (found in unix under `$HOME/.gramps/grampsdb/{HASH}/sqlite.db`)

It will attempt to *'unpickle'* the blob_data in each table using a call to python.  It is bundled with an `unpickle.py` script in the bin directory and it can also utilize a distilled binary version created with `pyinstaller -F` from this script.  But an additional method is included to call python directly with a command line `exec`.

The data is extracted in the python script and returned as json. (unpickle.py will accept either a base64 encoded string of the blob_data on the command line or can have the raw byte binary data injected via stdin)  The resulting json data is then mapped to key/value pairs.

The call to python can be slow, so the code includes a 'caching' layer for extracting the blob data that checks a sha1 string stored with the cached entry to validate if the blob data has changed. A Seeder class is included to build a full cache of all records to speed up the library when you run it prior to use. Cached entries included the unpickled data stored as a json string. (unmapped)

```bash
    # initialize the Unpicklecache table
    $ php artisan migrate ## or migrate:refresh
    $ php artisan  db:seed --class="\\Treii28\\Grampsdb\\Database\\CacheSeeder"
```

Depending on the size of your tree and the number of sources or other data, this proces can take some time.

Only reading is done from the gramps sqlite file, although it's still recommended to make a copy of the gramps file (as opposed to giving the direct path or using a symlink).  Cached data is stored in the main laravel database connection in a Unpicklecache table accessible from the Unpicklecache model.  It keys of the dataType (e.g. 'Person', 'Family', 'Source', etc.) and the gramps_id.

If the grampsdb is updated, the caching mechanism will still work on records with the same dataType and gramps_id, skipping any blobs that have not been modified. (e.g. you can copy a newer version of the gramps sqlite file over the old one and re-run the cache seeder and it should run faster over unmodified entries)

If you fork the project, you can drop your own 'default' grampsdb sqlite file into the database/data directory and 'publish' it when needed. Otherwise copy your sqlite file to the main project's database/data directory and modify the **GRAMPS_SQLITE** `.env` value as necessary. (the environment variable assumes a path under the database/... path)

Mapping and specific function calls included for each individual record type's blob data. (relevant keys are added to the json string extracted when the blob_data is 'unpickled')

To use this functionality, php needs to know where to find the python interpreter. It will try to find it itself, but you can specify a specific command using **PYTHON_EXE**=. (this should be an absolute path)  You can also create your own platform specific version of the binary and point to it with **UNPICKLE_BINARY**=.

URLs for media files are currently interpreted as a pointer to an AWS S3 bucket specified with **AWS_BUCKET** environment variable (defaults to *grampsmedia*) in `us-east-1` unless a different region is specified by **AWS_REGION**. 
Their 'relative' path in that bucket can be given with the **GEDCOM_MEDIA_PATH** prefix which defaults to `gedcomx/media`

example `.env` entries:

```dotenv
    # gramps helper configuration
    GRAMPS_SQLITE=data/grampsdb.sqlite
    # gramps sqlite3 config key in database.php
    GRAMPS_DBNAME=grampsdb
    PYTHON_EXE=/usr/bin/python3
    AWS_REGION=us-east-2
    AWS_BUCKET=woodgen
    GEDCOM_MEDIA_PATH=gedcomx/media
    # optional
    UNPICKLE_BINARY=bin/unpickle
```

example usage:

```php
    use Treii28\Grampsdb\GrampsdbHelper;

    // use a specific static 'key' configured in database.php (optional)
    GrampsdbHelper::setDbConnection('grampsdb');

    // find someone by their gramps_id
    $gramps_id = 'I12441296307';
    $person1 = GrampsdbHelper::getPersonById($gramps_id);


    // find someone by their 'handle'    
    $personHandle = 'efaddc1e2db7f12032d5c4f9560';
    $withMedia = true;
    $person2 = GrampsdbHelper::getPersonByHandle($personHandle, $withMedia);

    // get just the media references by handle
    $person_media = GrampsdbHelper::getMediaByPersonHandle($personHandle);

    // get the events related to a person
    $pevts = GrampsdbHelper::getEventsByPersonHandle($personHandle);
```

## Change log

This package is a work in progress.

Please see the [changelog](changelog.md) for more information on what has changed recently.

Potential changes coming:
- built in support for local url references instead of using AWS for media
- additional data search functions with relevant blob_data mapping by type
- conditional blob_data extraction via unpickle

## Testing

*Testing code pending*

``` bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email treii28@gmail.com instead of using the issue tracker.

## Credits

- [Scott Webster Wood][link-author]
- [All Contributors][link-contributors]
- [Gramps Genealogy Software](https://gramps-project.org/blog/)
- [Gramps Database Formats](https://www.gramps-project.org/wiki/index.php/Database_Formats)

## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/treii28/grampsdb.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/treii28/grampsdb.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/treii28/grampsdb/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/treii28/grampsdb
[link-downloads]: https://packagist.org/packages/treii28/grampsdb
[link-travis]: https://travis-ci.org/treii28/grampsdb
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/treii28
[link-contributors]: ../../contributors
