SonataPageBundle: a Symfony2 friendly CMS
=========================================

The ``SonataPageBundle`` is a special kind of CMS as it handles different types of page.
From within a Symfony2 application, actions are used to render a HTML response. When
you need to add a new component (or block) inside an action, you need to edit the
template. In the other hand, in the CMS world, users edit area and manage
content but it is not possible to have complex actions or workflows.

It is very difficult to mix CMS page and Action page inside one and unique solution. The
easiest way is to build the project with 2 backends, one for the CMS and one for
the application.

The ``SonataPageBundle`` tries to solve this problem by encapsulating action pages into the CMS.
So actions can be handled as a CMS page with the same solution, and this allows
to easily include externals Symfony bundles.

Page types:

    - ``CMS Page``: a standard CMS page with url
    - ``Hybrid Page`` : a page linked to a Symfony action, this can be any kind of url
      matched by the router.
    - ``Dynamic Page`` : a dynamic page is an hybrid page with parameters
      ie, /blog/{year}/{month}/{slug}
    - ``Internal Page`` : page shared across different pages, very useful for handling
      footer and header


## Available services

### Cache

    - sonata.page.cache.noop        : default, no cache
    - sonata.page.cache.esi         : Edge Side Include cache
    - sonata.page.cache.mongo       : MongoDB cache backend
    - sonata.page.cache.memcached   : Memcached cache backend
    - sonata.page.cache.apc         : Apc cache backend
    - sonata.page.cache.js_sync     : Javascript synchronized load (usefull for user specific content)
    - sonata.page.cache.js_async    : Javascript asynchronized load (usefull for user specific content)

### Block

    - sonata.page.block.container      : Block container
    - sonata.page.block.action         : Render a specific action
    - sonata.page.block.text           : Render a text block
    - sonata.page.block.children_pages : Render a navigation panel
