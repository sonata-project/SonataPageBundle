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

    [SonataPageBundle]
        git=http://github.com/sonata-project/SonataPageBundle.git
        target=/bundles/Sonata/PageBundle
        version=origin/2.0

After running ``php bin/vendors install``, be sure to enable the ``Page`` bundle
with all its dependencies in your application kernel:

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

.. note:: 
    If you do not have the ``Sonata`` namespace registered in your autoload,
    update the ``autoload.php`` to add it :

    .. code-block:: php

        <?php
        $loader->registerNamespaces(array(
            // ...
            'Sonata'       => __DIR__ . '/../vendor/bundles/',
            // ... other declarations
        ));

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

Don't forget to also add it in your autoload :

.. code-block:: php

    <?php
    $loader->registerNamespaces(array(
        // ...
        'Application'       => __DIR__ . '/../src/',
        // ... other declarations
    ));

And now, you're good to go !
