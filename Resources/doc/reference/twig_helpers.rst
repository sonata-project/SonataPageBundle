Twig Helpers
============

Url
---

Render a page url

.. code-block:: jinja

    {{ path(page) }} => /index.php/url/to/url

    {{ path(page, true) }} => http://sonata-project.org/index.php/url/to/url

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

Optionally, you can pass as a third argument some settings that will override original container settings:

.. code-block:: jinja

    {{ sonata_page_render_container('name', page, {key: value}) }}


Block
-----

You can also render an existing block using this function by sending a block object instance:

.. code-block:: jinja

    {{ sonata_page_render_block(block, {key: value}) }}

Or even create a on-the-fly block in a Twig template this way:

.. code-block:: jinja

    {{ sonata_page_render_block('my.template.text.block', {
        type: 'sonata.block.service.text',
        container: 'content_top',
        settings: { content: 'Hi! this is my text content' }
    }) }}

This way, a ``sonata.block.service.text`` block will be added to your ``content_top`` container with the following settings given.