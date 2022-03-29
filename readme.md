# Grampsdb

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleci]][link-styleci]

Gramps sqlite3 database helper to allow accessing the grampsdb in laravel.

## Installation

Via Composer

``` bash
$ composer require treii28/grampsdb
```

## Usage

This uses a specified database configuration in your config/database.php

e.g.:

`

    'connections' => [

        'grampsdb' => [
            'driver' => 'sqlite',
            'database' => database_path(env('GRAMPS_SQLITE', 'data/grampsdb.sqlite')),
            'prefix' => ''
        ],
    // ...
`

Thus the environment setting for **GRAMPS_SQLITE** can be set to point to a different filename under the `./database/` project path.

It will attempt to *'unpickle'* the blob_data in each table using a call to python.  It is bundled with an `unpickle.py` script in the bin directory and it can also utilize a distilled binary version created with `pyinstaller -F` from this script.  But an additional method is included to call python directly with a command line `exec`.

The data is extracted in the python script and returned as json. (unpickle.py will accept either a base64 encoded string of the blob_data on the command line or can have the raw byte binary data injected via stdin)  The resulting json data is then mapped to key/value pairs.

Mapping and specific function calls included for person, event and media data. (more coming soon)

To use this functionality, php needs to know where to find the python interpreter. It will try to find it itself, but you can specify a specific command using **PYTHON_EXE**=. (this should be an absolute path)  You can also create your own platform specific version of the binary and point to it with **UNPICKLE_BINARY**=.

URLs for media files are currently interpreted as a pointer to an AWS S3 bucket specified with **AWS_BUCKET** environment variable (defaults to *grampsmedia*) in `us-east-1` unless a different region is specified by **AWS_REGION**. 
Their 'relative' path in that bucket can be given with the **GEDCOM_MEDIA_PATH** prefix which defaults to `gedcomx/media`

example `.env` entries:

`

    # gramps helper configuration
    GRAMPS_SQLITE=data/grampsdb.sqlite
    # gramps sqlite3 config key in database.php
    GRAMPS_DBNAME=grampsdb
    PYTHON_EXE=/usr/bin/python3
    AWS_REGION=us-east-2
    AWS_BUCKET=woodgen
    # optional
    UNPICKLE_BINARY=bin/unpickle

`

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
