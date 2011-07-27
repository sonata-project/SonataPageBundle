Advanced usage
==============


Ignore options
--------------

By default, the ``PageBundle`` decorates all pages. However it is possible to tweak
this behavior by ignoring patterns :

    - ``ignore_route_patterns`` : based on pattern
    - ``ignore_routes``         : based on the route name
    - ``ignore_uri_patterns``   : based on the an uri pattern

.. code-block:: yaml

    sonata_page:
        ignore_route_patterns:
            - /(.*)admin(.*)/   # ignore admin route, ie route containing 'admin'
            - /^_(.*)/          # ignore symfony routes

        ignore_routes:
            - sonata_page_esi_cache
            - sonata_page_js_cache

        ignore_uri_patterns:
            - /admin(.*)/     # ignore admin route, ie route containing 'admin'


Contextual Cache
----------------

// todo