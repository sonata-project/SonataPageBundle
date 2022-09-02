Advanced Configuration
======================

More information can be found `here`_

Full configuration options:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/cmf_routing.yaml

        cmf_routing:
            chain:
                routers_by_id:
                    # enable the DynamicRouter with high priority to allow overwriting configured routes with content
                    #cmf_routing.dynamic_router: 200
                    # enable the symfony default router with a lower priority
                    sonata.page.router: 150
                    router.default: 100

    .. code-block:: yaml

        # config/packages/sonata_page.yaml

        # Default configuration for extension with alias: "sonata_page"
        sonata_page:
            skip_redirection: false # Skip asking Editor to redirect
            use_streamed_response: false
            multisite: ~ # Required
            ignore_route_patterns:
                # Defaults:
                - /(.*)admin(.*)/
                - /^_(.*)/
            ignore_uri_patterns:
                # Default:
                - /admin(.*)/
            default_page_service: sonata.page.service.default
            default_template: ~ # Required
            assets:
                stylesheets:
                    # Defaults:
                    - bundles/sonatapage/app.css
                javascripts:
                frontend_stylesheets:
                    # Defaults:
                    - bundles/sonatapage/frontend.css
                frontend_javascripts:
            templates: # Required
                # Prototype
                id:
                    name: ~
                    path: ~
                    inherits_containers: ~
                    containers:
                        # Prototype
                        id:
                            name: ~
                            shared: false
                            type: 1
                            blocks: []
                    matrix:
                        layout: ~ # Required
                        mapping: [] # Required
            templates_admin:
                list: "@SonataPage/PageAdmin/list.html.twig"
                tree: "@SonataPage/PageAdmin/tree.html.twig"
                compose: "@SonataPage/PageAdmin/compose.html.twig"
                compose_container_show: "@SonataPage/PageAdmin/compose_container_show.html.twig"
                select_site: "@SonataPage/PageAdmin/select_site.html.twig"
            page_defaults:
                # Prototype
                id:
                    decorate: true
                    enabled: true
            catch_exceptions:
                # Prototype
                id: ~
            class:
                page: App\Entity\SonataPagePage
                snapshot: App\Entity\SonataPageSnapshot
                block: App\Entity\SonataPageBlock
                site: App\Entity\SonataPageSite
            direct_publication: false

    .. code-block:: yaml

        # config/packages/doctrine.yaml

        # Enable Doctrine to map the provided entities
        doctrine:
            orm:
                entity_managers:
                    default:
                        mappings:
                            SonataPageBundle: ~

.. _`here`: https://docs.sonata-project.org/projects/SonataPageBundle/en/3.x/
