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
            - /(.*)admin(.*)/   # ignore admin route, ie route containing 'admin'
            - /^_(.*)/          # ignore symfony routes

        ignore_routes:
            - sonata_page_esi_cache
            - sonata_page_js_sync_cache
            - sonata_page_js_async_cache
            - sonata_page_apc_cache

        ignore_uri_patterns:
            - /admin(.*)/   # ignore admin route, ie route containing 'admin'

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

        services:
            sonata.page.block.text:
                cache: sonata.page.cache.noop
            sonata.page.block.action:
                cache: sonata.page.cache.noop
            sonata.page.block.container:
                cache: sonata.page.cache.noop
            sonata.page.block.children_pages:
                cache: sonata.page.cache.noop
            sonata.page.block.rss:
                cache: sonata.page.cache.noop

            # block from Media Bundle
            #sonata.media.block.media:
            #sonata.media.block.gallery:
            #sonata.media.block.feature_media:

        caches:
            #esi:
            #    servers:
            #        - varnishadm -T 127.0.0.1:2000 {{ COMMAND }} "{{ EXPRESSION }}"

            #mongo:
            #    database:   cache
            #    collection: cache
            #    servers:
            #        - {host: 127.0.0.1, port: 27017, user: username, password: pASS'}
            #        - {host: 127.0.0.2}

            #memcached:
            #    prefix: test     # prefix to ensure there is no clash between instances
            #    servers:
            #        - {host: 127.0.0.1, port: 11211, weight: 0}

            #apc:
            #    token:  s3cur3   # token used to clear the related cache
            #    prefix: test     # prefix to ensure there is no clash between instances
            #    servers:
            #        - { domain: kooqit.local, ip: 127.0.0.1, port: 80}

    # Enable Doctrine to map the provided entities
    doctrine:
        orm:
            entity_managers:
                default:
                    mappings:
                        ApplicationSonataPageBundle: ~
                        SonataPageBundle: ~