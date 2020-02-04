# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [3.15.0](https://github.com/sonata-project/SonataPageBundle/compare/3.14.0...3.15.0) - 2020-01-27
### Changed
- Re-allow SF DI>4.4.0
- Upgrade matthiasnoback/symfony-dependency-injection-test to ^4.0

## [3.14.0](https://github.com/sonata-project/SonataPageBundle/compare/3.13.0...3.14.0) - 2020-01-12
### Changed
- `BlockInteractor` constructor's argument $registry is now an instance of
  `Doctrine\Persistence\ManagerRegistry`
- `Transformer` constructor's argument $registry is now an instance of
  `Doctrine\Persistence\ManagerRegistry`

### Fixed
- deprecations from `doctrine/persistence`

## [3.13.0](https://github.com/sonata-project/SonataPageBundle/compare/3.12.0...3.13.0) - 2019-10-21
### Added
- Add missing translation for admin menu
- Added missing translation for blocks
- Add more `@method` annotation to propagate new methods of
  `SnapshotManagerInterface`

### Fixed
- Fixed `DecoratorStrategy` compatibility with `symfony/http-foundation` >= 3.4.31
- Match PHPDoc with doctrine model

### Changed
- Add `internal` as default context for technical blocks
- Use correct translation domain for page blocks

### Removed
- Remove translation call for empty block descriptions
- Remove superfluous PHPDoc

## [3.12.0](https://github.com/sonata-project/SonataPageBundle/compare/3.11.1...3.12.0) - 2019-10-03
### Added
- Added missing German translation
- Added support for new `EditableBlockService`

### Changed
- Match PHPDoc with doctrine model
- Increased block type length to 255 chars

### Removed
- Removed superfluous PHPDoc
- Removed routing deprecations

## [3.11.1](https://github.com/sonata-project/SonataPageBundle/compare/3.11.0...3.11.1) - 2019-05-28

### Fixed
- compare with a different value type in the `treeAction`
- Fixed missing macro call in `breadcrumb.html.twig`

## [3.11.0](https://github.com/sonata-project/SonataPageBundle/compare/3.10.0...3.11.0) - 2019-14-17
### Added
- Add `|trans()` to `child.name|default(service.name)` in `compose_preview.html.twig`

### Changed
- Translate page name in breadcrumb if no title is defined

### Fixed
- Use page title in breadcrumbs
- Creating homepage from router config (check if page available in config - we
  will not create default Homepage).

## [3.10.0](https://github.com/sonata-project/SonataPageBundle/compare/3.9.1...3.10.0) - 2018-10-18

### Removed
- Removed CoreBundle deprecations
- support for php 5 and php 7.0

## [3.9.1](https://github.com/sonata-project/SonataPageBundle/compare/3.9.0...3.9.1) - 2018-11-04

### Added
- Added group icon to admin pages

### Fixed
- Catch empty locale in exception listener
- Make `sonata.page.kernel.exception_listener` service public

### Security
- Hide debug information in prod environment

## [3.9.0](https://github.com/sonata-project/SonataPageBundle/compare/3.8.0...3.9.0) - 2018-06-18
### Changed
- Auto-register datepicker form theme
- Force use breadcrumb translation strings for page admins

### Fixed
- Removed default value for parent association mappings
- `addChild` deprecations
- Only blocks with getBlockMetadata method will be shown in "add block of type" menu of Page Composer

### Added
- Added support for latest `sonata-project/cache`

## [3.8.0](https://github.com/sonata-project/SonataPageBundle/compare/3.7.1...3.8.0) - 2018-02-23
### Added
- added block title translation domain option
- added block icon option
- added block class option
- Added auto-registration sonata.page.router to cmf_routing.router service

### Changed
- Switch all templates references to Twig namespaced syntax
- Switch from templating service to sonata.templating
- Remove default template from exception list
- Use default template in page create template
- Added styling to page create button
- Allow Slugify ^3.0

### Fixed
- Replaced service names for field types by classnames.
- Commands not working on symfony4
- sonata.page.site.selector is public
- forward-compatibility with strict mode

### Removed
- Removed default title from blocks
- Removed old `sonata-` classes from templates
- Removed compatibility with older versions of FOSRestBundle (<2.1)

## [3.7.1](https://github.com/sonata-project/SonataPageBundle/compare/3.7.0...3.7.1) - 2018-01-07
### Changed
- The internal page name is not used as a seo title fallback anymore
- make services explicit public
 
### Fixed
- Fix for getRuntime on Symfony older than 3.4
- Fixed missing import
- Lowered page request listener to make sure it's triggered behind the firewall listener
- Fixed template choices in BlockAdmin

## [3.7.0](https://github.com/sonata-project/SonataPageBundle/compare/3.6.0...3.7.0) - 2017-12-12
### Added
- Added Russian translations
- Add Symfony 4 compatibility
- Added new configuration `skip_redirection` to skip asking Editor to redirect

### Changed
- make services explicit public

### Fixed
- compatibility with Twig 2.0 was improved
- Fixed wrong route in pagelist block

### Removed
- Removed old form alias usage

## [3.6.0](https://github.com/sonata-project/SonataPageBundle/compare/3.5.2...3.6.0) - 2017-11-14
### Fixed
- use new sf3 choices structure
- Pass form data instead of request object to form::submit
- Not working SEO page title
- read_only error for site selection in page admin
- Renamed internal method to fix sf2.8 incompatibility
- Fixed passing snapshot factory as wrong argument to `Sonata\PageBundle\Entity\SnapshotManager`
- Unused `no-confirmation` option for site create command

### Removed
- support for old versions of php and Symfony
- Removed php5 version checks

## [3.5.2](https://github.com/sonata-project/SonataPageBundle/compare/3.5.1...3.5.2) - 2017-09-14
### Changed
- Changed fallback translation domain to `SonataBlockBundle` in page composer

### Fixed
- Batch blocks removing doesn't mark page as edited
- Composer (JS): Relying of custom status-property; instead rely on Response Status Code
- use `configureSettings` instead of deprecated `setDefaultSettings`
- compatibility with Twig 2.0 was improved
- ``_self`` returns the template path instead of the template object
- Twig runtime error on Symfony < 3.2 and Twig 2.x
- Don't call Extension::addClassesToCompile() on php versions greater than 7

## [3.5.1](https://github.com/sonata-project/SonataPageBundle/compare/3.5.0...3.5.1) - 2017-07-05
### Fixed
- use FQCN for Symfony 3 for `type` in `PageAdmin`
- parent page select input no longer has flipped choices
- crash when running `sonata:page:clone-site`
- form typess are referenced by FQCN and not by name, which is no longer supported

## [3.5.0](https://github.com/sonata-project/SonataPageBundle/compare/3.4.1...3.5.0) - 2017-06-05
### Added
- added support for `FOSRestBundle:2.0`
- Added Italian translations

### Fixed
- Rendering failure when block.page property does not exist.
- Fixed hardcoded paths to classes in `.xml.skeleton` files of config
- Compatibility with Symfony 3 was fixed
- A deprecation warning regarding the usage of factories in the DIC was fixed
- deprecation warning about scope attributes
- deprecation error message about `addViolationAt`
- added support for both `QuestionHelper` and `DialogHelper` in `CreateSiteCommand` for Symfony 2.3 and 3.x compatibility
- fixed token manager compatibility in `PageAdminController`
- fixed the syntax change necessary for question helper as opposed to dialog helper

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
