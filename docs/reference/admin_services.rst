Admin Services
===============

There are some already implemented admin, to know how to customize them check out `Admin Extension`_

List of admin
-------------

+------------------------+--------------------------------+----------------------------------------------------------------------+
| Admin name             | Target                         | Description                                                          |
+========================+================================+======================================================================+
| Page Admin             | sonata.page.admin.page         | This admin is used to manage pages.                                  |
+------------------------+--------------------------------+----------------------------------------------------------------------+
| Block Admin            | sonata.page.admin.block        | This admin is used to handle `Block`_ into your page.                |
+------------------------+--------------------------------+----------------------------------------------------------------------+
| Shared block Admin     | sonata.page.admin.shared_block | You can manager blocks to share between your pages.                  |
+------------------------+--------------------------------+----------------------------------------------------------------------+
| Snapshot Admin         | sonata.page.admin.snapshot     | This admin is used to create and visualize snapshot from your pages. |
+------------------------+--------------------------------+----------------------------------------------------------------------+
| Site Admin             | sonata.page.admin.site         | This admin is used to manager sites.                                 |
+------------------------+--------------------------------+----------------------------------------------------------------------+

.. Tip::

    You can use **alias** as a target in your service tags.


.. _Admin Extension: https://docs.sonata-project.org/projects/SonataAdminBundle/en/4.x/reference/extensions/
.. _Block: https://docs.sonata-project.org/projects/SonataBlockBundle/en/4.x/
