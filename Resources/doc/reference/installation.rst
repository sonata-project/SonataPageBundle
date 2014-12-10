Installation
============

Prerequisites
-------------
PHP 5.3 and Symfony 2 are needed to make this bundle work ; there are also some
Sonata dependencies that need to be installed and configured beforehand:

    - `SonataCacheBundle <http://sonata-project.org/bundles/cache>`_
    - `SonataBlockBundle <http://sonata-project.org/bundles/block>`_
    - `SonataSeoBundle <http://sonata-project.org/bundles/seo>`_
    - `SonataEasyExtendsBundle <http://sonata-project.org/bundles/easy-extends>`_
    - `SonataNotificationBundle <http://sonata-project.org/bundles/notification>`_
    - `SonataAdminBundle <http://sonata-project.org/bundles/admin>`_
    - `SonataDoctrineORMAdminBundle <http://sonata-project.org/bundles/doctrine-orm-admin>`_

You will also need a SymfonyCmf Bundle to make the routing work depending on which Symfony version you are using:

    - `SymfonyCmfRoutingExtraBundle <https://github.com/symfony-cmf/RoutingExtraBundle>`_ for Symfony <2.3
    - `SymfonyCmfRoutingBundle <https://github.com/symfony-cmf/RoutingBundle>`_ for Symfony >=2.3

Follow also their configuration steps; you will find everything you need in their installation chapter.

.. note::
    If a dependency is already installed somewhere in your project or in
    another dependency, you won't need to install it again.

Enable the Bundle
-----------------
Add the dependant bundles to the vendor/bundles directory:

.. code-block:: bash

    php composer.phar require sonata-project/page-bundle --no-update
    php composer.phar require sonata-project/datagrid-bundle 2.2.*@dev --no-update #for SonataPageBundle > 2.3.6
    php composer.phar require sonata-project/doctrine-orm-admin-bundle --no-update
    php composer.phar update

.. note::

    The SonataAdminBundle and SonataDoctrineORMAdminBundle must be installed, please refer to `the dedicated documentation for more information <http://sonata-project.org/bundles/admin>`_.
    
    The `SonataDatagridBundle <https://github.com/sonata-project/SonataDatagridBundle>`_ must be added in ``composer.json`` for SonataPageBundle versions above 2.3.6

Next, be sure to enable the ``Page`` and ``EasyExtends`` bundles in your application kernel:

.. code-block:: php

    <?php
    // app/appkernel.php
    public function registerbundles()
    {
        return array(
            // ...
            new Sonata\PageBundle\SonataPageBundle(),
            new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
            // ...
        );
    }

Before we can go on with generating our Application files trough the ``EasyExtends`` bundle, we need to add some lines which we will override later (we need them now only for the following step):

.. code-block:: yaml

    sonata_page:
        slugify_method:   sonata.core.slugify.cocur # old BC value is sonata.core.slugify.native
        multisite:        host
        default_template: default # template key from templates section, used as default for
        templates:
            default:  { path: 'SonataPageBundle::layout.html.twig',          name: 'default' }
            2columns: { path: 'SonataPageBundle::2columns_layout.html.twig', name: '2 columns layout' }

        # Generates a snapshot when a page is saved (from the admin)
        direct_publication: false # or %kernel.debug% if you want to publish in dev mode (but not in prod)


Configuration
-------------
To use the ``PageBundle``, add the following lines to your application
configuration file.

.. note::
    If your ``auto_mapping`` have a ``false`` value, add these lines to your
    mapping configuration :

    .. code-block:: yaml

        # app/config/config.yml
        doctrine:
            orm:
                entity_managers:
                    default:
                        mappings:
                            ApplicationSonataPageBundle: ~ # only once the ApplicationSonataPageBundle is generated
                            SonataPageBundle: ~

.. code-block:: yaml

    # app/config/config.yml
    cmf_routing:
        chain:
            routers_by_id:
                # enable the DynamicRouter with high priority to allow overwriting configured routes with content
                #cmf_routing.dynamic_router: 200
                # enable the symfony default router with a lower priority
                sonata.page.router: 150
                router.default: 100

    sonata_page:
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
            default:  { path: 'SonataPageBundle::layout.html.twig',          name: 'default' }
            2columns: { path: 'SonataPageBundle::2columns_layout.html.twig', name: '2 columns layout' }

        # manage the http errors
        catch_exceptions:
            not_found: [404]    # render 404 page with "not_found" key (name generated: _page_internal_error_{key})
            fatal:     [500]    # so you can use the same page for different http errors or specify specific page for each error


    sonata_admin:
        assets:
            javascripts:
                - bundles/sonataadmin/vendor/jquery/dist/jquery.min.js
                - bundles/sonataadmin/vendor/jquery.scrollTo/jquery.scrollTo.js
                - bundles/sonataadmin/vendor/jqueryui/ui/minified/jquery-ui.min.js
                - bundles/sonataadmin/vendor/jqueryui/ui/minified/i18n/jquery-ui-i18n.min.js
                - bundles/sonatapage/sonata-page.back.min.js

            stylesheets:
                - bundles/sonataadmin/vendor/AdminLTE/css/font-awesome.min.css
                - bundles/sonataadmin/vendor/jqueryui/themes/flick/jquery-ui.min.css
                - bundles/sonatapage/sonata-page.back.min.css

Add block context manager:

.. code-block:: yaml

    # app/config/config.yml
    sonata_block:
        context_manager: sonata.page.block.context_manager

.. note::

    Please you need to use the context ``sonata_page_bundle`` in the SonataBlockBundle to add block into a Page.


Add json Doctrine type

.. code-block:: yaml

    # app/config/config.yml
    doctrine:
        dbal:
            types:
                json: Sonata\Doctrine\Types\JsonType

Add Roles

.. code-block:: yaml

    # app/config/security.yml
    security:
        role_hierarchy:
            ROLE_ADMIN: ROLE_USER
            ROLE_SUPER_ADMIN: [ROLE_USER, ROLE_SONATA_ADMIN, ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH, SONATA]

            SONATA:
                - ROLE_SONATA_PAGE_ADMIN_PAGE_EDIT # if you are not using acl then this line must be uncommented
                - ROLE_SONATA_PAGE_ADMIN_BLOCK_EDIT

If you have decided to customize your logout management (in particular if you have set ``invalidate_session`` to false), you might want to add this logout handler:

.. code-block:: yaml

    # app/config/security.yml
    security:
        #...
        firewalls:
            #...
            main: # replace with your firewall name
                #...
                logout:
                    #...
                    handlers: ['sonata.page.cms_manager_selector']

At the end of your routing file, add the following lines

.. code-block:: yaml

    # app/config/routing.yml
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

    php app/console sonata:easy-extends:generate SonataPageBundle

If you specify no parameter, the files are generated in app/Application/Sonata... but you can specify the path with --dest=src

.. note::

    The command will generate domain objects in an ``Application`` namespace.
    So you can point entities associations to a global and common namespace.
    This will make entities sharing very easily as your models are accessible
    through a global namespace. For instance the page will be
    ``Application\Sonata\PageBundle\Entity\Page``.

Now, add the new `Application` Bundle to the kernel

.. code-block:: php

    <?php
    public function registerbundles()
    {
        return array(
            // ...

            // Application Bundles
            new Application\Sonata\PageBundle\ApplicationSonataPageBundle(),

            // ...
        );
    }

And now, you're good to go !
