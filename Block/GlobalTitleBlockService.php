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

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GlobalTitleBlockService extends BaseBlockService
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'code'     => 'title',
            'template' => 'SonataPageBundle:Block:block_global_title.html.twig',
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function buildEditForm(FormMapper $form, BlockInterface $block)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
    }
}