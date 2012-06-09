Advanced Configuration
======================

Full configuration options:

.. code-block:: yaml

    #
    # more information can be found here http://sonata-project.org/bundles/page
    #
    sonata_page:
        multisite: host # or host_with_path # the last one requires an altered app*.php file
        ignore_route_patterns:
            - ^/(.*)admin(.*)   # ignore admin route, ie route containing 'admin'
            - ^/_(.*)          # ignore symfony routes

        ignore_routes:
            - sonata_page_esi_cache
            - sonata_page_js_sync_cache
            - sonata_page_js_async_cache
            - sonata_page_apc_cache

        ignore_uri_patterns:
            - admin(.*)   # ignore admin route, ie route containing 'admin'

        cache_invalidation:
            service:  sonata.page.cache.invalidation.simple
            recorder: sonata.page.cache.recorder
            classes:
                "Application\Sonata\PageBundle\Entity\Block": getId

        default_template: default
        templates:
            default: { path: 'SonataPageBundle::layout.html.twig', name: 'default' }

        page_defaults:
            homepage: {decorate: false}

        caches:
            #esi:
            #    servers:
            #        - varnishadm -T 127.0.0.1:2000 {{ COMMAND }} "{{ EXPRESSION }}"


    # Enable Doctrine to map the provided entities
    doctrine:
        orm:
            entity_managers:
                default:
                    mappings:
                        ApplicationSonataPageBundle: ~
                        SonataPageBundle: ~
