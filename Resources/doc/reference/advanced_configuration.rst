Advanced Configuration
======================

Full configuration options (config.yml file):

.. code-block:: yaml

    #
    # more information can be found here http://sonata-project.org/bundles/page
    #
    symfony_cmf_routing_extra:
        chain:
            routers_by_id:
                # enable the DynamicRouter with high priority to allow overwriting configured routes with content
                #symfony_cmf_routing_extra.dynamic_router: 200
                # enable the symfony default router with a lower priority
                sonata.page.router: 150
                router.default: 100

    sonata_page:
        multisite: host # or host_with_path # the last one requires an altered app*.php file
        use_streamed_response: true # set the value to false in debug mode or if the reverse proxy does not handle streamed response
        ignore_route_patterns:
            - ^(.*)admin(.*)   # ignore admin route, ie route containing 'admin'
            - ^_(.*)           # ignore symfony routes

        # Generates a snapshot when a page is saved (from the admin)
        direct_publication: false # or %kernel.debug% if you want to publish in dev mode (but not in prod)
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

        cache_invalidation:
            service:  sonata.page.cache.invalidation.simple
            recorder: sonata.page.cache.recorder
            classes:
                "Application\Sonata\PageBundle\Entity\Block": getId

        default_template: default
        templates:
            default: { path: 'SonataPageBundle::layout.html.twig', name: 'default' }

        # Assets loaded by default in template
        assets:
            stylesheets:
                # Defaults:
                - bundles/sonataadmin/vendor/bootstrap/dist/css/bootstrap.min.css
                - bundles/sonatapage/sonata-page.front.min.css
            javascripts:
                # Defaults:
                - bundles/sonataadmin/vendor/jquery/dist/jquery.min.js
                - bundles/sonataadmin/vendor/bootstrap/dist/js/bootstrap.min.js
                - bundles/sonatapage/sonata-page.front.min.js

        page_defaults:
            homepage: {decorate: false}

        caches:
            ssi:
                token: an unique key # if not set a random value will be used

            esi:
                servers:
                    - varnishadm -T 127.0.0.1:2000 {{ COMMAND }} "{{ EXPRESSION }}"

        is_inline_edition_on: false # set to true to keep the old behavior. the feature will be deleted in futur versions

    # Enable Doctrine to map the provided entities
    doctrine:
        orm:
            entity_managers:
                default:
                    mappings:
                        ApplicationSonataPageBundle: ~
                        SonataPageBundle: ~
