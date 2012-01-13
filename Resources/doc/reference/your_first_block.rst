You first block
===============

This quick tutorial explains how to create a RSS reader block.

A block service is just a service which must implements the ``BlockServiceInterface``
interface. There is only one instance of a block service, however there are many block
instances.

First namespaces
----------------

The ``BaseBlockService`` implements some basic methods defined by the interface.
The current Rss block will extend this base class. The others `use` statements are required
by the interface and remaining methods.

.. code-block:: php

    <?php
    namespace Sonata\PageBundle\Block;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Form\Form;
    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\PageBundle\Model\BlockInterface;
    use Sonata\PageBundle\Model\PageInterface;
    use Sonata\AdminBundle\Validator\ErrorElement;
    use Sonata\PageBundle\CmsManager\CmsManagerInterface;

Default settings
----------------

A block service needs settings to work properly, so to ensure consistency, the service should
define a ``getDefaultSettings`` method. In the current tutorial, the default settings are:

    - url : the feed url
    - title : the block title

.. code-block:: php

    <?php
    function getDefaultSettings()
    {
        return array(
            'url'     => false,
            'title'   => 'Insert the rss title'
        );
    }

Form Edition
------------

The ``PageBundle`` relies on the ``AdminBundle`` to manage form edition and keep
a good consistency.

.. code-block:: php

    <?php
    public function buildEditForm(CmsManagerInterface $manager, FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('url', 'url', array('required' => false)),
                array('title', 'text', array('required' => false)),
            )
        ));
    }

The validation is done at runtime through a ``validateBlock`` method. You can call any
Symfony2 assertions, like :

.. code-block:: php

    <?php
    function validateBlock(CmsManagerInterface $manager, ErrorElement $errorElement, BlockInterface $block)
    {
        $errorElement
            ->with('settings.url')
                ->assertNotNull(array())
                ->assertNotBlank()
            ->end()
            ->with('settings.title')
                ->assertNotNull(array())
                ->assertNotBlank()
                ->assertMaxLength(array('limit' => 50))
            ->end();
    }

The ``sonata_type_immutable_array`` type is a specific form type which allows to edit
an array.

Execute
-------

The next step is the execute method, this method must return a ``Response`` object, this
object is used to render the block.

.. code-block:: php

    <?php
    public function execute(CmsManagerInterface $manager, BlockInterface $block, PageInterface $page, Response $response = null)
    {
        // merge settings
        $settings = array_merge($this->getDefaultSettings(), $block->getSettings());

        $feeds = false;
        if ($settings['url']) {
            $options = array(
                'http' => array(
                    'user_agent' => 'Sonata/RSS Reader',
                    'timeout' => 2,
                )
            );

            // retrieve contents with a specific stream context to avoid php errors
            $content = @file_get_contents($settings['url'], false, stream_context_create($options));

            if ($content) {
                // generate a simple xml element
                try {
                    $feeds = new \SimpleXMLElement($content);
                    $feeds = $feeds->channel->item;
                } catch(\Exception $e) {
                    // silently fail error
                }
            }
        }

        return $this->renderResponse('SonataPageBundle:Block:block_core_rss.html.twig', array(
            'feeds'     => $feeds,
            'block'     => $block,
            'settings'  => $settings
        ), $response);
    }

Template
--------

A block template is very simple, in the current tutorial, we are looping on feeds or if not
defined, a error message is displayed.

.. code-block:: jinja

    {% extends 'SonataPageBundle:Block:block_base.html.twig' %}

    {% block block %}
        <h3>{{ settings.title }}</h3>

        <div class="sonata-feeds-container">
            {% for feed in feeds %}
                <div>
                    <strong><a href="{{ feed.link}}" rel="nofollow" title="{{ feed.title }}">{{ feed.title }}</a></strong>
                    <div>{{ feed.description|raw }}</div>
                </div>
            {% elsefor %}
                No feeds available.
            {% endfor %}
        </div>
    {% endblock %}

Service
-------

We are almost done! Now just declare the block as a service

.. code-block:: xml

    <service id="sonata.page.block.rss" class="Sonata\PageBundle\Block\RssBlockService" public="false">
        <tag name="sonata.page.block" />
        <argument>sonata.page.block.rss</argument>
        <argument type="service" id="templating" />
    </service>

and add it to sonata configuration

.. code-block:: yaml

    #config.yml
    sonata_page:
        services:
            sonata.page.block.rss:
    #           cache: sonata.page.cache.memcached 

    
