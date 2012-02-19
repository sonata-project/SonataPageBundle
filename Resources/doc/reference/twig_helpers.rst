Twig Helpers
============

Url
---

Render a page url

.. code-block:: jinja

    {{ page_url(page) }} => /index.php/url/to/url

    {{ page_url(page, true) }} => http://sonata-project.org/index.php/url/to/url


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