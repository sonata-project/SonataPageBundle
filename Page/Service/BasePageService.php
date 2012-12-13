<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Page\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Sonata\PageBundle\Model\PageInterface;

/**
 * Abstract page service that provides a basic implementation
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
abstract class BasePageService implements PageServiceInterface
{
    /**
     * Page service name used in the admin
     *
     * @var string
     */
    protected $name;

    /**
     * Constructor
     *
     * @param string $name Page service name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function execute(PageInterface $page, Request $request, array $parameters = array(), Response $response = null);
}