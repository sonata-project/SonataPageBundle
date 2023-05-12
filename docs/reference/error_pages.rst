Error Pages
===========

The ``PageBundle`` can catch errors to render different error pages. For
instance, the page not found exception can be administrated and rendered like
any other pages.

The following configuration will create two pages when you execute:

.. code-block:: bash

    bin/console sonata:page:update-core-routes

.. code-block:: yaml

    # config/packages/sonata_page.yaml

    sonata_page:
        catch_exceptions:
            not_found: [404]
            fatal:     [500]

The page names will be ``_page_internal_error_not_found`` and
``_page_internal_error_fatal``.

They are editable by using the front navigation menu : "See all errors" menu
item.
