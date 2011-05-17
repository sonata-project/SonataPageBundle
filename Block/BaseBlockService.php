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
use Symfony\Component\Templating\EngineInterface;

/**
 * BaseBlockService
 *
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class BaseBlockService implements BlockServiceInterface
{
    protected $name;

    protected $templating;

    public function __construct($name, EngineInterface $templating)
    {
        $this->name = $name;
        $this->templating = $templating;
    }

    /**
     * Render a view
     *
     * @param string $view
     * @param array $parameters
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return string
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        return $this->getTemplating()->render($view, $parameters, $response);
    }

    /**
     *
     * @return name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \Symfony\Component\Templating\EngineInterface
     */
    public function getTemplating()
    {
        return $this->templating;
    }
}