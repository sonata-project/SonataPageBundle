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

Just configure the ``sonata_page`` section as:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml

        sonata_page:
            multisite: host

And that's it!

Host By Locale Strategy
-----------------------

This strategy handles the same sites than previous one.

Just configure the ``sonata_page`` section as:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml

        sonata_page:
            multisite: host_by_locale

And that's it!

Host and Path Strategy
----------------------

With this strategy it is possible to handle sites like :

* https://sonata-project.org
* https://sonata-project.org/beta
* http://sonata-project.com/fr
* http://sonata-project.net

This strategy requires a dedicated ``RequestFactory`` object. So you need to alter the
front controller to use the one provided by the ``PageBundle``. To do so, open
files: ``app.php`` and ``app_dev.php`` and change the following parts.

Working file examples:

.. code-block:: php

    <?php
    // web/app.php

    use Sonata\PageBundle\Request\RequestFactory; // before: use Symfony\Component\HttpFoundation\Request;

    $loader = require_once __DIR__.'/../app/bootstrap.php.cache';

    require_once __DIR__.'/../app/AppKernel.php';

    $kernel = new AppKernel('prod', false);
    $kernel->loadClassCache();

    $request = RequestFactory::createFromGlobals('host_with_path'); // before: $request = Request::createFromGlobals();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);

.. code-block:: php

    <?php
    // web/app_dev.php

    use Sonata\PageBundle\Request\RequestFactory; // before: use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Debug\Debug;

    // If you don't want to setup permissions the proper way, just uncomment the following PHP line
    // read http://symfony.com/doc/current/book/installation.html#configuration-and-setup for more information
    //umask(0000);

    // This check prevents access to debug front controllers that are deployed by accident to production servers.
    // Feel free to remove this, extend it, or make something more sophisticated.
    if (isset($_SERVER['HTTP_CLIENT_IP'])
        || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
        || !(in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', 'fe80::1', '::1')) || php_sapi_name() === 'cli-server')
    ) {
        header('HTTP/1.0 403 Forbidden');
        exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
    }

    $loader = require_once __DIR__.'/../app/bootstrap.php.cache';
    Debug::enable();

    require_once __DIR__.'/../app/AppKernel.php';

    $kernel = new AppKernel('dev', true);
    $kernel->loadClassCache();
    $request = RequestFactory::createFromGlobals('host_with_path'); // before: $request = Request::createFromGlobals();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);

.. note::

    If you use ``app_test.php`` and/or ``app_*.php`` don't forget to modify these files, too!

The last action is to configure the ``sonata_page`` section as:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml

        sonata_page:
            multisite: host_with_path

And that's it!

Host and Path By Locale Strategy
--------------------------------

This strategy requires a dedicated ``RequestFactory`` object. So you need to alter the
front controller to use the one provided by the ``PageBundle``. To do so, open
files: ``app.php`` and ``app_dev.php`` and change the following parts.

Working file examples:

.. code-block:: php

    <?php
    // web/app.php

    use Sonata\PageBundle\Request\RequestFactory; // before: use Symfony\Component\HttpFoundation\Request;

    $loader = require_once __DIR__.'/../app/bootstrap.php.cache';

    require_once __DIR__.'/../app/AppKernel.php';

    $kernel = new AppKernel('prod', false);
    $kernel->loadClassCache();

    $request = RequestFactory::createFromGlobals('host_with_path_by_locale'); // before: $request = Request::createFromGlobals();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);

.. code-block:: php

    <?php
    // web/app_dev.php

    use Sonata\PageBundle\Request\RequestFactory; // before: use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Debug\Debug;

    // If you don't want to setup permissions the proper way, just uncomment the following PHP line
    // read http://symfony.com/doc/current/book/installation.html#configuration-and-setup for more information
    //umask(0000);

    // This check prevents access to debug front controllers that are deployed by accident to production servers.
    // Feel free to remove this, extend it, or make something more sophisticated.
    if (isset($_SERVER['HTTP_CLIENT_IP'])
        || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
        || !(in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', 'fe80::1', '::1')) || php_sapi_name() === 'cli-server')
    ) {
        header('HTTP/1.0 403 Forbidden');
        exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
    }

    $loader = require_once __DIR__.'/../app/bootstrap.php.cache';
    Debug::enable();

    require_once __DIR__.'/../app/AppKernel.php';

    $kernel = new AppKernel('dev', true);
    $kernel->loadClassCache();
    $request = RequestFactory::createFromGlobals('host_with_path_by_locale'); // before: $request = Request::createFromGlobals();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);

.. note::

    If you use ``app_test.php`` and/or ``app_*.php`` don't forget to modify these files, too!

The last action is to configure the ``sonata_page`` section as:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml

        sonata_page:
            multisite: host_with_path_by_locale

And that's it!

.. note::

    If you have a working site with the PageBundle, you just need to create a
    ``Site`` and update the page table and the snapshot table with the correct
    Site ``id`` value.
