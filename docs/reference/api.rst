API
===

SonataPageBundle embeds Controllers to provide an API through FOSRestBundle, with its documentation provided by NelmioApiDocBundle.

Setup
-----

If you wish to use it, you must first follow the installation instructions of both bundles:

* `FOSRestBundle`_
* `NelmioApiDocBundle`_

Here's the configuration we used, you may adapt it to your needs:

.. configuration-block::

    .. code-block:: yaml

        fos_rest:
            param_fetcher_listener: true
            body_listener:          true
            format_listener:        true
            view:
                view_response_listener: force
            body_converter:
                enabled: true
                validate: true

    .. code-block:: yaml

        sensio_framework_extra:
            view:    { annotations: false }
            router:  { annotations: true }
            request: { converters: true }

    .. code-block:: yaml

        twig:
            exception_controller: 'FOS\RestBundle\Controller\ExceptionController::showAction'

    .. code-block:: yaml

        # for SonataPageBundle > 2.3.6
        jms_serializer:
            metadata:
                directories:
                    -
                        path: "%kernel.root_dir%/../vendor/sonata-project/datagrid-bundle/Resources/config/serializer"
                        namespace_prefix: 'Sonata\DatagridBundle'

In order to activate the API's, you'll also need to add this to your routing:

.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml

        NelmioApiDocBundle:
            resource: "@NelmioApiDocBundle/Resources/config/routing.yml"
            prefix:   /api/doc

        sonata_api_page:
            type:         rest
            prefix:       /api/page
            resource:     "@SonataPageBundle/Resources/config/routing/api.xml"

Serialization
-------------

We're using serializations groups from `JMSSerializerBundle`_ to customize the inputs & outputs.

The taxonomy is as follows:

* ``sonata_api_read`` is the group used to display entities
* ``sonata_api_write`` is the group used for input entities (when used instead of forms)

If you wish to customize the outputted data, feel free to setup your own serialization options by configuring `JMSSerializer`_ with those groups.

.. _`FOSRestBundle`: https://github.com/FriendsOfSymfony/FOSRestBundle
.. _`NelmioApiDocBundle`: https://github.com/nelmio/NelmioApiDocBundle
.. _`JMSSerializerBundle`: https://github.com/schmittjoh/JMSSerializerBundle
.. _`JMSSerializer`: https://github.com/schmittjoh/serializer
