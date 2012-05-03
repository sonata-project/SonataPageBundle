Multisite
=========

The ``PageBundle`` handles multisite out of the box. However due to some 
limitation of the Symfony2 API, the multisite support is done around 2 strategies :

* host : you can configure a site per host. This strategy works out of the box 
    with no changes.
* host with path : you can configure site per host and per path. This strategy 
    requires some changes.


Host Strategy
---------------

With this strategy it is possible to handle sites like :

* http://sonata-project.org
* http://sonata-project.com
* http://sonata-project.net

Just configure the ``sonata_page`` section as:

.. code-block:: yaml

    sonata_page:
        multisite: host
        [...]

And that's it!


Host and Path Strategy
------------------------

With this strategy it is possible to handle sites like :

* http://sonata-project.org
* http://sonata-project.org/beta
* http://sonata-project.com/fr
* http://sonata-project.net


This strategy required a dedicated ``Request`` object. So you need to alter the 
front controller to use the one provided by the PageBundle. To to so, open files: 
app.php and app_dev.php and change the ``use`` statement to ::

    use Sonata\PageBundle\Request\SiteRequest as Request;

Working file example :

.. code-block:: php

    <?php
    require_once __DIR__.'/../app/bootstrap.php.cache';
    require_once __DIR__.'/../app/AppKernel.php';

    $kernel = new AppKernel('dev', true);
    $kernel->loadClassCache();

    use Sonata\PageBundle\Request\SiteRequest as Request;

    $kernel->handle(Request::createFromGlobals())->send();

The last action is to configure the ``sonata_page`` section as:

.. code-block:: yaml

    sonata_page:
        multisite: host_with_site
        [...]

And that's it!


.. note::

    If you have a working site with the PageBundle, you just need to create a 
    ``Site`` and update the page table and the snapshot table with the correct 
    Site ``id`` value.
