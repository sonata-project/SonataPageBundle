Error Pages
===========

The ``PageBundle`` can catch errors to render different error pages. For 
instance, the page not found exception can be administrated and rendered like 
any other pages.

The following configuration will create two pages (by running the 
``sonata:page:update-core-routes``).

.. code-block:: yaml

    catch_exceptions:
        not_found: [404]
        fatal:     [500]

The pages names will be ``_page_internal_error_not_found`` and 
``_page_internal_error_fatal``.

They are editable by using the front navigation menu : "See all errors" menu 
item.
