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
templating engine and use it to render a Response object, just as you would do in a standard Controller. However, pages
can be configured to use a particular template. Therefore, to render a page, you need to retrieve the page's configured
template. This can be easily achieved by using the template manager service.

.. code-block:: php

    <?php
    // src/AppBundle/Service/CustomPageService.php

    namespace AppBundle\Service;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpFoundation\Request;
    use Sonata\PageBundle\Model\PageInterface;
    use Sonata\PageBundle\Page\TemplateManager;

    class CustomPageService implements PageServiceInterface
    {
        /**
         * @var TemplateManager
         */
        private $templateManager;

        public function __construct($name, TemplateManager $templateManager)
        {
            // ...
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

    <service id="app.custom_page_service" class="AppBundle\Service\CustomPageService">
        <tag name="sonata.page"/>
        <argument>Custom page service</argument>
        <argument type="service" id="sonata.page.template_manager" />
    </service>

Once you have defined the service and the class, you will have a new page type to choose in the ``Page Admin``.
