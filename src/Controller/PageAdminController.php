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

use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\PageBundle\Admin\BlockAdmin;
use Sonata\PageBundle\Admin\SnapshotAdmin;
use Sonata\PageBundle\Model\BlockInteractorInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Page\TemplateManagerInterface;
use Sonata\PageBundle\Service\CreateSnapshotService;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @extends CRUDController<PageInterface>
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class PageAdminController extends CRUDController
{
    public static function getSubscribedServices(): array
    {
        return [
            'sonata.page.admin.snapshot' => SnapshotAdmin::class,
            'sonata.page.admin.block' => BlockAdmin::class,
            'sonata.page.block_interactor' => BlockInteractorInterface::class,
            'sonata.page.manager.site' => SiteManagerInterface::class,
            'sonata.page.manager.page' => PageManagerInterface::class,
            'sonata.page.service.create_snapshot' => CreateSnapshotService::class,
            'sonata.page.site.selector' => SiteSelectorInterface::class,
            'sonata.page.template_manager' => TemplateManagerInterface::class,
            'sonata.block.manager' => BlockServiceManagerInterface::class,
        ] + parent::getSubscribedServices();
    }

    /**
     * @param ProxyQueryInterface<PageInterface> $query
     *
     * @throws AccessDeniedException
     */
    public function batchActionSnapshot(ProxyQueryInterface $query): RedirectResponse
    {
        $snapShotAdmin = $this->container->get('sonata.page.admin.snapshot');
        \assert($snapShotAdmin instanceof SnapshotAdmin);
        $snapShotAdmin->checkAccess('create');

        $createSnapshot = $this->container->get('sonata.page.service.create_snapshot');
        \assert($createSnapshot instanceof CreateSnapshotService);
        foreach ($query->execute() as $page) {
            $createSnapshot->createByPage($page);
        }

        return new RedirectResponse($this->admin->generateUrl('list', [
            'filter' => $this->admin->getFilterParameters(),
        ]));
    }

    public function listAction(Request $request): Response
    {
        if (null === $request->get('filter')) {
            return new RedirectResponse($this->admin->generateUrl('tree'));
        }

        return parent::listAction($request);
    }

    public function treeAction(Request $request): Response
    {
        $this->admin->checkAccess('tree');

        $siteManager = $this->container->get('sonata.page.manager.site');
        \assert($siteManager instanceof SiteManagerInterface);
        $sites = $siteManager->findBy([]);
        $pageManager = $this->container->get('sonata.page.manager.page');
        \assert($pageManager instanceof PageManagerInterface);

        $currentSite = null;
        $siteId = $request->get('site');
        foreach ($sites as $site) {
            if (null !== $siteId && (string) $site->getId() === $siteId) {
                $currentSite = $site;
            } elseif (null === $siteId && $site->getIsDefault()) {
                $currentSite = $site;
            }
        }
        if (null === $currentSite && 1 === \count($sites)) {
            $currentSite = $sites[0];
        }

        if (null !== $currentSite) {
            $pages = $pageManager->loadPages($currentSite);
        } else {
            $pages = [];
        }

        $datagrid = $this->admin->getDatagrid();
        $formView = $datagrid->getForm()->createView();
        $theme = $this->admin->getFilterTheme();
        $this->setFormTheme($formView, $theme);

        return $this->render($this->admin->getTemplateRegistry()->getTemplate('tree'), [
            'action' => 'tree',
            'sites' => $sites,
            'currentSite' => $currentSite,
            'pages' => $pages,
            'form' => $formView,
            'csrf_token' => $this->getCsrfToken('sonata.batch'),
        ]);
    }

    public function createAction(Request $request): Response
    {
        $this->admin->checkAccess('create');

        if ('GET' === $request->getMethod() && null === $request->get('siteId')) {
            $siteManager = $this->container->get('sonata.page.manager.site');
            \assert($siteManager instanceof SiteManagerInterface);
            $sites = $siteManager->findBy([]);

            if (1 === \count($sites)) {
                return $this->redirect($this->admin->generateUrl('create', [
                    'siteId' => $sites[0]->getId(),
                    'uniqid' => $this->admin->getUniqId(),
                ] + $request->query->all()));
            }

            try {
                $siteSelector = $this->container->get('sonata.page.site.selector');
                \assert($siteSelector instanceof SiteSelectorInterface);
                $current = $siteSelector->retrieve();
            } catch (\RuntimeException) {
                $current = false;
            }

            return $this->render($this->admin->getTemplateRegistry()->getTemplate('select_site'), [
                'sites' => $sites,
                'current' => $current,
            ]);
        }

        return parent::createAction($request);
    }

    /**
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    public function composeAction(Request $request): Response
    {
        $this->admin->checkAccess('compose');

        $blockAdmin = $this->container->get('sonata.page.admin.block');
        \assert($blockAdmin instanceof BlockAdmin);

        if (false === $blockAdmin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        $id = $request->get($this->admin->getIdParameter());
        $page = $this->admin->getObject($id);
        if (null === $page) {
            throw new NotFoundHttpException(sprintf('Unable to find the page with id : %s', $id));
        }
        $templateCode = $page->getTemplateCode();
        if (null === $templateCode) {
            throw new NotFoundHttpException(sprintf('The page with id "%s" has no template code', $id));
        }

        $containers = [];
        $orphanContainers = [];
        $children = [];

        $templateManager = $this->container->get('sonata.page.template_manager');
        \assert($templateManager instanceof TemplateManagerInterface);
        $template = $templateManager->get($templateCode);
        if (null === $template) {
            throw new NotFoundHttpException(sprintf('Unable to find the template with code : %s', $templateCode));
        }

        $templateContainers = $template->getContainers();

        foreach ($templateContainers as $containerId => $container) {
            $containers[$containerId] = [
                'area' => $container,
                'block' => false,
            ];
        }

        // 'attach' containers to corresponding template area, otherwise add it to orphans
        foreach ($page->getBlocks() as $block) {
            $blockCode = $block->getSetting('code');
            if (null === $block->getParent()) {
                if (isset($containers[$blockCode])) {
                    $containers[$blockCode]['block'] = $block;
                } else {
                    $orphanContainers[] = $block;
                }
            } else {
                $children[] = $block;
            }
        }

        // searching for block defined in template which are not created
        $blockInteractor = $this->container->get('sonata.page.block_interactor');
        \assert($blockInteractor instanceof BlockInteractorInterface);

        foreach ($containers as $containerId => $container) {
            if (false === $container['block'] && false === $templateContainers[$containerId]['shared']) {
                $blockContainer = $blockInteractor->createNewContainer([
                    'page' => $page,
                    'name' => $templateContainers[$containerId]['name'],
                    'code' => (string) $containerId,
                ]);

                $containers[$containerId]['block'] = $blockContainer;
            }
        }

        return $this->render($this->admin->getTemplateRegistry()->getTemplate('compose'), [
            'object' => $page,
            'action' => 'edit',
            'template' => $template,
            'page' => $page,
            'containers' => $containers,
            'orphanContainers' => $orphanContainers,
            'blockAdmin' => $blockAdmin,
            'csrfTokens' => [
                'remove' => $this->getCsrfToken('sonata.delete'),
            ],
        ]);
    }

    /**
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    public function composeContainerShowAction(Request $request): Response
    {
        $blockAdmin = $this->container->get('sonata.page.admin.block');
        \assert($blockAdmin instanceof BlockAdmin);

        if (false === $blockAdmin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        $id = $request->get($this->admin->getIdParameter());
        $block = $blockAdmin->getObject($id);
        if (null === $block) {
            throw new NotFoundHttpException(sprintf('Unable to find the block with id : %s', $id));
        }

        $blockManager = $this->container->get('sonata.block.manager');
        \assert($blockManager instanceof BlockServiceManagerInterface);
        $blockServices = $blockManager->getServicesByContext('sonata_page_bundle', false);

        foreach ($blockServices as $code => $blockService) {
            if ($blockService instanceof EditableBlockService) {
                continue;
            }

            unset($blockServices[$code]);
        }

        $page = $block->getPage();
        $blockCode = $block->getSetting('code');

        // filter service using the template configuration
        if (null !== $page && null !== $blockCode) {
            $templateCode = $page->getTemplateCode();
            if (null === $templateCode) {
                throw new NotFoundHttpException(sprintf('The page with id "%s" has no template code', $id));
            }

            $templateManager = $this->container->get('sonata.page.template_manager');
            \assert($templateManager instanceof TemplateManagerInterface);

            $template = $templateManager->get($templateCode);
            if (null === $template) {
                throw new NotFoundHttpException(
                    sprintf('Unable to find the template with code : %s', $templateCode)
                );
            }
            $container = $template->getContainer($blockCode);

            if (null !== $container && \count($container['blocks']) > 0) {
                foreach ($blockServices as $code => $blockService) {
                    if (\in_array($code, $container['blocks'], true)) {
                        continue;
                    }

                    unset($blockServices[$code]);
                }
            }
        }

        return $this->render($this->admin->getTemplateRegistry()->getTemplate('compose_container_show'), [
            'blockServices' => $blockServices,
            'blockAdmin' => $blockAdmin,
            'container' => $block,
            'page' => $page,
        ]);
    }
}
