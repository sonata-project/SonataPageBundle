Twig Helpers
============

URL
---

Render a page url

.. code-block:: jinja

    {{ path(page) }} => /absolute/path/to/url

    {{ path(page, {}, true) }} => ../relative/path/to/url

    {{ url(page) }} => https://sonata-project.org/absolute/url/to/url

    {{ url(page, {}, true) }} => //sonata-project.org/network/path/to/url

.. note::

    In case you need to use a page's router in twig files, you can use ``pageAlias`` e.g:
      ``{{ path('_page_alias_your_page') }}``.

    You will find this field in your sonata page admin, the ``pageAlias`` is named of ``Technical Alias``.

Render a block url to render it in AJAX (given we have a block id = 1 used on a page id = 2)

.. code-block:: jinja

    {{ sonata_page_ajax_url(block) }} => /index.php/_page/block/2/1

    {{ sonata_page_ajax_url(block, {'parameter': 'value'}) }} => /index.php/_page/block/2/1?parameter=value

    {{ sonata_page_ajax_url(block, {'parameter': 'value'}, true) }} => https://sonata-project.org/index.php/_page/block/2/1?parameter=value

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

Breadcrumbs
-----------

.. code-block:: jinja

    {{ sonata_page_breadcrumb(page, {key: value}) }}

where expected ``key`` options can be ``separator`` (string), ``current_class`` (string),
``last_separator`` (string), ``force_view_home_page`` (boolean), ``container_attr`` (array of html attributes), ``elements_attr`` (array of html attributes), ``template`` (string).

Assets
------

.. code-block:: jinja

    {% for js in sonata_page.assets.javascripts %}
        {# ... #}
    {% endfor %}

This allows to access the javascripts and css assets configured in sonata_page configuration.
