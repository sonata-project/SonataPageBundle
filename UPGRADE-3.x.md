UPGRADE 3.x
===========

UPGRADE FROM 3.22 to 3.23
=========================

### Sonata Cache-bundle

`SonataCacheBundle` was upgrade from `2.x` to `3.x`. Because of the change in the `CacheAdapterInterface`,
technically there are few BC-break in this project:
- `BlockEsiCache` and `BlockSsiCache` constructor signatures have changed.
- Some methods of `BlockEsiCache`, `BlockJsCache` and `BlockSsiCache` have now return type hints.

If you override any of these class make sure you are explicitly declaring your dependency with
`sonata-project/cache-bundle` to your `composer.json` in order to avoid unwanted upgrades.

UPGRADE FROM 3.18.0 to 3.19.0
=============================

### SonataEasyExtends is deprecated

Registering `SonataEasyExtendsBundle` bundle is deprecated, it SHOULD NOT be registered.
Register `SonataDoctrineBundle` bundle instead.

UPGRADE FROM 3.3 to 3.4
=======================

### Unused command option

The `--all` option of `sonata:page:update-core-routes` command is not used and is now deprecated.

UPGRADE FROM 3.1 to 3.2
=======================

### Tests

All files under the ``Tests`` directory are now correctly handled as internal test classes.
You can't extend them anymore, because they are only loaded when running internal tests.
More information can be found in the [composer docs](https://getcomposer.org/doc/04-schema.md#autoload-dev).

### Deprecations

The ``SnapshotManager::getPageByName`` method is deprecated and will be removed with the next major release.
