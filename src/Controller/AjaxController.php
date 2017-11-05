<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Controller;

use Sonata\BlockBundle\Block\BlockContextManagerInterface;
use Sonata\BlockBundle\Block\BlockRendererInterface;
use Sonata\BlockBundle\Exception\BlockNotFoundException;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Render a block in ajax.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AjaxController
{
    /**
     * @var CmsManagerSelectorInterface
     */
    protected $cmsManagerSelector;

    /**
     * @var BlockRendererInterface
     */
    protected $blockRenderer;

    /**
     * @var BlockContextManagerInterface
     */
    protected $contextManager;

    /**
     * @param CmsManagerSelectorInterface  $cmsManagerSelector CMS Manager selector
     * @param BlockRendererInterface       $blockRenderer      Block renderer
     * @param BlockContextManagerInterface $contextManager     Context Manager
     */
    public function __construct(CmsManagerSelectorInterface $cmsManagerSelector, BlockRendererInterface $blockRenderer, BlockContextManagerInterface $contextManager)
    {
        $this->cmsManagerSelector = $cmsManagerSelector;
        $this->blockRenderer = $blockRenderer;
        $this->contextManager = $contextManager;
    }

    /**
     * Action for ajax route rendering a block by calling his executeAjax() method.
     *
     * @param Request $request Symfony request object
     * @param int     $pageId  Page identifier
     * @param int     $blockId Block identifier
     *
     * @return Response
     */
    public function execute(Request $request, $pageId, $blockId)
    {
        $cmsManager = $this->cmsManagerSelector->retrieve();

        $page = $cmsManager->getPageById($pageId);
        $block = $cmsManager->getBlock($blockId);

        if (!$block instanceof BlockInterface) {
            throw new BlockNotFoundException(sprintf('Unable to find block identifier "%s" in page "%s".', $blockId, $pageId));
        }

        $blockContext = $this->contextManager->get($block);

        return $this->blockRenderer->render($blockContext);
    }
}
