Localized Routing
=================

The ``PageBundle`` supports Symfony's native localized routing with per-locale URL prefixes.
This allows you to have different URL structures for different languages while maintaining
strict locale isolation.

Overview
--------

With localized routing enabled, the bundle:

* Automatically partitions routes by locale suffix (e.g., ``app_home.en``, ``app_home.fi``)
* Enforces structural 404s for wrong-locale URLs (no redirects or fallbacks)
* Supports both CMS pages and Symfony routes with consistent locale prefixes
* Prevents route name conflicts (e.g., ``admin.dashboard`` vs locale ``dashboard``)

Configuration
-------------

Site Setup
~~~~~~~~~~

Configure your sites with locale-specific path prefixes::

    // In your database or fixtures
    $finnishSite = new Site();
    $finnishSite->setLocale('fi');
    $finnishSite->setRelativePath('');  // Finnish at root: /products
    $finnishSite->setHost('localhost');

    $englishSite = new Site();
    $englishSite->setLocale('en');
    $englishSite->setRelativePath('/en');  // English with prefix: /en/products
    $englishSite->setHost('localhost');

Route Definition
~~~~~~~~~~~~~~~~

Define your routes with locale suffixes:

.. code-block:: yaml

    # config/routes.yaml

    # Localized routes - note the .{locale} suffix
    app_home.fi:
        path: /
        controller: App\Controller\HomeController::index

    app_home.en:
        path: /en
        controller: App\Controller\HomeController::index

    app_products.fi:
        path: /tuotteet
        controller: App\Controller\ProductController::list

    app_products.en:
        path: /en/products
        controller: App\Controller\ProductController::list

    # Neutral routes (no locale) - accessible from all sites
    app_api:
        path: /api/data
        controller: App\Controller\ApiController::data

Alternatively, use Symfony's native localized route definition:

.. code-block:: yaml

    # config/routes.yaml

    app_home:
        path:
            fi: /
            en: /en
        controller: App\Controller\HomeController::index

    app_products:
        path:
            fi: /tuotteet
            en: /en/products
        controller: App\Controller\ProductController::list

.. note::

    Route names must follow the ``{base_name}.{locale}`` convention for the
    bundle to recognize them as localized routes.

How It Works
------------

Route Partitioning
~~~~~~~~~~~~~~~~~

The ``SiteAwareRouter`` automatically partitions your route collection:

1. **Localized routes** (with ``.{locale}`` suffix) are grouped by locale
2. **Neutral routes** (no suffix) are available to all locales
3. **Non-locale dotted routes** (e.g., ``admin.dashboard``) are treated as neutral

Only routes with suffixes matching enabled site locales are recognized as localized routes.

URL Generation
~~~~~~~~~~~~~

The router automatically selects the correct locale variant::

    // On Finnish site (locale: fi)
    $this->generateUrl('app_products');
    // Returns: /tuotteet (automatically resolves to app_products.fi)

    // On English site (locale: en)
    $this->generateUrl('app_products');
    // Returns: /en/products (automatically resolves to app_products.en)

    // Force a specific locale
    $this->generateUrl('app_products', ['_locale' => 'en']);
    // Returns: /en/products (even on Finnish site)

    // Neutral routes work on all sites
    $this->generateUrl('app_api');
    // Returns: /api/data (no locale prefix)

URL Matching
~~~~~~~~~~~

When a request comes in:

1. The current site's locale is determined from the request
2. Only routes for that locale (plus neutral routes) are available for matching
3. **Wrong-locale URLs return 404** (structural security)

.. code-block:: text

    Finnish site (locale: fi):
    ✓ /tuotteet          → matches app_products.fi
    ✓ /api/data          → matches app_api (neutral)
    ✗ /en/products       → 404 (English route not in Finnish matcher)

    English site (locale: en):
    ✓ /en/products       → matches app_products.en
    ✓ /api/data          → matches app_api (neutral)
    ✗ /tuotteet          → 404 (Finnish route not in English matcher)

CMS Pages
---------

CMS pages automatically respect locale prefixes:

.. code-block:: text

    Finnish site (locale: fi, relativePath: ''):
    CMS page URL: /about
    Final URL: /about

    English site (locale: en, relativePath: '/en'):
    CMS page URL: /about
    Final URL: /en/about

.. note::

    CMS pages are stored without locale prefixes. The prefix is added
    automatically based on the site's ``relativePath`` setting.

Hybrid Pages
-----------

Hybrid pages (CMS pages backed by Symfony routes) work with localized routing:

.. code-block:: bash

    # Generate hybrid pages for all sites
    bin/console sonata:page:update-core-routes --site=all

The generator automatically:

* Filters routes by locale (only creates hybrid pages for matching locales)
* Skips routes from other locales to prevent duplication
* Creates error pages (404, 500) for each site

Migration from Non-Localized Setup
----------------------------------

If you're migrating an existing site:

1. **Create localized route variants** for each locale:

.. code-block:: yaml

    # Before
    app_home:
        path: /

    # After
    app_home.fi:
        path: /

    app_home.en:
        path: /en

2. **Update site configuration** with ``relativePath``::

    $finnishSite->setRelativePath('');
    $englishSite->setRelativePath('/en');

3. **Update CMS pages** - no changes needed, prefixes are automatic

4. **Update templates** - no changes needed if using ``path()`` or ``url()``

.. code-block:: twig

    {# This works automatically with both old and new routes #}
    <a href="{{ path('app_home') }}">Home</a>

5. **Run page commands** to regenerate hybrid pages:

.. code-block:: bash

    bin/console sonata:page:update-core-routes --site=all
    bin/console sonata:page:create-snapshots --site=all

Best Practices
-------------

Route Naming
~~~~~~~~~~~

* Use descriptive base names: ``app_products``, ``blog_post_show``
* Always include locale suffix: ``.en``, ``.fi``, ``.sv``
* Avoid other dots in base names: use ``api_v2`` not ``api.v2``

Performance
~~~~~~~~~~

* Site locales are cached per-request (only 1 database query)
* Route collections are partitioned once per request
* Locale matchers/generators are built lazily

Security
~~~~~~~~

* Wrong-locale URLs **always return 404** (no information leakage)
* No automatic redirects between locales
* Each site only sees its own locale's routes

Troubleshooting
--------------

Routes not matching
~~~~~~~~~~~~~~~~~~

**Problem:** Routes with dots aren't working

**Solution:** Check if the route name follows the ``.{locale}`` convention. Routes
like ``admin.dashboard`` are treated as neutral routes, not locale routes. If this
is a localized route, rename it to ``admin_dashboard.en``.

Double locale prefix
~~~~~~~~~~~~~~~~~~~

**Problem:** URLs have double prefixes like ``/en/en/products``

**Solution:** Ensure your route paths already include the locale prefix. Don't add
it both in the route path and the site ``relativePath``.

.. code-block:: yaml

    # Correct
    app_products.en:
        path: /en/products  # Include /en in path

    # Site config
    $englishSite->setRelativePath('/en');  # Also in relativePath
    # Result: /en/products ✓

Hybrid pages duplicated
~~~~~~~~~~~~~~~~~~~~~~

**Problem:** Same hybrid page appears on multiple sites

**Solution:** Ensure hybrid pages have locale-specific route names:

.. code-block:: yaml

    app_shop.fi:
        path: /kauppa

    app_shop.en:
        path: /en/shop

Each site will only see its locale's variant.

Additional Resources
-------------------

* `Symfony Localized Routes Documentation <https://symfony.com/doc/current/routing.html#localized-routes-i18n>`_
* :doc:`multisite` - Multisite configuration guide
* :doc:`advanced_configuration` - Advanced routing options
