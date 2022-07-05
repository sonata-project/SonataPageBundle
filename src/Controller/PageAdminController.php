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

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\NotificationBundle\Backend\RuntimeBackend;
use Sonata\PageBundle\Service\Contract\CreateSnapshotByPageInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Page Admin Controller.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @final since sonata-project/page-bundle 3.26
 */
class PageAdminController extends Controller
{
    /**
     * @throws AccessDeniedException
     *
     * @return RedirectResponse
     */
    public function batchActionSnapshot($query)
    {
        if (false === $this->get('sonata.page.admin.snapshot')->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        foreach ($query->execute() as $page) {
            //NEXT_MAJOR: Remove the $notificationBackend variable
            $notificationBackend = $this->get('sonata.notification.backend');

            //NEXT_MAJOR: Remove the "if" condition and use only "createByPage"
            if ($notificationBackend instanceof RuntimeBackend) {
                //NEXT_MAJOR: Inject CreateSnapshotByPageInterface type and remove this "get" call.
                $this->get('sonata.page.service.create_snapshot')->createByPage($page);
            } else {
                @trigger_error(
                    sprintf(
                        'Inject %s in %s is deprecated since sonata-project/page-bundle 3.27.0'.
                        ' and will be removed in 4.0, Please inject %s insteadof %s',
                        BackendInterface::class,
                        self::class,
                        CreateSnapshotByPageInterface::class,
                        BackendInterface::class
                    ),
                    \E_USER_DEPRECATED
                );
                $notificationBackend
                    ->createAndPublish('sonata.page.create_snapshot', [
                        'pageId' => $page->getId(),
                    ]);
            }
        }

        return new RedirectResponse($this->getAdmin()->generateUrl('list', [
            'filter' => $this->getAdmin()->getFilterParameters(),
        ]));
    }

    public function listAction(?Request $request = null)
    {
        if (!$request->get('filter')) {
            return new RedirectResponse($this->admin->generateUrl('tree'));
        }

        return parent::listAction();
    }

    /**
     * @return Response
     */
    public function treeAction(?Request $request = null)
    {
        $this->admin->checkAccess('tree');

        $sites = $this->get('sonata.page.manager.site')->findBy([]);
        $pageManager = $this->get('sonata.page.manager.page');

        $currentSite = null;
        $siteId = (int) $request->get('site');
        foreach ($sites as $site) {
            if ($siteId && $site->getId() === $siteId) {
                $currentSite = $site;
            } elseif (!$siteId && $site->getIsDefault()) {
                $currentSite = $site;
            }
        }
        if (!$currentSite && 1 === \count($sites)) {
            $currentSite = $sites[0];
        }

        if ($currentSite) {
            $pages = $pageManager->loadPages($currentSite);
        } else {
            $pages = [];
        }

        $datagrid = $this->admin->getDatagrid();
        $formView = $datagrid->getForm()->createView();
        $theme = $this->admin->getFilterTheme();
        $this->setFormTheme($formView, $theme);

        return $this->render($this->admin->getTemplate('tree'), [
            'action' => 'tree',
            'sites' => $sites,
            'currentSite' => $currentSite,
            'pages' => $pages,
            'form' => $formView,
            'csrf_token' => $this->getCsrfToken('sonata.batch'),
        ]);
    }

    public function createAction(?Request $request = null)
    {
        $this->admin->checkAccess('create');

        if ('GET' === $request->getMethod() && !$this->getRequest()->get('siteId')) {
            $sites = $this->get('sonata.page.manager.site')->findBy([]);

            if (1 === \count($sites)) {
                return $this->redirect($this->admin->generateUrl('create', [
                    'siteId' => $sites[0]->getId(),
                    'uniqid' => $this->admin->getUniqid(),
                ] + $request->query->all()));
            }

            try {
                $current = $this->get('sonata.page.site.selector')->retrieve();
            } catch (\RuntimeException $e) {
                $current = false;
            }

            return $this->render($this->admin->getTemplate('select_site'), [
                'sites' => $sites,
                'current' => $current,
            ]);
        }

        return parent::createAction();
    }

    /**
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function composeAction(?Request $request = null)
    {
        $this->admin->checkAccess('compose');
        if (false === $this->get('sonata.page.admin.block')->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        $id = $request->get($this->admin->getIdParameter());
        $page = $this->admin->getObject($id);
        if (!$page) {
            throw new NotFoundHttpException(sprintf('unable to find the page with id : %s', $id));
        }

        $containers = [];
        $orphanContainers = [];
        $children = [];

        $templateManager = $this->get('sonata.page.template_manager');
        $template = $templateManager->get($page->getTemplateCode());
        $templateContainers = $template->getContainers();

        foreach ($templateContainers as $id => $container) {
            $containers[$id] = [
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
        $blockInteractor = $this->get('sonata.page.block_interactor');

        foreach ($containers as $id => $container) {
            if (false === $container['block'] && false === $templateContainers[$id]['shared']) {
                $blockContainer = $blockInteractor->createNewContainer([
                    'page' => $page,
                    'name' => $templateContainers[$id]['name'],
                    'code' => $id,
                ]);

                $containers[$id]['block'] = $blockContainer;
            }
        }

        return $this->render($this->admin->getTemplate('compose'), [
            'object' => $page,
            'action' => 'edit',
            'template' => $template,
            'page' => $page,
            'containers' => $containers,
            'orphanContainers' => $orphanContainers,
            'csrfTokens' => [
                'remove' => $this->getCsrfToken('sonata.delete'),
            ],
        ]);
    }

    /**
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function composeContainerShowAction(?Request $request = null)
    {
        if (false === $this->get('sonata.page.admin.block')->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        $id = $request->get($this->admin->getIdParameter());
        $block = $this->get('sonata.page.admin.block')->getObject($id);
        if (!$block) {
            throw new NotFoundHttpException(sprintf('unable to find the block with id : %s', $id));
        }

        $blockServices = $this->get('sonata.block.manager')->getServicesByContext('sonata_page_bundle', false);

        // filter service using the template configuration
        if ($page = $block->getPage()) {
            $template = $this->get('sonata.page.template_manager')->get($page->getTemplateCode());

            $container = $template->getContainer($block->getSetting('code'));

            if (isset($container['blocks']) && \count($container['blocks']) > 0) {
                foreach ($blockServices as $code => $service) {
                    if (\in_array($code, $container['blocks'], true)) {
                        continue;
                    }

                    unset($blockServices[$code]);
                }
            }
        }

        return $this->render($this->admin->getTemplate('compose_container_show'), [
            'blockServices' => $blockServices,
            'container' => $block,
            'page' => $block->getPage(),
        ]);
    }

    /**
     * Sets the admin form theme to form view. Used for compatibility between Symfony versions.
     */
    private function setFormTheme(FormView $formView, $theme)
    {
        $twig = $this->get('twig');

        $twig->getRuntime(FormRenderer::class)->setTheme($formView, $theme);
    }

    /**
     * NEXT_MAJOR: Check if it was added in SonataAdminBundle, if yes remove this method!
     * @internal
     */
    protected function getAdmin(): AdminInterface
    {
        return $this->admin;
    }
}
