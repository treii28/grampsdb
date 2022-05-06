# Changelog

All notable changes to `Grampsdb` will be documented in this file.

## Version 1.0

### Added
- python call added to 'unpickle' blob_data
- mapping for individual record types (those tables/data with blob_data and gramps_id columns)
- caching added for unpickle functionality with sha1 check for changes to blob_data when uncaching
- Seeder class added to pre-populate (or update) the unpickle cache
- separate database configuration added so grampsdb sqlite can be used for read-only purposes
- AWS bucket url mapping method added

### Pending
- still adding various get*by* type functionality for various data types and cleaning up/normalizing those already included.
- will add documentation for individual get*** functions once normalized (in addition to inline phpdoc)
