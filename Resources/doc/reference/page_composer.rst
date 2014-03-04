Page Composer
=============

Configure
---------

Javascript
----------

The **PageComposer** js object trigger various events to allow customization:


**containerloaded**, event properties:

.. code-block:: javascript

   event.containerId # loaded container id


**blockcreated**, event properties:

.. code-block:: javascript

   event.$childBlock # created block jQuery element
   event.parentId    # created block parent id
   event.blockId     # created block id
   event.blockName   # created block name
   event.blockType   # created block type


**blockremoved**, event properties:

.. code-block:: javascript

   event.parentId # removed block parent id


**blockcreateformloaded**, event properties:

.. code-block:: javascript

   event.response    # the raw html response (form)
   event.containerId # current container id
   event.blockType   # selected block type


**blockpositionsupdate**, event properties:

.. code-block:: javascript

   event.disposition # a javascript object containing all child blocks position/ids…


**blockeditformloaded**, event properties:

.. code-block:: javascript

   event.$block # the block jQuery element


**blockparentswitched**, event properties:

.. code-block:: javascript

   event.previousParentId # previous parent block id
   event.newParentId      # new parent block id
   event.blockId          # child block id
