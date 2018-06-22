Assets
======

Assets are managed with gulp which require yarn and a global gulp install.

* stylesheets are generated from .scss files located in ``assets/src/scss``
* javascripts are generated from files located in ``assets/src/js``

.. warning::

   Do not edit directly files located in the ``src/Resources/public`` folder,
   if you want to contribute, you should edit files in the ``assets/src`` folder.


Compiling Sources
-----------------

If you made some modifications in the sources,
you can generate the public ones with the following command:

.. code-block:: bash

   yarn build
