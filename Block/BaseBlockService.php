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

use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;

/**
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class BaseBlockService extends \Sonata\BlockBundle\Block\BaseBlockService
{
    /**
     * {@inheritdoc}
     */
    public function getCacheKeys(BlockInterface $block)
    {
        return array(
            'name'        => $this->getName(),
            'block_id'    => $block->getId(),
            'page_id'     => $block->getPage()->getId(),
            'updated_at'  => $block->getUpdatedAt()->format('U')
        );
    }
}