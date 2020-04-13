Installation
============

Prerequisites
-------------

PHP 7.1 and Symfony >=4.3 are needed to make this bundle work, there are
also some Sonata dependencies that need to be installed and configured beforehand:

    - SonataAdminBundle_
    - SonataDoctrineORMAdminBundle_
    - SonataBlockBundle_
    - SonataCacheBundle_
    - SonataSeoBundle_
    - SonataEasyExtendsBundle_
    - SonataNotificationBundle_

.. note::

    If a dependency is already installed somewhere in your project or in
    another dependency, you won't need to install it again.

Enable the Bundle
-----------------

Add the dependant bundles to the vendor/bundles directory:

.. code-block:: bash

    composer require sonata-project/page-bundle --no-update

    # for SonataPageBundle > 2.3.6
    composer require sonata-project/datagrid-bundle --no-update
    composer require sonata-project/doctrine-orm-admin-bundle --no-update

    # optional when using API
    composer require friendsofsymfony/rest-bundle  --no-update
    composer require nelmio/api-doc-bundle  --no-update

    composer update

Next, be sure to enable the bundles in your ``bundles.php`` file if they
are not already enabled::

    // config/bundles.php

    return [
        // ...
        Sonata\PageBundle\SonataPageBundle::class => ['all' => true],
        Sonata\EasyExtendsBundle\SonataEasyExtendsBundle::class => ['all' => true],
    ];

Configuration
-------------

Doctrine Configuration
~~~~~~~~~~~~~~~~~~~~~~

Add these bundles in the config mapping definition (or enable `auto_mapping`_):

.. code-block:: yaml

    # config/packages/doctrine.yaml

    doctrine:
        orm:
            entity_managers:
                default:
                    mappings:
                        ApplicationSonataPageBundle: ~ # only once the ApplicationSonataPageBundle is generated
                        SonataPageBundle: ~

        dbal:
            types:
                json: Sonata\Doctrine\Types\JsonType

CMF Routing Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~

``sonata.page.router`` service must be added to the index of ``cmf_routing.router`` chain router.

Configure ``symfony-cmf/routing-bundle``:

.. code-block:: yaml

    # config/packages/cmf_routing_bundle.yaml

    cmf_routing:
        chain:
            routers_by_id:
                # enable the DynamicRouter with high priority to allow overwriting configured routes with content
                #cmf_routing.dynamic_router: 200
                # enable the symfony default router with a lower priority
                sonata.page.router: 150
                router.default: 100

Or register ``sonata.page.router`` automatically:

.. code-block:: yaml

    # config/packages/sonata_page.yaml

    sonata_page:
        router_auto_register:
            enabled: true
            priority: 150

SonataPageBundle Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: yaml

    # config/packages/sonata_page.yaml

    sonata_page:
        slugify_service:   sonata.core.slugify.cocur # old BC value is sonata.core.slugify.native
        multisite: host
        use_streamed_response: true # set the value to false in debug mode or if the reverse proxy does not handle streamed response
        ignore_route_patterns:
            - ^(.*)admin(.*)   # ignore admin route, ie route containing 'admin'
            - ^_(.*)          # ignore symfony routes

        ignore_routes:
            - sonata_page_cache_esi
            - sonata_page_cache_ssi
            - sonata_page_js_sync_cache
            - sonata_page_js_async_cache
            - sonata_cache_esi
            - sonata_cache_ssi
            - sonata_cache_js_async
            - sonata_cache_js_sync
            - sonata_cache_apc

        ignore_uri_patterns:
            - ^/admin\/   # ignore admin route, ie route containing 'admin'

        page_defaults:
            homepage: {decorate: false} # disable decoration for homepage, key - is a page route

        default_template: default # template key from templates section, used as default for pages
        templates:
            default:  { path: '@SonataPage/layout.html.twig',          name: 'default' }
            2columns: { path: '@SonataPage/2columns_layout.html.twig', name: '2 columns layout' }

        direct_publication: false # or %kernel.debug% if you want to publish in dev mode (but not in prod)

        # manage the http errors
        catch_exceptions:
            not_found: [404]    # render 404 page with "not_found" key (name generated: _page_internal_error_{key})
            fatal:     [500]    # so you can use the same page for different http errors or specify specific page for each error

SonataAdminBundle Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: yaml

    # config/packages/sonata_admin.yaml

    sonata_admin:
        assets:
            extra_javascripts:
                - bundles/sonatapage/sonata-page.back.min.js
            extra_stylesheets:
                - bundles/sonatapage/sonata-page.back.min.css

SonataBlockBundle Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: yaml

    # config/packages/sonata_block.yaml

    sonata_block:
        context_manager: sonata.page.block.context_manager

.. note::

    Please you need to use the context ``sonata_page_bundle`` in the SonataBlockBundle to add block into a Page.

Security Configuration
~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: yaml

    # config/packages/security.yaml

    security:
        role_hierarchy:
            ROLE_ADMIN: ROLE_USER
            ROLE_SUPER_ADMIN: [ROLE_USER, ROLE_SONATA_ADMIN, ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH, SONATA]

            SONATA:
                - ROLE_SONATA_PAGE_ADMIN_PAGE_EDIT # if you are not using acl then this line must be uncommented
                - ROLE_SONATA_PAGE_ADMIN_BLOCK_EDIT

If you have decided to customize your logout management (in particular
if you have set ``invalidate_session`` to false), you might want to add
this logout handler:

.. code-block:: yaml

    # config/packages/security.yaml

    security:
        firewalls:
            main: # replace with your firewall name
                logout:
                    handlers: ['sonata.page.cms_manager_selector']

Routing Configuration
~~~~~~~~~~~~~~~~~~~~~

.. code-block:: yaml

    # config/routes.yaml

    sonata_page_exceptions:
        resource: '@SonataPageBundle/Resources/config/routing/exceptions.xml'
        prefix: /

    sonata_page_cache:
        resource: '@SonataPageBundle/Resources/config/routing/cache.xml'
        prefix: /

Extend the Bundle
-----------------

At this point, the bundle is usable, but not quite ready yet. You need to
generate the correct entities for the page:

.. code-block:: bash

    bin/console sonata:easy-extends:generate SonataPageBundle --dest=src --namespace_prefix=App

With provided parameters, the files are generated in ``src/Application/Sonata/PageBundle``.

.. note::

    The command will generate domain objects in an ``App\Application`` namespace.
    So you can point entities' associations to a global and common namespace.
    This will make Entities sharing easier as your models will allow to
    point to a global namespace. For instance the page will be
    ``App\Application\Sonata\PageBundle\Entity\Page``.

Now, add the new ``Application`` Bundle into the ``bundles.php``::

    // config/bundles.php

    return [
        // ...
        App\Application\Sonata\PageBundle\ApplicationSonataPageBundle::class => ['all' => true],
    ];

Configure SonataPageBundle to use the newly generated classes:

.. code-block:: yaml

    # config/packages/sonata_page.yaml

    sonata_page:
        class:
            page: App\Application\Sonata\PageBundle\Entity\Page # This is an optional value
            snapshot: App\Application\Sonata\PageBundle\Entity\Snapshot
            block: App\Application\Sonata\PageBundle\Entity\Block
            site: App\Application\Sonata\PageBundle\Entity\Site

The only thing left is to update your schema:

.. code-block:: bash

    bin/console doctrine:schema:update --force

.. _SonataAdminBundle: https://sonata-project.org/bundles/admin
.. _SonataDoctrineORMAdminBundle: https://sonata-project.org/bundles/doctrine-orm-admin
.. _SonataBlockBundle: https://sonata-project.org/bundles/block
.. _SonataCacheBundle: https://sonata-project.org/bundles/cache
.. _SonataSeoBundle: https://sonata-project.org/bundles/seo
.. _SonataEasyExtendsBundle: https://sonata-project.org/bundles/easy-extends
.. _SonataNotificationBundle: https://sonata-project.org/bundles/notification
.. _EasyExtendsBundle: https://sonata-project.org/bundles/easy-extends/master/doc/index.html
.. _SymfonyCmfRoutingBundle: https://github.com/symfony-cmf/RoutingBundle
.. _SymfonyCmfRoutingExtraBundle: https://github.com/symfony-cmf/RoutingExtraBundle
.. _auto_mapping: http://symfony.com/doc/2.0/reference/configuration/doctrine.html#configuration-overview
