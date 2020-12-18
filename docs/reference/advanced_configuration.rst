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
            is_inline_edition_on:  false
            use_streamed_response:  false
            multisite:            ~ # Required
            ignore_route_patterns:

                # Defaults:
                - /(.*)admin(.*)/
                - /^_(.*)/
            slugify_service:      sonata.core.slugify.native
            ignore_routes:

                # Defaults:
                - sonata_page_cache_esi
                - sonata_page_cache_ssi
                - sonata_page_js_sync_cache
                - sonata_page_js_async_cache
                - sonata_cache_esi
                - sonata_cache_js_async
                - sonata_cache_js_sync
                - sonata_cache_apc
            ignore_uri_patterns:

                # Default:
                - /admin(.*)/
            cache_invalidation:
                service:              sonata.cache.invalidation.simple
                recorder:             sonata.cache.recorder
                classes:

                    # Prototype
                    id:                   ~
            default_page_service:  sonata.page.service.default
            default_template:     ~ # Required
            assets:
                stylesheets:

                    # Defaults:
                    - bundles/sonatacore/vendor/bootstrap/dist/css/bootstrap.min.css
                    - bundles/sonatapage/sonata-page.front.css
                javascripts:

                    # Defaults:
                    - bundles/sonatacore/vendor/jquery/dist/jquery.min.js
                    - bundles/sonatacore/vendor/bootstrap/dist/js/bootstrap.min.js
                    - bundles/sonatapage/sonata-page.front.js
            templates:            # Required

                # Prototype
                id:
                    name:                 ~
                    path:                 ~
                    inherits_containers:  ~
                    containers:

                        # Prototype
                        id:
                            name:                 ~
                            shared:               false
                            type:                 1
                            blocks:               []
                    matrix:
                        layout:               ~ # Required
                        mapping:              [] # Required
            templates_admin:
                list:                       "@SonataPage/PageAdmin/list.html.twig"
                tree:                       "@SonataPage/PageAdmin/tree.html.twig"
                compose:                    "@SonataPage/PageAdmin/compose.html.twig"
                compose_container_show:     "@SonataPage/PageAdmin/compose_container_show.html.twig"
                select_site:                "@SonataPage/PageAdmin/select_site.html.twig"
            page_defaults:

                # Prototype
                id:
                    decorate:             true
                    enabled:              true
            caches:
                esi:
                    token:                4b8fa46a0a00d0297e0b39b71aaeaa56cc2c40e3083642a720f940e9cf4ee718
                    version:              2
                    servers:              []
                ssi:
                    token:                adcd02dc23d9da234436d44b1ec58d147f86db2a08b94b872d969ce48687c386
            catch_exceptions:

                # Prototype
                id:                   ~
            class:
                page: App\Entity\SonataPagePage
                snapshot: App\Entity\SonataPageSnapshot
                block: App\Entity\SonataPageBlock
                site: App\Entity\SonataPageSite
            direct_publication: false

            publisher: "@Sonata\\PageBundle\\Publisher\\SimplePublisher"

    .. code-block:: yaml

        # config/packages/doctrine.yaml

        # Enable Doctrine to map the provided entities
        doctrine:
            orm:
                entity_managers:
                    default:
                        mappings:
                            SonataPageBundle: ~

.. _`here`: https://sonata-project.org/bundles/page
