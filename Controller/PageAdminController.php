<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Page Admin Controller.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class PageAdminController extends Controller
{
    /**
     * @param mixed $query
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException
     */
    public function batchActionSnapshot($query)
    {
        if (false === $this->get('sonata.page.admin.snapshot')->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        foreach ($query->execute() as $page) {
            $this->get('sonata.notification.backend')
                ->createAndPublish('sonata.page.create_snapshot', array(
                    'pageId' => $page->getId(),
                ));
        }

        return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
    }

    /**
     * {@inheritdoc}
     */
    public function listAction(Request $request = null)
    {
        if (!$request->get('filter')) {
            return new RedirectResponse($this->admin->generateUrl('tree'));
        }

        return parent::listAction();
    }

    /**
     * @param Request|null $request
     *
     * @return Response
     */
    public function treeAction(Request $request = null)
    {
        $this->admin->checkAccess('tree');

        $sites = $this->get('sonata.page.manager.site')->findBy(array());
        $pageManager = $this->get('sonata.page.manager.page');

        $currentSite = null;
        $siteId = $request->get('site');
        foreach ($sites as $site) {
            if ($siteId && $site->getId() == $siteId) {
                $currentSite = $site;
            } elseif (!$siteId && $site->getIsDefault()) {
                $currentSite = $site;
            }
        }
        if (!$currentSite && count($sites) == 1) {
            $currentSite = $sites[0];
        }

        if ($currentSite) {
            $pages = $pageManager->loadPages($currentSite);
        } else {
            $pages = array();
        }

        $datagrid = $this->admin->getDatagrid();
        $formView = $datagrid->getForm()->createView();

        $this->get('twig')->getExtension('form')->renderer->setTheme($formView, $this->admin->getFilterTheme());

        return $this->render($this->admin->getTemplate('tree'), array(
            'action' => 'tree',
            'sites' => $sites,
            'currentSite' => $currentSite,
            'pages' => $pages,
            'form' => $formView,
            'csrf_token' => $this->getCsrfToken('sonata.batch'),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function createAction(Request $request = null)
    {
        $this->admin->checkAccess('create');

        if ($request->getMethod() == 'GET' && !$this->getRequest()->get('siteId')) {
            $sites = $this->get('sonata.page.manager.site')->findBy(array());

            if (count($sites) == 1) {
                return $this->redirect($this->admin->generateUrl('create', array(
                    'siteId' => $sites[0]->getId(),
                    'uniqid' => $this->admin->getUniqid(),
                ) + $request->query->all()));
            }

            try {
                $current = $this->get('sonata.page.site.selector')->retrieve();
            } catch (\RuntimeException $e) {
                $current = false;
            }

            return $this->render($this->admin->getTemplate('select_site'), array(
                'sites' => $sites,
                'current' => $current,
            ));
        }

        return parent::createAction();
    }

    /**
     * @param Request|null $request
     *
     * @return Response
     *
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    public function composeAction(Request $request = null)
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

        $containers = array();
        $orphanContainers = array();
        $children = array();

        $templateManager = $this->get('sonata.page.template_manager');
        $template = $templateManager->get($page->getTemplateCode());
        $templateContainers = $template->getContainers();

        foreach ($templateContainers as $id => $container) {
            $containers[$id] = array(
                'area' => $container,
                'block' => false,
            );
        }

        // 'attach' containers to corresponding template area, otherwise add it to orphans
        foreach ($page->getBlocks() as $block) {
            $blockCode = $block->getSetting('code');
            if ($block->getParent() === null) {
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
            if ($container['block'] === false && $templateContainers[$id]['shared'] === false) {
                $blockContainer = $blockInteractor->createNewContainer(array(
                    'page' => $page,
                    'name' => $templateContainers[$id]['name'],
                    'code' => $id,
                ));

                $containers[$id]['block'] = $blockContainer;
            }
        }

        return $this->render($this->admin->getTemplate('compose'), array(
            'object' => $page,
            'action' => 'edit',
            'template' => $template,
            'page' => $page,
            'containers' => $containers,
            'orphanContainers' => $orphanContainers,
            'csrfTokens' => array(
                'remove' => $this->getCsrfToken('sonata.delete'),
            ),
        ));
    }

    /**
     * @param Request|null $request
     *
     * @return Response
     *
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    public function composeContainerShowAction(Request $request = null)
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

            if (isset($container['blocks']) && count($container['blocks']) > 0) {
                foreach ($blockServices as $code => $service) {
                    if (in_array($code, $container['blocks'])) {
                        continue;
                    }

                    unset($blockServices[$code]);
                }
            }
        }

        return $this->render($this->admin->getTemplate('compose_container_show'), array(
            'blockServices' => $blockServices,
            'container' => $block,
            'page' => $block->getPage(),
        ));
    }
}
