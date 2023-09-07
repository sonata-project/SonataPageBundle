# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [4.6.0](https://github.com/sonata-project/SonataPageBundle/compare/4.5.0...4.6.0) - 2023-09-07
### Changed
- [[#1522](https://github.com/sonata-project/SonataPageBundle/pull/1522)] Uses Symfony Serializer to transform Page and Block Models into array for json ([@Hanmac](https://github.com/Hanmac))

### Deprecated
- [[#1522](https://github.com/sonata-project/SonataPageBundle/pull/1522)] Not implementing PageInterface::removeChild ([@Hanmac](https://github.com/Hanmac))
- [[#1522](https://github.com/sonata-project/SonataPageBundle/pull/1522)] Not implementing PageInterface::removeBlock ([@Hanmac](https://github.com/Hanmac))
- [[#1522](https://github.com/sonata-project/SonataPageBundle/pull/1522)] Not passing Serializer to Transformer as 5th argument ([@Hanmac](https://github.com/Hanmac))

## [4.5.0](https://github.com/sonata-project/SonataPageBundle/compare/4.4.0...4.5.0) - 2023-07-22
### Added
- [[#1704](https://github.com/sonata-project/SonataPageBundle/pull/1704)] Some missing Italian translations ([@gremo](https://github.com/gremo))

### Removed
- [[#1702](https://github.com/sonata-project/SonataPageBundle/pull/1702)] Removed twig form themes from SonataPageExtension ([@Hanmac](https://github.com/Hanmac))

## [4.4.0](https://github.com/sonata-project/SonataPageBundle/compare/4.3.0...4.4.0) - 2023-06-04
### Added
- [[#1696](https://github.com/sonata-project/SonataPageBundle/pull/1696)] Support for SonataBlockBundle 5.0 ([@jordisala1991](https://github.com/jordisala1991))

## [4.3.0](https://github.com/sonata-project/SonataPageBundle/compare/4.2.0...4.3.0) - 2023-05-14
### Added
- [[#1692](https://github.com/sonata-project/SonataPageBundle/pull/1692)] Support for `sonata-project/form-extensions` 2.0 ([@jordisala1991](https://github.com/jordisala1991))

## [4.2.0](https://github.com/sonata-project/SonataPageBundle/compare/4.1.1...4.2.0) - 2023-04-25
### Removed
- [[#1679](https://github.com/sonata-project/SonataPageBundle/pull/1679)] Support for Symfony 4.4 ([@jordisala1991](https://github.com/jordisala1991))
- [[#1679](https://github.com/sonata-project/SonataPageBundle/pull/1679)] Support for Twig 2 ([@jordisala1991](https://github.com/jordisala1991))

## [4.1.1](https://github.com/sonata-project/SonataPageBundle/compare/4.1.0...4.1.1) - 2023-04-12
### Fixed
- [[#1676](https://github.com/sonata-project/SonataPageBundle/pull/1676)] ORM schema mapping does not give validation errors ([@jordisala1991](https://github.com/jordisala1991))

## [4.1.0](https://github.com/sonata-project/SonataPageBundle/compare/4.0.0...4.1.0) - 2023-03-10
### Added
- [[#1660](https://github.com/sonata-project/SonataPageBundle/pull/1660)] Usage of list_action_button_content options and create publications icon ([@gremo](https://github.com/gremo))

### Fixed
- [[#1661](https://github.com/sonata-project/SonataPageBundle/pull/1661)] Page admin filter by page type. ([@gremo](https://github.com/gremo))

### Removed
- [[#1658](https://github.com/sonata-project/SonataPageBundle/pull/1658)] Support for PHP 7.4
- [[#1658](https://github.com/sonata-project/SonataPageBundle/pull/1658)] Support for Symfony 6.0 and 6.1
- [[#1658](https://github.com/sonata-project/SonataPageBundle/pull/1658)] Support for `doctrine/persistence` ^2.0.

## [4.0.0](https://github.com/sonata-project/SonataPageBundle/compare/4.0.0-alpha-1...4.0.0) - 2022-11-30
### Changed
- [[#1635](https://github.com/sonata-project/SonataPageBundle/pull/1635)] Changed default entity class values ([@VincentLanglet](https://github.com/VincentLanglet))

### Fixed
- [[#1636](https://github.com/sonata-project/SonataPageBundle/pull/1636)] Fixed `sonata:page:create-site` command's option in the doc ([@eerison](https://github.com/eerison))

## [4.0.0-alpha-1](https://github.com/sonata-project/SonataPageBundle/compare/3.x...4.0.0-alpha-1) - 2022-10-22
### Added
- [[#1583](https://github.com/sonata-project/SonataPageBundle/pull/1583)] Added `assets/css/frontend.scss` to use in site's page ([@eerison](https://github.com/eerison))
- [[#1623](https://github.com/sonata-project/SonataPageBundle/pull/1623)] Add ability to display and remove blocks that belongs to types that no longer exist on compose page. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1534](https://github.com/sonata-project/SonataPageBundle/pull/1534)] Add missing dependency to `symfony/intl`. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1530](https://github.com/sonata-project/SonataPageBundle/pull/1530)] Add typehints and return types instead of phpdoc on all places possible. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1520](https://github.com/sonata-project/SonataPageBundle/pull/1520)] Add support for `sonata-project/doctrine-extensions` ^2.0. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1520](https://github.com/sonata-project/SonataPageBundle/pull/1520)] Add support for `sonata-project/twig-extensions` ^2.0. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1491](https://github.com/sonata-project/SonataPageBundle/pull/1491)] Add support for Symfony 5 and 6. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1485](https://github.com/sonata-project/SonataPageBundle/pull/1485)] Add support for Sonata ^4. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1483](https://github.com/sonata-project/SonataPageBundle/pull/1483)] Add support for SonataBlockBundle ^4. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1483](https://github.com/sonata-project/SonataPageBundle/pull/1483)] Add support for SonataSeoBundle ^3. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1452](https://github.com/sonata-project/SonataPageBundle/pull/1452)] Add `PageManager::createWithDefaults()` to create Pages with some default values. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1464](https://github.com/sonata-project/SonataPageBundle/pull/1464)] Add support for PHP 8.1 ([@jordisala1991](https://github.com/jordisala1991))
- [[#1450](https://github.com/sonata-project/SonataPageBundle/pull/1450)] Add Twig ^3.0 compatibility. ([@jordisala1991](https://github.com/jordisala1991))

### Changed
- [[#1437](https://github.com/sonata-project/SonataPageBundle/pull/1437)] Updated ValidatorTest to use ConstraintValidatorTestCase ([@Hanmac](https://github.com/Hanmac))
- [[#1460](https://github.com/sonata-project/SonataPageBundle/pull/1460)] Changed `BaseCommand::class` to `Symfony\Command::class` ([@eerison](https://github.com/eerison))
- [[#1493](https://github.com/sonata-project/SonataPageBundle/pull/1493)] Change `SnapshotChildrenCollection` to be a lazy `Collection` from Doctrine ([@jordisala1991](https://github.com/jordisala1991))
- [[#1564](https://github.com/sonata-project/SonataPageBundle/pull/1564)] Changed `page` context to `sonata.page.block.breadcrumb` used in `src/Resources/views/layout.html.twig` file ([@eerison](https://github.com/eerison))
- [[#1559](https://github.com/sonata-project/SonataPageBundle/pull/1559)] Change frontend compilation system from Gulp to Webpack ([@jordisala1991](https://github.com/jordisala1991))
- [[#1559](https://github.com/sonata-project/SonataPageBundle/pull/1559)] Change final minified file to `app.js` and `app.css` ([@jordisala1991](https://github.com/jordisala1991))
- [[#1531](https://github.com/sonata-project/SonataPageBundle/pull/1531)] Change from xml configuration format to PHP, for the internal bundle configuration. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1528](https://github.com/sonata-project/SonataPageBundle/pull/1528)] `BlockAdmin` and its childs now inject their required dependencies through `__construct` instead of set methods. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1519](https://github.com/sonata-project/SonataPageBundle/pull/1519)] `PageAdmin` now injects its required dependencies through `__construct` instead of set methods. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1513](https://github.com/sonata-project/SonataPageBundle/pull/1513)] Replace usage of Container as a dependency by the actual dependencies of services. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1512](https://github.com/sonata-project/SonataPageBundle/pull/1512)] Adjust `PageInterface`, `SnapshotInterface` and `SiteInterface` to reflect all the methods available on the actual implementation. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1510](https://github.com/sonata-project/SonataPageBundle/pull/1510)] Changed visibility for most protected properties / methods to private, since classes are final. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1508](https://github.com/sonata-project/SonataPageBundle/pull/1508)] Changed from `\DateTime` to `\DateTimeInterface` on all the codebase. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1436](https://github.com/sonata-project/SonataPageBundle/pull/1436)] PageExtension doesn't need InitRuntimeInterface anymore ([@Hanmac](https://github.com/Hanmac))

### Fixed
- [[#1627](https://github.com/sonata-project/SonataPageBundle/pull/1627)] Fixed `snapshot` translation ([@eerison](https://github.com/eerison))
- [[#1624](https://github.com/sonata-project/SonataPageBundle/pull/1624)] Fix to not show blocks that are not meant to be editable. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1625](https://github.com/sonata-project/SonataPageBundle/pull/1625)] Fix delete button while editting blocks on compose page. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1607](https://github.com/sonata-project/SonataPageBundle/pull/1607)] Fix `customUrl` implementation. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1606](https://github.com/sonata-project/SonataPageBundle/pull/1606)] Fix `host_with_path` with site on a relative path of '/'. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1606](https://github.com/sonata-project/SonataPageBundle/pull/1606)] Fix `View Page` button on Page edit, when site has relative path of '/'. ([@jordisala1991](https://github.com/jordisala1991))

### Removed
- [[#1460](https://github.com/sonata-project/SonataPageBundle/pull/1460)] Removed `--site=all` option for the commands ([@eerison](https://github.com/eerison))
- [[#1460](https://github.com/sonata-project/SonataPageBundle/pull/1460)] Removed `DumpPageCommand::class` ([@eerison](https://github.com/eerison))
- [[#1460](https://github.com/sonata-project/SonataPageBundle/pull/1460)] Removed `RenderBlockCommand::class` ([@eerison](https://github.com/eerison))
- [[#1460](https://github.com/sonata-project/SonataPageBundle/pull/1460)] Removed `BaseCommand::class` ([@eerison](https://github.com/eerison))
- [[#1596](https://github.com/sonata-project/SonataPageBundle/pull/1596)] Removed `sonata.page.assets` option ([@eerison](https://github.com/eerison))
- [[#1627](https://github.com/sonata-project/SonataPageBundle/pull/1627)] Removed SonataAdmin translation files ([@eerison](https://github.com/eerison))
- [[#1594](https://github.com/sonata-project/SonataPageBundle/pull/1594)] Removed deprecate `page_stylesheets` block from `base_layout.html.twig` ([@eerison](https://github.com/eerison))
- [[#1594](https://github.com/sonata-project/SonataPageBundle/pull/1594)] Removed deprecate `page_javascripts` block from `base_layout.html.twig` ([@eerison](https://github.com/eerison))
- [[#1594](https://github.com/sonata-project/SonataPageBundle/pull/1594)] Removed deprecate `page_top_bar` block from `base_layout.html.twig` ([@eerison](https://github.com/eerison))
- [[#1594](https://github.com/sonata-project/SonataPageBundle/pull/1594)] Removed deprecate `page_container` block from `base_layout.html.twig` ([@eerison](https://github.com/eerison))
- [[#1594](https://github.com/sonata-project/SonataPageBundle/pull/1594)] Removed deprecate `page_asset_footer` block from `base_layout.html.twig` ([@eerison](https://github.com/eerison))
- [[#1574](https://github.com/sonata-project/SonataPageBundle/pull/1574)] Remove show route for block since blocks are meant to be edited through compose page. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1541](https://github.com/sonata-project/SonataPageBundle/pull/1541)] Remove unused `AjaxController`. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1541](https://github.com/sonata-project/SonataPageBundle/pull/1541)] Remove unused `BlockController`. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1524](https://github.com/sonata-project/SonataPageBundle/pull/1524)] Remove `BlockContextManager` service to use the default one from BlockBundle. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#1506](https://github.com/sonata-project/SonataPageBundle/pull/1506)] Remove `setSlugifyMethod` and `getSlugifyMethod` static methods from Page. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1473](https://github.com/sonata-project/SonataPageBundle/pull/1473)] Removed the command `MigrateBlockNameSettingCommand::class` ([@eerison](https://github.com/eerison))
- [[#1473](https://github.com/sonata-project/SonataPageBundle/pull/1473)] Removed the command `MigrateToJsonTypeCommand::class` ([@eerison](https://github.com/eerison))
- [[#1456](https://github.com/sonata-project/SonataPageBundle/pull/1456)] Remove integration with SonataNotificationBundle ([@jordisala1991](https://github.com/jordisala1991))
- [[#1449](https://github.com/sonata-project/SonataPageBundle/pull/1449)] Remove SonataCoreBundle related code. ([@jordisala1991](https://github.com/jordisala1991))
- [[#1430](https://github.com/sonata-project/SonataPageBundle/pull/1430)] Remove cache related code. ([@Hanmac](https://github.com/Hanmac))

## [3.29.0](https://github.com/sonata-project/SonataPageBundle/compare/3.28.0...3.29.0) - 2022-09-08
### Deprecated
- [[#1595](https://github.com/sonata-project/SonataPageBundle/pull/1595)] Deprecated `assets` option from sonata_page config file ([@eerison](https://github.com/eerison))

## [3.28.0](https://github.com/sonata-project/SonataPageBundle/compare/3.27.0...3.28.0) - 2022-08-15
### Deprecated
- [[#1511](https://github.com/sonata-project/SonataPageBundle/pull/1511)] Deprecate `slugify_service` configuration option. ([@jordisala1991](https://github.com/jordisala1991))

### Fixed
- [[#1536](https://github.com/sonata-project/SonataPageBundle/pull/1536)] SnapshotManager Cleanup Query if there are no Snapshots to keep ([@Hanmac](https://github.com/Hanmac))

## [3.27.0](https://github.com/sonata-project/SonataPageBundle/compare/3.26.0...3.27.0) - 2022-07-26
### Added
- [[#1418](https://github.com/sonata-project/SonataPageBundle/pull/1418)] Added `CreateSnapshotService` to create snapshots ([@eerison](https://github.com/eerison))
- [[#1418](https://github.com/sonata-project/SonataPageBundle/pull/1418)] Added `CreateSnapshotBySiteInterface` ([@eerison](https://github.com/eerison))
- [[#1418](https://github.com/sonata-project/SonataPageBundle/pull/1418)] Added `CreateSnapshotByPageInterface` ([@eerison](https://github.com/eerison))

### Changed
- [[#1446](https://github.com/sonata-project/SonataPageBundle/pull/1446)] Updated SnapshotManger to use QueryBuilder instead of native SQL ([@Hanmac](https://github.com/Hanmac))
- [[#1427](https://github.com/sonata-project/SonataPageBundle/pull/1427)] Make Cache dependency optional ([@Hanmac](https://github.com/Hanmac))

### Deprecated
- [[#1461](https://github.com/sonata-project/SonataPageBundle/pull/1461)] Deprecated `DumpPageCommand::class` ([@eerison](https://github.com/eerison))
- [[#1461](https://github.com/sonata-project/SonataPageBundle/pull/1461)] Deprecated `RenderBlockCommand::class` ([@eerison](https://github.com/eerison))
- [[#1459](https://github.com/sonata-project/SonataPageBundle/pull/1459)] Deprecated `Mustache:class` generator ([@eerison](https://github.com/eerison))
- [[#1434](https://github.com/sonata-project/SonataPageBundle/pull/1434)] Deprecated `CleanupSnapshotConsumer` class ([@eerison](https://github.com/eerison))
- [[#1434](https://github.com/sonata-project/SonataPageBundle/pull/1434)] Deprecated `CleanupSnapshotsConsumer` class ([@eerison](https://github.com/eerison))
- [[#1434](https://github.com/sonata-project/SonataPageBundle/pull/1434)] Deprecated `--mode` option for `sonata:page:cleanup-snapshots` ([@eerison](https://github.com/eerison))
- [[#1434](https://github.com/sonata-project/SonataPageBundle/pull/1434)] Deprecated `--base-console` option for `sonata:page:cleanup-snapshots` ([@eerison](https://github.com/eerison))
- [[#1434](https://github.com/sonata-project/SonataPageBundle/pull/1434)] Deprecated `--site=all` option  for `sonata:page:cleanup-snapshots` ([@eerison](https://github.com/eerison))
- [[#1434](https://github.com/sonata-project/SonataPageBundle/pull/1434)] Deprecated `--base-console` option  for `sonata:page:create-snapshots` ([@eerison](https://github.com/eerison))
- [[#1434](https://github.com/sonata-project/SonataPageBundle/pull/1434)] Deprecated `--site=all` option  for `sonata:page:create-snapshots` ([@eerison](https://github.com/eerison))
- [[#1434](https://github.com/sonata-project/SonataPageBundle/pull/1434)] Deprecated `--site=all` option  for `sonata:page:update-core-routes` ([@eerison](https://github.com/eerison))
- [[#1434](https://github.com/sonata-project/SonataPageBundle/pull/1434)] Deprecated `BaseCommand::class` ([@eerison](https://github.com/eerison))
- [[#1482](https://github.com/sonata-project/SonataPageBundle/pull/1482)] Deprecated target page code from `src/Command/CloneSiteCommand.php` ([@eerison](https://github.com/eerison))
- [[#1482](https://github.com/sonata-project/SonataPageBundle/pull/1482)] Deprecated target page code from `src/Entity/Transformer.php` ([@eerison](https://github.com/eerison))
- [[#1482](https://github.com/sonata-project/SonataPageBundle/pull/1482)] Deprecated `src/Model/Page::target` property ([@eerison](https://github.com/eerison))
- [[#1482](https://github.com/sonata-project/SonataPageBundle/pull/1482)] Deprecated `src/Model/Page::getTarget()` method ([@eerison](https://github.com/eerison))
- [[#1482](https://github.com/sonata-project/SonataPageBundle/pull/1482)] Deprecated `src/Model/Page::setTarget()` method ([@eerison](https://github.com/eerison))
- [[#1482](https://github.com/sonata-project/SonataPageBundle/pull/1482)] Deprecated `src/Model/PageInterface::getTarget()` method ([@eerison](https://github.com/eerison))
- [[#1482](https://github.com/sonata-project/SonataPageBundle/pull/1482)] Deprecated `src/Model/PageInterface::setTarget()` method ([@eerison](https://github.com/eerison))
- [[#1482](https://github.com/sonata-project/SonataPageBundle/pull/1482)] Deprecated `src/Model/Snapshot::target` property ([@eerison](https://github.com/eerison))
- [[#1482](https://github.com/sonata-project/SonataPageBundle/pull/1482)] Deprecated `src/Model/Snapshot::targetId` property ([@eerison](https://github.com/eerison))
- [[#1482](https://github.com/sonata-project/SonataPageBundle/pull/1482)] Deprecated `src/Model/Snapshot::getTarget()` method ([@eerison](https://github.com/eerison))
- [[#1482](https://github.com/sonata-project/SonataPageBundle/pull/1482)] Deprecated `src/Model/Snapshot::setTarget()` method ([@eerison](https://github.com/eerison))
- [[#1482](https://github.com/sonata-project/SonataPageBundle/pull/1482)] Deprecated `src/Model/Snapshot::getTargetId()` method ([@eerison](https://github.com/eerison))
- [[#1482](https://github.com/sonata-project/SonataPageBundle/pull/1482)] Deprecated `src/Model/Snapshot::setTargetId()` method ([@eerison](https://github.com/eerison))
- [[#1482](https://github.com/sonata-project/SonataPageBundle/pull/1482)] Deprecated `src/Model/SnapshotPageProxy::target` property ([@eerison](https://github.com/eerison))
- [[#1482](https://github.com/sonata-project/SonataPageBundle/pull/1482)] Deprecated `src/Model/SnapshotPageProxy::getTargetId()` method ([@eerison](https://github.com/eerison))
- [[#1482](https://github.com/sonata-project/SonataPageBundle/pull/1482)] Deprecated `src/Model/SnapshotPageProxy::setTargetId()` method ([@eerison](https://github.com/eerison))
- [[#1482](https://github.com/sonata-project/SonataPageBundle/pull/1482)] Deprecated `src/Page/PageServiceManager::createResponse()` method ([@eerison](https://github.com/eerison))
- [[#1476](https://github.com/sonata-project/SonataPageBundle/pull/1476)] Deprecated the command `MigrateToJsonTypeCommand::class` ([@eerison](https://github.com/eerison))
- [[#1476](https://github.com/sonata-project/SonataPageBundle/pull/1476)] Deprecated the command `MigrateBlockNameSettingCommand::class` ([@eerison](https://github.com/eerison))
- [[#1435](https://github.com/sonata-project/SonataPageBundle/pull/1435)] Deprecated `BlockEsiCache`, `BlockJsCache` and `BlockSsiCache`. ([@Hanmac](https://github.com/Hanmac))
- [[#1418](https://github.com/sonata-project/SonataPageBundle/pull/1418)] Deprecated `async` mode in `CreateSnapshotsCommand` ([@eerison](https://github.com/eerison))
- [[#1418](https://github.com/sonata-project/SonataPageBundle/pull/1418)] Deprecated `sonata.notification.backend` code into `SiteAdminController` ([@eerison](https://github.com/eerison))
- [[#1418](https://github.com/sonata-project/SonataPageBundle/pull/1418)] Deprecated `sonata.notification.backend` code into `PageAdminController` ([@eerison](https://github.com/eerison))
- [[#1418](https://github.com/sonata-project/SonataPageBundle/pull/1418)] Deprecated `sendMessage` and `$backend` property into the `CreateSnapshotAdminExtension` ([@eerison](https://github.com/eerison))

### Removed
- [[#1429](https://github.com/sonata-project/SonataPageBundle/pull/1429)] Remove `symfony/debug` dependency as it is deprecated. ([@Hanmac](https://github.com/Hanmac))

## [3.26.0](https://github.com/sonata-project/SonataPageBundle/compare/3.25.0...3.26.0) - 2021-12-28
### Added
- [[#1392](https://github.com/sonata-project/SonataPageBundle/pull/1392)] Add `SnapshotInterface::getId()` method ([@core23](https://github.com/core23))
- [[#1369](https://github.com/sonata-project/SonataPageBundle/pull/1369)] Added `Sonata\BlockBundle\Model\BlockManagerInterface` ([@core23](https://github.com/core23))

### Changed
- [[#1392](https://github.com/sonata-project/SonataPageBundle/pull/1392)] Mark all classes as final ([@core23](https://github.com/core23))

### Deprecated
- [[#1369](https://github.com/sonata-project/SonataPageBundle/pull/1369)] Deprecated `Sonata\BlockBundle\Model\BlockManagerInterface` usage ([@core23](https://github.com/core23))

## [3.25.0](https://github.com/sonata-project/SonataPageBundle/compare/3.24.0...3.25.0) - 2021-12-04
### Added
- [[#1382](https://github.com/sonata-project/SonataPageBundle/pull/1382)] Added PHPStan and Psalm. ([@jordisala1991](https://github.com/jordisala1991))

## [3.24.0](https://github.com/sonata-project/SonataPageBundle/compare/3.23.0...3.24.0) - 2021-11-06
### Deprecated
- [[#1365](https://github.com/sonata-project/SonataPageBundle/pull/1365)] Deprecated ReST API with FOSRest, Nelmio Api Docs and JMS Serializer. ([@wbloszyk](https://github.com/wbloszyk))

### Fixed
- [[#1377](https://github.com/sonata-project/SonataPageBundle/pull/1377)] Block validation in edit mode ([@VincentLanglet](https://github.com/VincentLanglet))

## [3.23.0](https://github.com/sonata-project/SonataPageBundle/compare/3.22.3...3.23.0) - 2021-08-22
### Added
- [[#1341](https://github.com/sonata-project/SonataPageBundle/pull/1341)] Support for `SonataCacheBundle` v3 ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#1341](https://github.com/sonata-project/SonataPageBundle/pull/1341)] Support for PHP 8 ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#1341](https://github.com/sonata-project/SonataPageBundle/pull/1341)] Some return type hint to `BlockEsiCache`, `BlockJsCache` and `BlockSsiCache` ([@VincentLanglet](https://github.com/VincentLanglet))

### Changed
- [[#1341](https://github.com/sonata-project/SonataPageBundle/pull/1341)] `BlockEsiCache::__construct()` signature ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#1341](https://github.com/sonata-project/SonataPageBundle/pull/1341)] `BlockSsiCache::__construct` signature ([@VincentLanglet](https://github.com/VincentLanglet))

### Removed
- [[#1341](https://github.com/sonata-project/SonataPageBundle/pull/1341)] Support for `SonataCacheBundle` v2 ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#1341](https://github.com/sonata-project/SonataPageBundle/pull/1341)] Support for `SonataCache` v1 ([@VincentLanglet](https://github.com/VincentLanglet))

## [3.22.3](https://github.com/sonata-project/SonataPageBundle/compare/3.22.2...3.22.3) - 2021-07-15
### Fixed
- [[#1323](https://github.com/sonata-project/SonataPageBundle/pull/1323)] Page composer block validation ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#1275](https://github.com/sonata-project/SonataPageBundle/pull/1275)] Ignore metadata setup on SiteSelector, not whole site detection. ([@BadPixxel](https://github.com/BadPixxel))

## [3.22.2](https://github.com/sonata-project/SonataPageBundle/compare/3.22.1...3.22.2) - 2021-05-26
### Fixed
- [[#1310](https://github.com/sonata-project/SonataPageBundle/pull/1310)] Fixed creating `sonata_page_admin` twig variable only when `SonataAdminBundle` is loaded. ([@franmomu](https://github.com/franmomu))

## [3.22.1](https://github.com/sonata-project/SonataPageBundle/compare/3.22.0...3.22.1) - 2021-05-18
### Fixed
- [[#1305](https://github.com/sonata-project/SonataPageBundle/pull/1305)] Do not load `api_form.xml` if `JMSSerializerBundle` is not installed ([@4c0n](https://github.com/4c0n))
- [[#1306](https://github.com/sonata-project/SonataPageBundle/pull/1306)] Added missing Dutch translation value. ([@4c0n](https://github.com/4c0n))

## [3.22.0](https://github.com/sonata-project/SonataPageBundle/compare/3.21.1...3.22.0) - 2021-04-19
### Added
- [[#1293](https://github.com/sonata-project/SonataPageBundle/pull/1293)] Added `CreateSnapshotAdminExtension::postRemove()` method in order to create a snapshot when a block is deleted. ([@gremo](https://github.com/gremo))
- [[#1297](https://github.com/sonata-project/SonataPageBundle/pull/1297)] Added `sonata_page_admin` Twig global variable which holds `sonata.page.admin.page` service ([@franmomu](https://github.com/franmomu))

### Fixed
- [[#1296](https://github.com/sonata-project/SonataPageBundle/pull/1296)] Fixed using deprecated Twig tags ([@franmomu](https://github.com/franmomu))
- [[#1296](https://github.com/sonata-project/SonataPageBundle/pull/1296)] Fixed references to deprecated `sonata.core.slugify.cocur` and changed them to `sonata.page.slugify.cocur` ([@franmomu](https://github.com/franmomu))
- [[#1295](https://github.com/sonata-project/SonataPageBundle/pull/1295)] Fixed deprecations using commands because of not returning `int` ([@franmomu](https://github.com/franmomu))

### Removed
- [[#1297](https://github.com/sonata-project/SonataPageBundle/pull/1297)] Removed deprecations from `sonata-project/admin-bundle` using `sonata_admin` Twig global variable ([@franmomu](https://github.com/franmomu))
- [[#1284](https://github.com/sonata-project/SonataPageBundle/pull/1284)] Remove admin deprecations ([@core23](https://github.com/core23))

## [3.21.1](https://github.com/sonata-project/SonataPageBundle/compare/3.21.0...3.21.1) - 2021-03-21
### Fixed
- [[#1283](https://github.com/sonata-project/SonataPageBundle/pull/1283)] Catch null errors when accessing request ([@core23](https://github.com/core23))
- [[#1283](https://github.com/sonata-project/SonataPageBundle/pull/1283)] Catch null errors when loading unknown block ([@core23](https://github.com/core23))

## [3.21.0](https://github.com/sonata-project/SonataPageBundle/compare/3.20.0...3.21.0) - 2021-02-15
### Added
- [[#1250](https://github.com/sonata-project/SonataPageBundle/pull/1250)] Added support for `doctrine/persistence` 2 ([@core23](https://github.com/core23))

### Changed
- [[#1230](https://github.com/sonata-project/SonataPageBundle/pull/1230)] Update Dutch translations ([@zghosts](https://github.com/zghosts))

## [3.20.0](https://github.com/sonata-project/SonataPageBundle/compare/3.19.0...3.20.0) - 2020-12-05
### Added
- [[#1202](https://github.com/sonata-project/SonataPageBundle/pull/1202)] Support for sonata-project/datagrid-bundle v3 ([@wbloszyk](https://github.com/wbloszyk))

### Changed
- [[#1209](https://github.com/sonata-project/SonataPageBundle/pull/1209)] Replace mentions of 'whitelist' with 'allowlist' ([@jlt10](https://github.com/jlt10))

### Fixed
- [[#1242](https://github.com/sonata-project/SonataPageBundle/pull/1242)] Newly created snapshot does not effect on all previous snapshots end dates only the last one ([@haivala](https://github.com/haivala))

## [3.19.0](https://github.com/sonata-project/SonataPageBundle/compare/3.18.0...3.19.0) - 2020-09-04
### Added
- [[#1175](https://github.com/sonata-project/SonataPageBundle/pull/1175)] Support for "friendsofsymfony/rest-bundle:^3.0" ([@wbloszyk](https://github.com/wbloszyk))
- [[#1173](https://github.com/sonata-project/SonataPageBundle/pull/1173)] Added public alias `Sonata\PageBundle\Controller\Api\BlockController` for `sonata.page.controller.api.block` service ([@wbloszyk](https://github.com/wbloszyk))
- [[#1173](https://github.com/sonata-project/SonataPageBundle/pull/1173)] Added public alias `Sonata\PageBundle\Controller\Api\PageController` for `sonata.page.controller.api.page` service ([@wbloszyk](https://github.com/wbloszyk))
- [[#1173](https://github.com/sonata-project/SonataPageBundle/pull/1173)] Added public alias `Sonata\PageBundle\Controller\Api\SiteController` for `sonata.page.controller.api.site` service ([@wbloszyk](https://github.com/wbloszyk))
- [[#1173](https://github.com/sonata-project/SonataPageBundle/pull/1173)] Added public alias `Sonata\PageBundle\Controller\Api\SnapshotController` for `sonata.page.controller.api.snapshot` service ([@wbloszyk](https://github.com/wbloszyk))

### Change
- [[#1175](https://github.com/sonata-project/SonataPageBundle/pull/1175)] Support for deprecated "rest" routing type in favor for xml ([@wbloszyk](https://github.com/wbloszyk))

### Changed
- [[#1162](https://github.com/sonata-project/SonataPageBundle/pull/1162)] SonataEasyExtendsBundle is now optional, using SonataDoctrineBundle is preferred ([@jordisala1991](https://github.com/jordisala1991))

### Deprecated
- [[#1162](https://github.com/sonata-project/SonataPageBundle/pull/1162)] Using SonataEasyExtendsBundle to add Doctrine mapping information ([@jordisala1991](https://github.com/jordisala1991))

### Fixed
- [[#1195](https://github.com/sonata-project/SonataPageBundle/pull/1195)] Fixed support for string model identifiers at Open API definitions ([@wbloszyk](https://github.com/wbloszyk))
- [[#1173](https://github.com/sonata-project/SonataPageBundle/pull/1173)] Fix RestFul API - `Class could not be determined for Controller identified` Error ([@wbloszyk](https://github.com/wbloszyk))
- [[#1170](https://github.com/sonata-project/SonataPageBundle/pull/1170)] Fix `Twig\Extra\String\StringExtension` optional auto-registration to avoid duplication `twig.extension` service ([@wbloszyk](https://github.com/wbloszyk))

### Removed
- [[#1195](https://github.com/sonata-project/SonataPageBundle/pull/1195)] Removed requirements that were only allowing integers for model identifiers at Open API definitions ([@wbloszyk](https://github.com/wbloszyk))

## [3.18.0](https://github.com/sonata-project/SonataPageBundle/compare/3.17.3...3.18.0) - 2020-06-29
### Added
- [[#1166](https://github.com/sonata-project/SonataPageBundle/pull/1166)] Added
  `twig/string-extra` dependency. ([@wbloszyk](https://github.com/wbloszyk))

### Changed
- [[#1166](https://github.com/sonata-project/SonataPageBundle/pull/1166)]
  Changed use of `truncate` filter with `u` filter.
([@wbloszyk](https://github.com/wbloszyk))

### Fixed
- [[#1156](https://github.com/sonata-project/SonataPageBundle/pull/1156)] Fix
  wrong root node ([@wbloszyk](https://github.com/wbloszyk))

### Removed
- [[#1156](https://github.com/sonata-project/SonataPageBundle/pull/1156)]
  Remove support for Symfony <4.4 ([@wbloszyk](https://github.com/wbloszyk))
- [[#1156](https://github.com/sonata-project/SonataPageBundle/pull/1156)]
  Remove SonataCoreBundle dependencies
([@wbloszyk](https://github.com/wbloszyk))

## [3.17.3](https://github.com/sonata-project/SonataPageBundle/compare/3.17.2...3.17.3) - 2020-06-22
### Fixed
- [[#1165](https://github.com/sonata-project/SonataPageBundle/pull/1165)] Fix
  mysql database schema ([@wbloszyk](https://github.com/wbloszyk))

### Removed
- [[#1165](https://github.com/sonata-project/SonataPageBundle/pull/1165)]
  Remove support for mssql database ([@wbloszyk](https://github.com/wbloszyk))

## [3.17.2](https://github.com/sonata-project/SonataPageBundle/compare/3.17.1...3.17.2) - 2020-05-20
### Fixed
- fixed sql to work with mssql
- Fix switch parent

## [3.17.1](https://github.com/sonata-project/SonataPageBundle/compare/3.17.0...3.17.1) - 2020-05-08
### Fixed
- Truncate texts in page composer
- Fix invalid html in page block
- Ignore subrequests in `SiteSelector`

## [3.17.0](https://github.com/sonata-project/SonataPageBundle/compare/3.16.0...3.17.0) - 2020-05-01
### Fixed
- Catch possible null error when retriving site
- Redirecting after batch snapshot
- Missing var type declaration

### Removed
- Support for Symfony < 4.3

## [3.16.0](https://github.com/sonata-project/SonataPageBundle/compare/3.15.1...3.16.0) - 2020-03-25
### Changed
- Removed underscores in page names when calling `sonata:page:update-core-route`

### Removed
- Dependency on `cocur/slugify`

## [3.15.1](https://github.com/sonata-project/SonataPageBundle/compare/3.15.0...3.15.1) - 2020-03-14
### Fixed
- Fix page actions for symfony 4

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
