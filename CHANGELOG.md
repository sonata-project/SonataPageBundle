# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [3.4.1](https://github.com/sonata-project/SonataPageBundle/compare/3.4.0...3.4.1) - 2017-04-04
### Deprecated
- Removed block service deprecation

### Fixed
- use `is not null` instead of `is defined` in `Block/block_base.html.twig`

## [3.4.0](https://github.com/sonata-project/SonataPageBundle/compare/3.3.0...3.4.0) - 2017-03-16
### Added
- Added --clean option to `sonata:page:update-core-routes` command to remove orphaned pages

### Fixed
- Configuration regex for `ignore_route_patterns` and `ignore_uri_patterns` nodes
- ISO 639 compatibility, `Site::$locale`, now has length set as 7 instead of 6.
- Add relative path to the "view page" link in the `PageAdmin`
- deprecated usage of the logger
- deprecated usage `configureSideMenu`

### Changed
- `CmsManagerSelector` now uses the `PageAdmin::isGranted` method to check for EDIT
- Route name from `admin_sonata_page_page_create` to method `sonata_admin.url()`.
- `Sonata\PageBundle\Admin\PageAdmin`, added method `getPersistentParameters`
- use font awesome icon instead of famfamfam icon in `select_site.html.twig`

### Deprecated
- Deprecated unused `--all` option in `sonata:page:update-core-routes` command
- Removed deprecation for `security.context`

## [3.3.0](https://github.com/sonata-project/SonataPageBundle/compare/3.2.0...3.3.0) - 2017-01-17
### Added
- Added new `sonata:page:clone` command
- Added `SiteRequestContextInterface` to check the current context type in get SiteRequestContext
- Added `SiteRequestContext::setSite()` to change the site context
- Added `SiteRequestContext::getSite()` to get the site context

### Changed
- Changed `CmsPageRouter::generateFromPage` to change the site context when generating the url for the given page

### Fixed
- Failed to create object: AppBundle\Entity\Site
- NotNull constraint on `Page` instead of `Site`
- Fixed `inherits_containers` feature Subject
- Missing `blockId` setting SharedBlockBlockService
- Use the correct protocol for urls
- The page title won't get overwritten anymore

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
