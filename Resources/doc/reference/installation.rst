Installation
============

Make sure you have a ``Sonata`` directory. If you don't, create it::

  mkdir src/Sonata

To begin, add the dependent bundles to the ``src/`` directory. If you're
using git, you can add them as submodules::

  git submodule add git@github.com:Sonata-project/PageBundle.git src/Sonata/PageBundle

  // dependency bundles
  git submodule add git@github.com:Sonata-project/AdminBundle.git src/Sonata/AdminBundle
  git submodule add git@github.com:Sonata-project/EasyExtendsBundle.git src/Sonata/EasyExtendsBundle


Next, be sure to enable the bundles in your application kernel:

.. code-block:: php

  // app/appkernel.php
  public function registerbundles()
  {
      return array(
          // ...
          new Sonata\MediaBundle\SonataPageBundle(),
          new Sonata\AdminBundle\SonataAdminBundle(),
          new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
          // ...
      );
  }

At this point, the bundle is not yet ready. You need to generate the correct
entities for the page::

    php app/console sonata:easy-extends:generate SonataPageBundle

.. note::

    The command will generate domain objects in an ``Application`` namespace.
    So you can point entities' associations to a global and common namespace.
    This will make Entities sharing very easier as your models are accessible
    through a global namespace. For instance the page will be
    ``Application\Sonata\PageBundle\Entity\Page``.

Now, add the new `Application` Bundle into the kernel

.. code-block:: php

  public function registerbundles()
  {
      return array(
          // Application Bundles
          new Application\Sonata\PageBundle\ApplicationSonataPageBundle(),

          // Vendor specifics bundles
          new Sonata\PageBundle\SonataPageBundle(),
          new Sonata\AdminBundle\SonataAdminBundle(),
          new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
      );
  }

Update the ``autoload.php`` to add a new namespaces :

.. code-block:: php

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

At the end of your routing file, add the following lines

.. code-block:: yaml

    catchAll:
        pattern:  /{path}
        defaults: { _controller: SonataPageBundle:Page:catchAll }
        requirements:
            path: .*
