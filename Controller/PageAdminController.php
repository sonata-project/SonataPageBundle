<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Page Admin Controller
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class PageAdminController extends Controller
{
    /**
     * @param mixed $query
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        if (!$this->getRequest()->get('filter')) {
            return new RedirectResponse($this->admin->generateUrl('tree'));
        }

        return parent::listAction();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function treeAction()
    {
        if (false === $this->admin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        $sites = $this->get('sonata.page.manager.site')->findBy(array());
        $pageManager = $this->get('sonata.page.manager.page');

        $currentSite = null;
        $siteId = $this->getRequest()->get('site');
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

        return $this->render('SonataPageBundle:PageAdmin:tree.html.twig', array(
            'action'      => 'tree',
            'sites'       => $sites,
            'currentSite' => $currentSite,
            'pages'       => $pages,
            'form'        => $formView,
            'csrf_token'  => $this->getCsrfToken('sonata.batch'),
        ));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function createAction()
    {
        if (false === $this->admin->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        if ($this->getRequest()->getMethod() == 'GET' && !$this->getRequest()->get('siteId')) {
            $sites = $this->get('sonata.page.manager.site')->findBy(array());

            if (count($sites) == 1) {
                return $this->redirect($this->admin->generateUrl('create', array(
                    'siteId' => $sites[0]->getId(),
                    'uniqid' => $this->admin->getUniqid()
                )));
            }

            try {
                $current = $this->get('sonata.page.site.selector')->retrieve();
            } catch (\RuntimeException $e) {
                $current = false;
            }

            return $this->render('SonataPageBundle:PageAdmin:select_site.html.twig', array(
                'sites'   => $sites,
                'current' => $current,
            ));
        }

        return parent::createAction();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws NotFoundHttpException
     */
    public function composeAction()
    {
        if (
            false === $this->admin->isGranted('EDIT')
            || false === $this->get('sonata.page.admin.block')->isGranted('LIST')
        ) {
            throw new AccessDeniedException();
        }

        $id   = $this->get('request')->get($this->admin->getIdParameter());
        $page = $this->admin->getObject($id);
        if (!$page) {
            throw new NotFoundHttpException(sprintf('unable to find the page with id : %s', $id));
        }

        $containers       = array();
        $orphanContainers = array();
        $children         = array();

        $templateManager    = $this->get('sonata.page.template_manager');
        $template           = $templateManager->get($page->getTemplateCode());
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

        $csrfProvider = $this->get('form.csrf_provider');

        return $this->render('SonataPageBundle:PageAdmin:compose.html.twig', array(
            'object'           => $page,
            'action'           => 'edit',
            'template'         => $template,
            'page'             => $page,
            'containers'       => $containers,
            'orphanContainers' => $orphanContainers,
            'csrfTokens'       => array(
                'remove' => $csrfProvider->generateCsrfToken('sonata.delete'),
            ),
        ));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws NotFoundHttpException
     */
    public function composeContainerShowAction()
    {
        if (false === $this->get('sonata.page.admin.block')->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        $id    = $this->get('request')->get($this->admin->getIdParameter());
        $block = $this->get('sonata.page.admin.block')->getObject($id);
        if (!$block) {
            throw new NotFoundHttpException(sprintf('unable to find the block with id : %s', $id));
        }

        $blockServices = $this->get('sonata.block.manager')->getServicesByContext('sonata_page_bundle', false);

        return $this->render('SonataPageBundle:PageAdmin:compose_container_show.html.twig', array(
            'blockServices' => $blockServices,
            'container'     => $block,
            'page'          => $block->getPage(),
        ));
    }
}
