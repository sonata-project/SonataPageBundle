Command Line Tools
==================

Page commands
-------------

- Update core routes, from routing files to page manager

.. code-block:: bash

    bin/console sonata:page:update-core-routes

You could also remove orphan pages with the ``--clean`` option.

.. code-block:: bash

    bin/console sonata:page:update-core-routes --clean

- Create snapshots from defined pages

.. code-block:: bash

    bin/console sonata:page:create-snapshots

- Cleanup snapshots

.. code-block:: bash

    bin/console sonata:page:cleanup-snapshots --keep-snapshots=5

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
