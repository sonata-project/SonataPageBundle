<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Bundle\Sonata\PageBundle\Block;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * PageExtension
 *
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ActionBlockService extends BaseBlockService
{

    public function render($template, $params = array())
    {

        return $this
            ->container->get('templating')
            ->render($template, $params);
    }

    public function execute($block, $page)
    {
        
        return $this->render($this->getViewTemplate(), array(
            'content' => $this->container->get('controller_resolver')->render(
                $block->getSetting('action'),
                array_merge($block->getSetting('parameters'), array('_block' => $block, '_page' => $page))
            ),
            'block' => $block,
            'page'  => $page,
        ));
    }

    public function validateBlock($block)
    {
        // TODO: Implement validateBlock() method.
    }

    public function defineBlockGroupField($field_group, $block)
    {
        $field_group->add(new \Symfony\Component\Form\TextField('action'));

        $parameters = new \Symfony\Component\Form\FieldGroup('parameters');
        
        foreach($block->getSetting('parameters') as $name => $value) {
            $parameters->add(new \Symfony\Component\Form\TextField($name));
        }

        $field_group->add($parameters);
    }

}