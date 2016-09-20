# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [3.2.0](https://github.com/sonata-project/SonataPageBundle/compare/3.1.0...3.2.0) - 2016-09-20
### Added
- Added new command to create block container for all pages
- Added new `SnapshotPageFactory`

### Changed
- UniqueUrl validation isn't checked for Dynamic pages anymore
- `UniqueUrlValidator` is now more specific with the error, and the error is attached to a field

### Fixed
- `trigger_error` for deprecated `sonata.core.slugify.native`
- Removed deprecation warning for `Admin` usage.
- Removed deprecation warning for `AdminExtension` usage.
- Fixed duplicate translation in tab menu
- Fixed duplicate translation of batch actions
- make sure the scope in the container is clean to avoid failing test while unit testing the command
- Fixed deprecated doctrine methods
- top menu items not translated for the compose action
- add missing title in the compose action.
- Top menu links for edit/compose action are now highlighted.
- Custom query parameters are no longer lost on redirect.

### Removed
- The `beta` tag onto the `Composer` functionality

## [3.1.0](https://github.com/sonata-project/SonataPageBundle/compare/3.0.2...3.1.0) - 2016-08-01
### Fixed
- Warmup the cache from the CLI brings an error in HTTP
- Fixed PHP Fatal error:  Call to a member function getRelativePath() on null

### Removed
- Internal test classes are now excluded from the autoloader

## [3.0.2](https://github.com/sonata-project/SonataPageBundle/compare/3.0.1...3.0.2) - 2016-06-21
### Added
- Support version 2.x for `cocur/slugify` dependency

### Fixed
- Typo with `\RuntimeException` usage
- Fix missing `$transformer` property in `SnapshotPageProxy`
- Service definition `sonata.page.admin.page` not exists error if admin-bundle is not present

## [3.0.1](https://github.com/sonata-project/SonataPageBundle/compare/3.0.0...3.0.1) - 2016-06-13
### Fixed
- Fixed missing null check when rendering tree view without any site
- The page name is now correctly used as a page title fallback

### Removed
- Removed never implemented `sonata_page_url` twig function
