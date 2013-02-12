Installation
============
Prerequisites
-------------
PHP 5.3 and Symfony 2 are needed to make this bundle work ; there are also some
Sonata dependencies that need to be installed and configured beforehand :

    - `SonataCacheBundle <http://sonata-project.org/bundles/cache>`_
    - `SonataBlockBundle <http://sonata-project.org/bundles/block>`_
    - `SonataSeoBundle <http://sonata-project.org/bundles/seo>`_
    - `SonataEasyExtendsBundle <http://sonata-project.org/bundles/easy-extends>`_
    - `SonataNotificationBundle <http://sonata-project.org/bundles/notification>`_
    - `SonataAdminBundle <http://sonata-project.org/bundles/admin>`_
    - `SonataDoctrineORMAdminBundle <http://sonata-project.org/bundles/doctrine-orm-admin>`_

You will need to install those in their 2.0 branches. Follow also their
configuration step ; you will find everything you need in their installation
chapter.

.. note::
    If a dependency is already installed somewhere in your project or in
    another dependency, you won't need to install it again.

Enable the Bundle
-----------------
Add the dependent bundles to the vendor/bundles directory. Add the following
lines to the file deps::

    php composer.phar require sonata-project/page-bundle --no-update
    php composer.phar require sonata-project/doctrine-orm-admin-bundle --no-update
    php composer.phar update

.. note::

    The SonataAdminBundle and SonataDoctrineORMAdminBundle must be installed, please refer to `the dedicated documentation for more information <http://sonata-project.org/bundles/admin>`_.

Next, be sure to enable the ``EasyExtends`` bundle in your application kernel:

.. code-block:: php

  <?php
  // app/appkernel.php
  public function registerbundles()
  {
      return array(
          // ...
          new Sonata\PageBundle\SonataPageBundle(),
          // ...
      );
  }

At this point, the bundle is not yet ready. You need to generate the correct
entities for the page::

    php app/console sonata:easy-extends:generate SonataPageBundle

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
            // Application Bundles
            new Application\Sonata\PageBundle\ApplicationSonataPageBundle(),

            // Vendor specifics bundles
            new Sonata\PageBundle\SonataPageBundle(),
            new Sonata\CacheBundle\SonataCacheBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Sonata\SeoBundle\SonataSeoBundle(),
            new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),

            new Symfony\Cmf\Bundle\RoutingExtraBundle\SymfonyCmfRoutingExtraBundle(),
        );
    }

Configuration
-------------
To use the ``PageBundle``, add the following lines to your application
configuration file.

.. note::
    If your ``auto_mapping`` have a ``false`` value, add these lines to your
    mapping configuration :

    .. code-block:: yaml

        # app/config/config.yml
        ApplicationSonataPageBundle: ~
        SonataPageBundle: ~

.. code-block:: yaml

    # app/config/config.yml
    symfony_cmf_routing_extra:
        chain:
            routers_by_id:
                # enable the DynamicRouter with high priority to allow overwriting configured routes with content
                #symfony_cmf_routing_extra.dynamic_router: 200
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
            - sonata_page_esi_cache
            - sonata_page_ssi_cache
            - sonata_page_js_sync_cache
            - sonata_page_js_async_cache
            - sonata_cache_esi
            - sonata_cache_ssi
            - sonata_cache_js_async
            - sonata_cache_js_sync
            - sonata_cache_apc

        ignore_uri_patterns:
            - ^/admin(.*)   # ignore admin route, ie route containing 'admin'

        page_defaults:
            homepage: {decorate: false} # disable decoration for homepage, key - is a page route

        default_template: default # template key from templates section, used as default for pages
        templates:
            default: {path: 'SonataPageBundle::layout.html.twig', name: default }

        # manage the http errors
        catch_exceptions:
            not_found: [404]    # render 404 page with "not_found" key (name generated: _page_internal_error_{key})
            fatal:     [500]    # so you can use the same page for different http errors or specify specific page for each error

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
At this point, the bundle is usuable, but not quite ready yet. You need to
generate the correct entities for the page::

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
