<?php

namespace Sonata\PageBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class MenuBuilder
{
    private $factory;

    /**
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function createMainMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');
        $menu->setCurrentUri($request->getRequestUri());

        $menu->addChild('Home', array('route' => 'homepage'));
        // ... add more children

        return $menu;
    }
}