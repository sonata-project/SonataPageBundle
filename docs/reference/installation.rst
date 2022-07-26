.. index::
    single: Installation
    single: Configuration

Installation
============

Prerequisites
-------------

There are some Sonata dependencies that need to be installed and configured beforehand.

Required dependencies:

* `SonataAdminBundle <https://docs.sonata-project.org/projects/SonataAdminBundle/en/3.x/>`_
* `SonataBlockBundle_ <https://docs.sonata-project.org/projects/SonataBlockBundle/en/3.x/>`_
* `SonataSeoBundle_ <https://docs.sonata-project.org/projects/SonataSeoBundle/en/2.x/>`_

And the persistence bundle (currently, not all the implementations of the Sonata persistence bundles are available):

* `SonataDoctrineOrmAdminBundle <https://docs.sonata-project.org/projects/SonataDoctrineORMAdminBundle/en/3.x/>`_

Follow also their configuration step; you will find everything you need in
their own installation chapter.

.. note::

    If a dependency is already installed somewhere in your project or in
    another dependency, you won't need to install it again.

Enable the Bundle
-----------------

Add ``SonataPageBundle`` via composer::

    composer require sonata-project/page-bundle

.. note::

    This will install the SymfonyCmfRoutingBundle_, too.

Next, be sure to enable the bundles in your ``config/bundles.php`` file if they
are not already enabled::

    // config/bundles.php

    return [
        // ...
        Sonata\PageBundle\SonataPageBundle::class => ['all' => true],
    ];

Configuration
=============

CMF Routing Configuration
-------------------------

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
------------------------------

.. code-block:: yaml

    # config/packages/sonata_page.yaml

    sonata_page:
        slugify_service: sonata.page.slugify.cocur
        multisite: host
        use_streamed_response: true # set the value to false in debug mode or if the reverse proxy does not handle streamed response
        ignore_route_patterns:
            - ^(.*)admin(.*) # ignore admin route, ie route containing 'admin'
            - ^_(.*) # ignore symfony routes

        class:
            page: App\Entity\SonataPagePage
            snapshot: App\Entity\SonataPageSnapshot
            block: App\Entity\SonataPageBlock
            site: App\Entity\SonataPageSite

        ignore_uri_patterns:
            - ^/admin\/ # ignore admin route, ie route containing 'admin'

        page_defaults:
            homepage: {decorate: false} # disable decoration for homepage, key - is a page route

        default_template: default # template key from templates section, used as default for pages
        templates:
            default: { path: '@SonataPage/layout.html.twig', name: 'default' }
            2columns: { path: '@SonataPage/2columns_layout.html.twig', name: '2 columns layout' }

        direct_publication: false # or %kernel.debug% if you want to publish in dev mode (but not in prod)

        # manage the http errors
        catch_exceptions:
            not_found: [404] # render 404 page with "not_found" key (name generated: _page_internal_error_{key})
            fatal: [500] # so you can use the same page for different http errors or specify specific page for each error

SonataAdminBundle Configuration
-------------------------------

.. code-block:: yaml

    # config/packages/sonata_admin.yaml

    sonata_admin:
        assets:
            extra_javascripts:
                - bundles/sonatapage/sonata-page.back.min.js
            extra_stylesheets:
                - bundles/sonatapage/sonata-page.back.min.css

SonataBlockBundle Configuration
-------------------------------

.. code-block:: yaml

    # config/packages/sonata_block.yaml

    sonata_block:
        context_manager: sonata.page.block.context_manager

.. note::

    Please you need to use the context ``sonata_page_bundle`` in the SonataBlockBundle to add block into a Page.

Security Configuration
----------------------

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
---------------------

.. code-block:: yaml

    # config/routes.yaml

    sonata_page_exceptions:
        resource: '@SonataPageBundle/Resources/config/routing/exceptions.xml'
        prefix: /

Doctrine ORM Configuration
--------------------------

And these in the config mapping definition (or enable `auto_mapping`_)::

    # config/packages/doctrine.yaml

    doctrine:
        orm:
            entity_managers:
                default:
                    mappings:
                        SonataPageBundle: ~

And then create the corresponding entities, ``src/Entity/SonataPageBlock``::

    // src/Entity/SonataPageBlock.php

    use Doctrine\ORM\Mapping as ORM;
    use Sonata\PageBundle\Entity\BaseBlock;

    /**
     * @ORM\Entity
     * @ORM\Table(name="page__block")
     */
    class SonataPageBlock extends BaseBlock
    {
        /**
         * @ORM\Id
         * @ORM\GeneratedValue
         * @ORM\Column(type="integer")
         */
        protected $id;
    }

``src/Entity/SonataPagePage``::

    // src/Entity/SonataPagePage.php

    use Doctrine\ORM\Mapping as ORM;
    use Sonata\PageBundle\Entity\BasePage;

    /**
     * @ORM\Entity
     * @ORM\Table(name="page__page")
     */
    class SonataPagePage extends BasePage
    {
        /**
         * @ORM\Id
         * @ORM\GeneratedValue
         * @ORM\Column(type="integer")
         */
        protected $id;
    }

``src/Entity/SonataPageSite``::

    // src/Entity/SonataPageSite.php

    use Doctrine\ORM\Mapping as ORM;
    use Sonata\PageBundle\Entity\BaseSite;

    /**
     * @ORM\Entity
     * @ORM\Table(name="page__site")
     */
    class SonataPageSite extends BaseSite
    {
        /**
         * @ORM\Id
         * @ORM\GeneratedValue
         * @ORM\Column(type="integer")
         */
        protected $id;
    }

and ``src/Entity/SonataPageSnapshot``::

    // src/Entity/SonataPageSnapshot.php

    use Doctrine\ORM\Mapping as ORM;
    use Sonata\PageBundle\Entity\BaseSnapshot;

    /**
     * @ORM\Entity
     * @ORM\Table(name="page__snapshot")
     */
    class SonataPageSnapshot extends BaseSnapshot
    {
        /**
         * @ORM\Id
         * @ORM\GeneratedValue
         * @ORM\Column(type="integer")
         */
        protected $id;
    }

The only thing left is to update your schema::

    bin/console doctrine:schema:update --force

Next Steps
----------

At this point, your Symfony installation should be fully functional, without errors
showing up from SonataPageBundle. If, at this point or during the installation,
you come across any errors, don't panic:

    - Read the error message carefully. Try to find out exactly which bundle is causing the error.
      Is it SonataPageBundle or one of the dependencies?
    - Make sure you followed all the instructions correctly, for both SonataPageBundle and its dependencies.
    - Still no luck? Try checking the project's `open issues on GitHub`_.

.. _`open issues on GitHub`: https://github.com/sonata-project/SonataPageBundle/issues
.. _SymfonyCmfRoutingBundle: https://github.com/symfony-cmf/RoutingBundle
.. _auto_mapping: http://symfony.com/doc/2.0/reference/configuration/doctrine.html#configuration-overview
