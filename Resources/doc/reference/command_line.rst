Command Line Tools
==================

Flush commands
--------------

- Flush cache elements matching the key ``block_id = 5``::

    $ php app/console sonata:cache:flush --keys='{"block_id":5}'

- Flush all cache elements::

    $ php app/console sonata:cache:flush-all

For more information about this, please have a look at the `SonataCacheBundle documentation`_.

Page commands
-------------

- Update core routes, from routing files to page manager::

    $ php app/console sonata:page:update-core-routes --site=all

    You could also remove orphan pages with the ``--clean`` option.

    $ php app/console sonata:page:update-core-routes --site=all --clean

- Create snapshots from defined pages::

    $ php app/console sonata:page:create-snapshots --site=all

- Cleanup snapshots::

    $ php app/console sonata:page:cleanup-snapshots --site=all --keep-snapshots=5

- Create blocks::

    $ php app/console sonata:page:create-block-container --templateCode=default --blockCode=content_bottom --blockName="Left Content"

- Clone site with pages::

    $ php app/console sonata:page:clone-site --source-id=1 --dest-id=2 --prefix=Foo

Please note that you can also give multiple website identifiers to some commands, this way::

    $ php app/console sonata:page:update-core-routes --site=1 --site=2 --site=...
    $ php app/console sonata:page:create-snapshots --site=1 --site=2 --site=...
    $ php app/console sonata:page:cleanup-snapshots --site=1 --site=2 --site=...

Debug Commands
--------------

- Print page composition::

    $ php app/console sonata:page:dump-page sonata.page.cms.snapshot PAGE_ID
    $ php app/console sonata:page:dump-page sonata.page.cms.snapshot PAGE_ID --extended


- Render a block::

    $ php app/console sonata:page:render-block sonata.page.cms.snapshot PAGE_ID BLOCK_ID
    $ php app/console sonata:page:render-block sonata.page.cms.page PAGE_ID BLOCK_ID

.. _`SonataCacheBundle documentation`: https://sonata-project.org/bundles/cache/master/doc/index.html
