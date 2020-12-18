UPGRADE 3.x
===========

UPGRADE FROM 3.x to 3.x
=======================

### SonataNotificationBundle is optional

All publishing commands are synchronous by default.

If you still want to use asynchronous processing, you should explicitly require `sonata-project/notification-bundle` and add the following configuration:

```yaml

sonata_page:
  publisher: "@Sonata\PageBundle\Publisher\NotificationPublisher"
```

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
