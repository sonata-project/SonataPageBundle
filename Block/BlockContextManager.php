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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sonata\BlockBundle\Block\BlockContextManager as BaseBlockContextManager;

class BlockContextManager extends BaseBlockContextManager
{
    /**
     * @param OptionsResolverInterface $optionsResolver
     * @param BlockInterface           $block
     */
    protected function setDefaultSettings(OptionsResolverInterface $optionsResolver, BlockInterface $block)
    {
	    trigger_error(__CLASS__.'::'.__METHOD__.' is deprecated since version 2.3, to be renamed in 3.0. Use '.__CLASS__.'::configureSettings instead.');
	    $this->configureSettings($optionsResolver, $block);
    }

	protected function configureSettings(OptionsResolver $optionsResolver, BlockInterface $block)
	{

		parent::configureSettings($optionsResolver, $block);

		$optionsResolver->setDefaults(array(
			'manager' => false,
			'page_id' => false,
		));

		$optionsResolver->addAllowedTypes(array(
			'manager' => array('string', 'bool'),
			'page_id' => array('int', 'string', 'bool')
		));

		$optionsResolver->setRequired(array(
			'manager',
			'page_id'
		));
	}
}
