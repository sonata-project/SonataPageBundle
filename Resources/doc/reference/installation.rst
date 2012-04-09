Installation
============

To begin, add the dependent bundles to the vendor/bundles directory. Add the following lines to the file deps::

    [SonataCacheBundle]
        git=http://github.com/sonata-project/SonataCacheBundle.git
        target=/bundles/Sonata/CacheBundle

    [SonataBlockBundle]
        git=http://github.com/sonata-project/SonataBlockBundle.git
        target=/bundles/Sonata/BlockBundle

    [SonataPageBundle]
        git=http://github.com/sonata-project/SonataPageBundle.git
        target=/bundles/Sonata/PageBundle

    [SonataSeoBundle]
        git=http://github.com/sonata-project/SonataSeoBundle.git
        target=/bundles/Sonata/SeoBundle

    [SonataEasyExtendsBundle]
        git=http://github.com/sonata-project/SonataEasyExtendsBundle.git
        target=/bundles/Sonata/EasyExtendsBundle

    [SonataNotificationBundle]
        git=http://github.com/sonata-project/SonataNotificationBundle.git
        target=/bundles/Sonata/NotificationBundle

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
          new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
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
            new Sonata\NotificationBundle\SonataNotificationBundle(),
            new Sonata\SeoBundle\SonataSeoBundle(),
            new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
        );
    }

Update the ``autoload.php`` to add new namespaces:

.. code-block:: php

    <?php
    $loader->registerNamespaces(array(
        'Sonata'                             => __DIR__,
        'Application'                        => __DIR__,

        // ... other declarations
    ));

Then add these bundles in the config mapping definition:

.. code-block:: yaml

    # app/config/config.yml
    ApplicationSonataPageBundle: ~
    SonataPageBundle: ~

Configuration
-------------

To use the ``PageBundle``, add the following lines to your application configuration
file.

.. code-block:: yaml

    # app/config/config.yml
    sonata_page:
        multisite: host
        ignore_route_patterns:
            - ^(.*)admin(.*)   # ignore admin route, ie route containing 'admin'
            - ^_(.*)          # ignore symfony routes

        ignore_routes:
            - sonata_page_esi_cache
            - sonata_page_js_sync_cache
            - sonata_page_js_async_cache
            - sonata_page_apc_cache

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

    catchAll:
        pattern:  /{path}
        defaults: { _controller: SonataPageBundle:Page:catchAll }
        requirements:
            path: .*
