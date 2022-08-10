UPGRADE FROM 3.x to 4.0
=======================

## Final classes

All classes that were marked as final in 3.x are now marked final in 4.0.

## Deprecations

All the deprecated code introduced on 3.x is removed on 4.0.

Please read [3.x](https://github.com/sonata-project/SonataPageBundle/tree/3.x) upgrade guides for more information.

See also the [diff code](https://github.com/sonata-project/SonataPageBundle/compare/3.x...4.0.0).

## BlockContextManager

The class `Sonata\PageBundle\Block\BlockContextManager` was removed.

Please be aware the config
```
sonata_block:
        context_manager: sonata.page.block.context_manager
```
won't work anymore then. You should rely on the default value instead.
