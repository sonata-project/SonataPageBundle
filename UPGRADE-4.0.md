UPGRADE FROM 3.x to 4.0
=======================

## Final classes

All classes that were marked as final in 3.x are now marked final in 4.0.

## Deprecations

All the deprecated code introduced on 3.x is removed on 4.0.

Please read [3.x](https://github.com/sonata-project/SonataPageBundle/tree/3.x) upgrade guides for more information.

See also the [diff code](https://github.com/sonata-project/SonataPageBundle/compare/3.x...4.0.0).

## Assets
Removed `assets.stylesheets` and `assets.javascripts` from sonata_page configuration

```diff
sonata_page:
-    assets:
-        stylesheets:
-            # Defaults:
-            - bundles/sonatapage/app.css
-        javascripts:
```

## Slugify Service

This config was removed from sonata page configuration, make sure that, you do not have this anymore into your configs.
```diff
- sonata_page:
-    slugify_service: sonata.page.slugify.cocur
```

## BlockContextManager

The class `Sonata\PageBundle\Block\BlockContextManager` was removed.

Please be aware the config
```diff
- sonata_block:
-        context_manager: sonata.page.block.context_manager 
```
won't work anymore then. You should rely on the default value instead.

## Route name and url changes

The following routes were changed:

* Remove non working `/view` route for Block Admin
* Rename compose preview url for Blocks from `compose_preview` to `compose-preview`
* Rename shared block route name from `block/shared` to `block_shared`

## Remove unused code

The following code has been removed since it is not used:

* `Sonata\PageBundle\Controller\AjaxController`
* `Sonata\PageBundle\Controller\BlockController`

## Migration to Webpack

Please check the src/Resources/public and the documentation to see the used CSS and JavaScript.

If you are customising (specially removing standard JavaScript or CSS) assets, this will affect you.
