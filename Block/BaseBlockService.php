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
use Sonata\PageBundle\Model\BlockInterface;
use Sonata\PageBundle\Cache\CacheElement;
use Sonata\PageBundle\CmsManager\CmsManagerInterface;
use Sonata\AdminBundle\Form\FormMapper;

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

    protected $manager;

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
    public function renderResponse($view, array $parameters = array(), Response $response = null)
    {
        return $this->getTemplating()->renderResponse($view, $parameters, $response);
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

    /**
     * Returns the cache keys for the block
     *
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return \Sonata\PageBundle\Cache\CacheElement
     */
    public function getCacheElement(BlockInterface $block)
    {
        $baseCacheKeys = array(
            'block_id'    => $block->getId(),
            'page_id'     => $block->getPage()->getId(),
            'updated_at'  => $block->getUpdatedAt()->format('U')
        );

        return new CacheElement($baseCacheKeys, $block->getTtl());
    }

    public function setManager(CmsManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function buildCreateForm(FormMapper $formMapper, BlockInterface $block)
    {
        return $this->buildEditForm($formMapper, $block);
    }

    public function prePersist(BlockInterface $block)
    {
    }

    public function postPersist(BlockInterface $block)
    {
    }

    public function preUpdate(BlockInterface $block)
    {
    }

    public function postUpdate(BlockInterface $block)
    {
    }

    public function preDelete(BlockInterface $block)
    {
    }

    public function postDelete(BlockInterface $block)
    {
    }

    public function load(BlockInterface $block)
    {
    }
}