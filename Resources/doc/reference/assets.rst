Assets
======

Assets are managed with gulp which require nodejs/npm and a global gulp install.

* stylesheets are generated from .scss files located in ``/Resources/assets_src/src/scss``
* javascripts are generated from files located in ``/Resources/assets_src/src/js``

.. warning:: Do not edit directly files located in the ``/Resources/public`` folder,
   if you want to contribute, you should edit files in the ``/Resources/assets_src/src`` folder.


Compiling sources
-----------------

If you made some modifications in the sources,
you can generate the public ones with the following command:

.. code-block:: bash

   make assets