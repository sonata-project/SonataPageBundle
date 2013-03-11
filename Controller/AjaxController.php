<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sonata\BlockBundle\Exception\BlockNotFoundException;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BlockRendererInterface;

use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;

/**
 * Render a block in ajax
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AjaxController
{
    /**
     * @var \Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface
     */
    protected $cmsManagerSelector;

    /**
     * @var \Sonata\BlockBundle\Block\BlockRendererInterface
     */
    protected $blockRenderer;

    /**
     * @param CmsManagerSelectorInterface $cmsManagerSelector CMS Manager selector
     * @param BlockRendererInterface      $blockRenderer      Block renderer
     */
    public function __construct(CmsManagerSelectorInterface $cmsManagerSelector, BlockRendererInterface $blockRenderer)
    {
        $this->cmsManagerSelector = $cmsManagerSelector;
        $this->blockRenderer      = $blockRenderer;
    }

    /**
     * Action for ajax route rendering a block by calling his executeAjax() method
     *
     * @param Request $request Symfony request object
     * @param integer $pageId  Page identifier
     * @param integer $blockId Block identifier
     */
    public function execute(Request $request, $pageId, $blockId)
    {
        $cmsManager = $this->cmsManagerSelector->retrieve();

        $page  = $cmsManager->getPageById($pageId);
        $block = $cmsManager->getBlock($blockId);

        if (!$block instanceof BlockInterface) {
            throw new BlockNotFoundException(sprintf('Unable to find block identifier "%s" in page "%s".', $blockId, $pageId));
        }

        $response = $this->blockRenderer->render($block);

        return $response;
    }
}
