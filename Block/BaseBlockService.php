<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\PageBundle\Block;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * PageExtension
 *
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class BaseBlockService extends ContainerAware
{
    protected $name;

    public function __construct($name, $container)
    {
        $this->name = $name;
        $this->container = $container;
    }

    abstract public function defineBlockGroupField($fieldGroup, $block);

    abstract public function validateBlock($block);

    public function getViewTemplate()
    {
        return sprintf('SonataPageBundle:Block:block_%s.twig.html', str_replace('.', '_', $this->getName()));
    }

    /**
     * Creates a Response instance.
     *
     * @param string  $content The Response body
     * @param integer $status  The status code
     * @param array   $headers An array of HTTP headers
     *
     * @return Response A Response instance
     */
    public function createResponse($content = '', $status = 200, array $headers = array())
    {
        $response = $this->container->get('response');
        $response->setContent($content);
        $response->setStatusCode($status);
        foreach ($headers as $name => $value) {
            $response->headers->set($name, $value);
        }

        return $response;
    }

    public function getEditTemplate()
    {
        return sprintf('SonataPageBundle:BlockAdmin:block_%s_edit.twig.html', str_replace('.', '_', $this->getName()));
    }

    public function render($view, array $parameters = array(), Response $response = null)
    {

        return $this
            ->container->get('templating')
            ->render($view, $parameters, $response);
    }

    public function execute($block, $page, Response $response = null)
    {
        try {
            $response = $this->render($block->getTemplate(), array(
                 'block' => $block,
                 'page'  => $page
            ));
        } catch(\Exception $e) {
            return $this->createResponse('An error occur while processing the block : '.$e->getMessage());
        }

        return $response;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}