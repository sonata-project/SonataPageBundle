# Symfony2 friendly CMS

Available services
------------------

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
