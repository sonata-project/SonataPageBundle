Twig Helpers
============

Url
---

Render a page url

.. code-block:: jinja

    {{ sonata_page_url(page) }} => /index.php/url/to/url

    {{ sonata_page_url(page, true) }} => http://sonata-project.org/index.php/url/to/url
    
Render a block url to render it in AJAX (given we have a block id = 1 used on a page id = 2)

.. code-block:: jinja

	{{ sonata_page_ajax_url(block) }} => /index.php/_page/block/2/1

    {{ sonata_page_ajax_url(block, {'parameter': 'value'}) }} => /index.php/_page/block/2/1?parameter=value

    {{ sonata_page_ajax_url(block, {'parameter': 'value'}, true) }} => http://sonata-project.org/index.php/_page/block/2/1?parameter=value


Container
---------

Render a container using the current page

.. code-block:: jinja

    {{ sonata_page_render_container('name') }}


Render a container using a transversal page named blog

.. code-block:: jinja

    {{ sonata_page_render_container('name', '_blog') }}


Render a container using a page instance

.. code-block:: jinja

    {{ sonata_page_render_container('name', page) }}