Pages Services
==============

Page services provide a way to implement specific processing for certain pages. By specifying a page's type, you may
associate a page with a service that will full-fill various needs for such type of pages. One of the most basic needs
is to render the page's template, set SEO data and possibly update http headers. This is the default behavior for a
page service, but you can easily extend those needs to include data loading, security checks, breadcrumb management,
and url generation.

Creating a new Page Service
---------------------------

Creating a new page service requires a class that implements the ``Sonata\PageBundle\Page\Service\PageServiceInterface``.
You must implement an execute() method. This method is called with a Page Entity and a Request object and is expected to
return a Response object. At this stage, the page service works a little bit like a Controller.

For example, a simple implementation of a page service is to render the page. Normally, you could simply inject the
Templating engine and use it to render a Response object, just as you would do in a standard Controller. However, pages
can be configured to use a particular template. Therefore, to render a page, you need to retrieve the page's configured
template. This can be easily achieved by using the template manager service.

.. code-block:: php

    <?php

    class MyService implements PageServiceInterface
    {
        public function __construct($name, TemplateManager $templateManager)
        {
            $this->templateManager = $templateManager;
        }

        public function execute(PageInterface $page, Request $request, array $parameters = array(), Response $response = null)
        {
            // add custom processing (load data, update SEO values, update http headers, perform security checks, ...)

            return $this->templateManager->renderResponse($page->getTemplateCode(), $parameters, $response);
        }
    }

You then need to declare the class as a page service in the service container configuration and use a tag to identify
it as being a page service:

.. code-block:: xml

    <service id="my_service_id" class="My\PageService\ClassName">
        <tag name="sonata.page"/>
        <argument>Service name</argument>
        <argument type="service" id="sonata.page.template_manager" />
    </service>

Once you have defined the service and the class, you will have a new page type to choose in the Page Admin.