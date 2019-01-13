Command Line Tools
==================

Flush commands
--------------

- Flush cache elements matching the key ``block_id = 5``::

    bin/console sonata:cache:flush --keys='{"block_id":5}'

- Flush all cache elements::

    bin/console sonata:cache:flush-all

For more information about this, please have a look at the `SonataCacheBundle documentation`_.

Page commands
-------------

- Update core routes, from routing files to page manager

.. code-block:: bash

    bin/console sonata:page:update-core-routes --site=all

You could also remove orphan pages with the ``--clean`` option.

.. code-block:: bash

    bin/console sonata:page:update-core-routes --site=all --clean

- Create snapshots from defined pages

.. code-block:: bash

    bin/console sonata:page:create-snapshots --site=all

- Cleanup snapshots

.. code-block:: bash

    bin/console sonata:page:cleanup-snapshots --site=all --keep-snapshots=5

- Create blocks

.. code-block:: bash

    bin/console sonata:page:create-block-container --templateCode=default --blockCode=content_bottom --blockName="Left Content"

- Clone site with pages

.. code-block:: bash

    bin/console sonata:page:clone-site --source-id=1 --dest-id=2 --prefix=Foo

Please note that you can also give multiple website identifiers to some commands, this way

.. code-block:: bash

    bin/console sonata:page:update-core-routes --site=1 --site=2 --site=...
    bin/console sonata:page:create-snapshots --site=1 --site=2 --site=...
    bin/console sonata:page:cleanup-snapshots --site=1 --site=2 --site=...

Debug Commands
--------------

- Print page composition

.. code-block:: bash

    bin/console sonata:page:dump-page sonata.page.cms.snapshot PAGE_ID
    bin/console sonata:page:dump-page sonata.page.cms.snapshot PAGE_ID --extended

- Render a block

.. code-block:: bash

    bin/console sonata:page:render-block sonata.page.cms.snapshot PAGE_ID BLOCK_ID
    bin/console sonata:page:render-block sonata.page.cms.page PAGE_ID BLOCK_ID

.. _`SonataCacheBundle documentation`: https://sonata-project.org/bundles/cache/master/doc/index.html
