Installation
============

To begin, add the dependent bundles to the vendor/bundles directory. Add the following lines to the file deps::

    [SonataPageBundle]
        git=http://github.com/sonata-project/SonataPageBundle.git
        target=/bundles/Sonata/PageBundle

    [SonataEasyExtendsBundle]
        git=http://github.com/sonata-project/SonataEasyExtendsBundle.git
        target=/bundles/Sonata/EasyExtendsBundle

.. note::

    The SonataAdminBundle must be installed, please refer to `the dedicated documentation for more information <http://sonata-project.org/bundles/admin>`_.

Next, be sure to enable the bundles in your application kernel:

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
            new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
        );
    }

Update the ``autoload.php`` to add new namespaces :

.. code-block:: php

    <?php
    $loader->registerNamespaces(array(
        'Sonata'                             => __DIR__,
        'Application'                        => __DIR__,

        // ... other declarations
    ));

Then add these bundles in the config mapping definition :

.. code-block:: yaml

    # app/config/config.yml
    ApplicationSonataPageBundle: ~
    SonataPageBundle: ~

Configuration
-------------

To use the ``AdminBundle``, add the following to your application configuration
file.

.. code-block:: yaml

    # app/config/config.yml
    sonata_page:
        ignore_route_patterns:
            - /(.*)admin(.*)/   # ignore admin route, ie route containing 'admin'
            - /^_(.*)/          # ignore symfony routes

        ignore_routes:
            - sonata_page_esi_cache
            - sonata_page_js_cache

        ignore_uri_patterns:
            - /admin(.*)/   # ignore admin route, ie route containing 'admin'

        services:
            sonata.page.block.text:
                cache: sonata.page.cache.noop
                default_settings: {}

            sonata.page.block.action:
                cache: sonata.page.cache.noop
                default_settings: {}

            sonata.page.block.container:
                cache: sonata.page.cache.esi
                default_settings: {}

            sonata.page.block.children_page:
                cache: sonata.page.cache.noop
                default_settings: {}

        page_defaults:
            homepage: {decorate: false} # disable decoration for homepage, key - is a page route

        default_template: default # template key from templates section, used as default for pages
        templates:
            default: {default: true, path: 'SonataPageBundle::layout.html.twig', name: default }

        caches:
            sonata.page.cache.esi:
                servers:
                    - varnishadm -T 127.0.0.1:2000 {{ COMMAND }} "{{ EXPRESSION }}"

            sonata.page.cache.mongo:
                database:   cache
                collection: cache
                servers:
                    - '127.0.0.1:27017'
                    #- 'username:password@localhost:27017'

            sonata.page.cache.memcached:
                prefix: test     # prefix to ensure there is no clash between instances
                servers:
                    - [127.0.0.1, 11211, 0]

            sonata.page.cache.apc:
                token:  s3cur3   # token used to clear the related cache
                prefix: test     # prefix to ensure there is no clash between instances
                servers:
                    - { domain: kooqit.local, ip: 127.0.0.1, port: 80}

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

    catchAll:
        pattern:  /{path}
        defaults: { _controller: SonataPageBundle:Page:catchAll }
        requirements:
            path: .*


Final step

.. code-block:: console

    app/console sonata:page:update-core-routes
    app/console sonata:page:create-snapshots