<?php

declare(strict_types=1);

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
use Symfony\Component\HttpFoundation\Response;

/**
 * Render a block in ajax.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class AjaxController
{
    private CmsManagerSelectorInterface $cmsManagerSelector;

    private BlockRendererInterface $blockRenderer;

    private BlockContextManagerInterface $contextManager;

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
     * @param int $pageId  Page identifier
     * @param int $blockId Block identifier
     *
     * @return Response
     */
    public function execute($pageId, $blockId)
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
