Introduction
============

This small introduction will try to explain the basics concepts behind the
``PageBundle``. Since the version 2.1 of ``PageBundle``, the Symfony CMF router
chain is now integrated. This makes the solution compatible with other projects
using this component : ezPublish, CMF project and more ...

A Site
------

A ``Site`` is an entity linked to many Pages. The ``PageBundle`` can host many
sites on a same project.

Depending on the configuration a site can be represented by:

 - a host : sonata-project.org or sonata-project.com
 - a host + a path : sonata-project.org/uk or sonata-project.org/fr
   or sonata-project.com/uk
 - a host and a locale (locale detected from the browser)
 - a host, path and a locale (locale detected from the browser)

The latter requires a specific ``RequestContext`` to work. This is done by
changing the ``router`` service.

A Page
------

The ``SonataPageBundle`` is a special kind of CMS as it handles different types
of pages. From within a Symfony2 application, actions are used to render a HTML
response. When you need to add a new component (or block) inside an action, you
need to edit the template. In the other hand, in the CMS world, users edit areas
and manage content but it is not possible to have complex actions or workflows.

It is very difficult to mix CMS page and Action page inside one and unique
solution. The easiest way is to build the project with 2 backends, one for the
CMS and one for the application.

The ``SonataPageBundle`` tries to solve this problem by encapsulating action
pages into the CMS. So actions can be handled as a CMS page with the same
solution, and this allows to easily include external Symfony bundles.

Page types:

 - ``CMS Page`` : a standard CMS page with url
 - ``Hybrid Page`` : a page linked to a Symfony action, this can be any kind
   of url matched by the router.
 - ``Dynamic Page`` : a dynamic page is a hybrid page with parameters
   ie, /blog/{year}/{month}/{slug}
 - ``Internal Page`` : page shared across different pages, very useful for
   handling footer and header

A Block
-------

The ``SonataPageBundle`` does not know how to manage content, actually there is
no content management. This part is delegated to services. The bundle only
manages references to the service required by a page. Reference information is
stored in a ``Block``.

A block is a small unit, it contains the following information:

 - service id
 - position
 - settings used by the service

Each block service must implement the ``Sonata\PageBundle\Block\BlockServiceInterface``
which defines a set of functions to create so the service can be integrated
nicely with editing workflow. The important information is that a block service
must always return a ``Response`` object.

By default the ``SonataPageBundle`` is shipped with core block services:

 - sonata.page.block.container      : Block container
 - sonata.page.block.children_pages : Render a navigation panel

A Snapshot
----------

A ``Snapshot`` is a version of a ``Page`` used to render the page to the final user.
So when an editor organizes ``Page`` and ``Block`` the final user does not see these
modifications unless the editor creates a new snapshot of the page.

A Cache
-------

There is a cache mechanism integrated into the bundle. Each block service is linked
to a cache service.

Depending on the block logic some cache backends are more suitable than others:

 - Container should use the ``sonata.page.cache.esi`` cache
 - Users related block like basket summary or authentication area should
   use the ``sonata.page.cache.js_async`` cache.

Of course if you don't have a reverse proxy server you can use other caching
solution such as memcached, mongo or apc.

The ``sonata.cache.noop`` cache can be use if you don't want caching!
