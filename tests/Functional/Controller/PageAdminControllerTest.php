<?php

namespace Sonata\PageBundle\Tests\Functional\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\PageBundle\Admin\BlockAdmin;
use Sonata\PageBundle\Controller\PageAdminController;
use Sonata\PageBundle\Entity\BlockManager;
use Sonata\PageBundle\Page\TemplateManagerInterface;
use Sonata\PageBundle\Tests\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class PageAdminControllerTest extends WebTestCase
{
    public function testComposerContainerShow(): void
    {
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();

        $blockServiceManager = $this->createMock(BlockServiceManagerInterface::class);
        $managerRegistry = $this->createMock(ManagerRegistry::class);

        $container->set('sonata.page.admin.block', new BlockAdmin($blockServiceManager));
        $container->set('sonata.block.manager', new BlockManager('foo', $managerRegistry));
        $container->set('sonata.page.template_manager', $container->get('twig'));
        $request = $this->createMock(Request::class);

        $pageAdminController = new PageAdminController();
        $pageAdminController->composeContainerShowAction($request);
    }

    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }
}