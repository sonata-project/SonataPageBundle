Multisite
=========

The ``PageBundle`` handles multisite out of the box. However due to some
limitation of the Symfony API, the multisite support is done around 4 strategies:

============================    ==========================================================================================================
Type                            Description
============================    ==========================================================================================================
**host**                        you can configure a site per host. This strategy works out of the box with no changes.
**host_by_locale**              same than host, but try to retrieve the site by the Accept-Language header of the HTTP request.
**host_with_path**              you can configure site per host and per path. This strategy requires some changes.
**host_with_path_by_locale**    same than host with path, but try to retrieve the site by the Accept-Language header of the HTTP request.
============================    ==========================================================================================================

Host Strategy
-------------

With this strategy it is possible to handle sites like :

* https://sonata-project.org
* http://sonata-project.com
* http://sonata-project.net

Configure the ``sonata_page`` section as:

.. code-block:: yaml

    # config/packages/sonata_page.yaml

    sonata_page:
        multisite: host

Host By Locale Strategy
-----------------------

This strategy handles the same sites than previous one.

Configure the ``sonata_page`` section as:

.. code-block:: yaml

    # config/packages/sonata_page.yaml

    sonata_page:
        multisite: host_by_locale

Host and Path Strategy
----------------------

With this strategy it is possible to handle sites like :

* https://sonata-project.org
* https://sonata-project.org/beta
* http://sonata-project.com/fr
* http://sonata-project.net

This strategy requires a dedicated runtime. So you need to modify your
composer.json and declare ``SonataPageRuntime`` runtime and the multisite type you want to use.

.. code-block:: json


        "extra": {
            "runtime": {
                "class": "Sonata\\PageBundle\\Runtime\\SonataPageRuntime",
                "multisite": "host_with_path"
            }



The last action is to configure the ``sonata_page`` section as:

.. code-block:: yaml

    # config/packages/sonata_page.yaml

    sonata_page:
        multisite: host_with_path

Host and Path By Locale Strategy
--------------------------------

This strategy requires a dedicated runtime. So you need to modify your
composer.json and declare ``SonataPageRuntime`` runtime and the multisite type you want to use.

.. code-block:: json


        "extra": {
            "runtime": {
                "class": "Sonata\\PageBundle\\Runtime\\SonataPageRuntime",
                "multisite": "host_with_path_by_locale"
            }


The last action is to configure the ``sonata_page`` section as:

.. code-block:: yaml

    # config/packages/sonata_page.yaml

    sonata_page:
        multisite: host_with_path_by_locale

.. note::

    If you have a working site with the PageBundle, you just need to create a
    ``Site`` and update the page table and the snapshot table with the correct
    Site ``id`` value.
