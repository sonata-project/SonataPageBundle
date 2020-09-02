UPGRADE 3.x
===========

UPGRADE FROM 3.x to 3.x
=======================

UPGRADE FROM 3.18.0 to 3.19.0
=============================

### SonataEasyExtends is deprecated

Registering `SonataEasyExtendsBundle` bundle is deprecated, it SHOULD NOT be registered.
Register `SonataDoctrineBundle` bundle instead.

### Support for NelmioApiDocBundle > 3.6 is added

Controllers for NelmioApiDocBundle v2 were moved under `Sonata\PageBundle\Controller\Api\Legacy\` namespace and controllers for NelmioApiDocBundle v3 were added as replacement. If you extend them, you must ensure they are using the corresponding inheritance.

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
