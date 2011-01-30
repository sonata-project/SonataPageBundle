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

    public function render($view, array $parameters = array(), Response $response = null)
    {

        return $this
            ->container->get('templating')
            ->render($view, $parameters);
    }

    public function execute($block, $page, Response $response = null)
    {

        return $this->render($this->getViewTemplate(), array(
            'content' => $this->container->get('http_kernel')->render(
                $block->getSetting('action'),
                array_merge($block->getSetting('parameters', array()), array('_block' => $block, '_page' => $page))
            ),
            'block' => $block,
            'page'  => $page,
        ), $response);
    }

    public function validateBlock($block)
    {
        // TODO: Implement validateBlock() method.
    }

    public function defineBlockGroupField($fieldGroup, $block)
    {
        $fieldGroup->add(new \Symfony\Component\Form\TextField('action'));

        $parameters = new \Symfony\Component\Form\FieldGroup('parameters');
        
        foreach($block->getSetting('parameters', array()) as $name => $value) {
            $parameters->add(new \Symfony\Component\Form\TextField($name));
        }

        $fieldGroup->add($parameters);
    }

}