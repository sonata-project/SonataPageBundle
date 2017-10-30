<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Admin;

use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Cache\CacheManagerInterface;
use Sonata\PageBundle\Exception\InternalErrorException;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Sonata\PageBundle\Form\Type\PageSelectorType;
use Sonata\PageBundle\Form\Type\PageTypeChoiceType;
use Sonata\PageBundle\Form\Type\TemplateChoiceType;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Admin definition for the Page class.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class PageAdmin extends AbstractAdmin
{
    /**
     * @var PageManagerInterface
     */
    protected $pageManager;

    /**
     * @var SiteManagerInterface
     */
    protected $siteManager;

    /**
     * @var CacheManagerInterface
     */
    protected $cacheManager;

    /**
     * {@inheritdoc}
     */
    protected $accessMapping = [
        'tree' => 'LIST',
        'compose' => 'EDIT',
    ];

    /**
     * {@inheritdoc}
     */
    public function configureRoutes(RouteCollection $collection)
    {
        $collection->add('compose', '{id}/compose', [
            'id' => null,
        ]);
        $collection->add('compose_container_show', 'compose/container/{id}', [
            'id' => null,
        ]);

        $collection->add('tree', 'tree');
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($object)
    {
        $object->setEdited(true);
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate($object)
    {
        if ($this->cacheManager) {
            $this->cacheManager->invalidate([
                'page_id' => $object->getId(),
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
        $object->setEdited(true);
    }

    /**
     * @param PageManagerInterface $pageManager
     */
    public function setPageManager(PageManagerInterface $pageManager)
    {
        $this->pageManager = $pageManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewInstance()
    {
        $instance = parent::getNewInstance();

        if (!$this->hasRequest()) {
            return $instance;
        }

        if ($site = $this->getSite()) {
            $instance->setSite($site);
        }

        if ($site && $this->getRequest()->get('url')) {
            $slugs = explode('/', $this->getRequest()->get('url'));
            $slug = array_pop($slugs);

            try {
                $parent = $this->pageManager->getPageByUrl($site, implode('/', $slugs));
            } catch (PageNotFoundException $e) {
                try {
                    $parent = $this->pageManager->getPageByUrl($site, '/');
                } catch (PageNotFoundException $e) {
                    throw new InternalErrorException('Unable to find the root url, please create a route with url = /');
                }
            }

            $instance->setSlug(urldecode($slug));
            $instance->setParent($parent ?: null);
            $instance->setName(urldecode($slug));
        }

        return $instance;
    }

    /**
     * @return SiteInterface|bool
     *
     * @throws \RuntimeException
     */
    public function getSite()
    {
        if (!$this->hasRequest()) {
            return false;
        }

        $siteId = null;

        if ('POST' == $this->getRequest()->getMethod()) {
            $values = $this->getRequest()->get($this->getUniqid());
            $siteId = isset($values['site']) ? $values['site'] : null;
        }

        $siteId = (null !== $siteId) ? $siteId : $this->getRequest()->get('siteId');

        if ($siteId) {
            $site = $this->siteManager->findOneBy(['id' => $siteId]);

            if (!$site) {
                throw new \RuntimeException('Unable to find the site with id='.$this->getRequest()->get('siteId'));
            }

            return $site;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getBatchActions()
    {
        $actions = parent::getBatchActions();

        $actions['snapshot'] = [
            'label' => 'create_snapshot',
            'translation_domain' => $this->getTranslationDomain(),
            'ask_confirmation' => true,
        ];

        return $actions;
    }

    /**
     * @param SiteManagerInterface $siteManager
     */
    public function setSiteManager(SiteManagerInterface $siteManager)
    {
        $this->siteManager = $siteManager;
    }

    /**
     * @return SiteInterface[]
     */
    public function getSites()
    {
        return $this->siteManager->findBy([]);
    }

    /**
     * @param CacheManagerInterface $cacheManager
     */
    public function setCacheManager(CacheManagerInterface $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getPersistentParameters()
    {
        $parameters = parent::getPersistentParameters();
        $key = sprintf('%s.current_site', $this->getCode());

        if ($site = $this->request->get('site', null)) {
            $this->request->getSession()->set($key, $site);
        }

        if ($site = $this->request->getSession()->get($key, null)) {
            $parameters['site'] = $site;
        }

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('site')
            ->add('routeName')
            ->add('pageAlias')
            ->add('type')
            ->add('enabled')
            ->add('decorate')
            ->add('name')
            ->add('slug')
            ->add('customUrl')
            ->add('edited')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('hybrid', 'text', ['template' => 'SonataPageBundle:PageAdmin:field_hybrid.html.twig'])
            ->addIdentifier('name')
            ->add('type')
            ->add('pageAlias')
            ->add('site', null, [
                'sortable' => 'site.name',
            ])
            ->add('decorate', null, ['editable' => true])
            ->add('enabled', null, ['editable' => true])
            ->add('edited', null, ['editable' => true])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('site')
            ->add('name')
            ->add('type', null, ['field_type' => PageTypeChoiceType::class])
            ->add('pageAlias')
            ->add('parent')
            ->add('edited')
            ->add('hybrid', 'doctrine_orm_callback', [
                'callback' => function ($queryBuilder, $alias, $field, $data) {
                    if (in_array($data['value'], ['hybrid', 'cms'])) {
                        $queryBuilder->andWhere(sprintf('%s.routeName %s :routeName', $alias, 'cms' == $data['value'] ? '=' : '!='));
                        $queryBuilder->setParameter('routeName', PageInterface::PAGE_ROUTE_CMS_NAME);
                    }
                },
                'field_options' => [
                    'required' => false,
                    'choices' => [
                        'hybrid' => $this->trans('hybrid'),
                        'cms' => $this->trans('cms'),
                    ],
                    'choice_translation_domain' => false,
                ],
                'field_type' => 'choice',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        // define group zoning
        $formMapper
             ->with('form_page.group_main_label', ['class' => 'col-md-6'])->end()
             ->with('form_page.group_seo_label', ['class' => 'col-md-6'])->end()
             ->with('form_page.group_advanced_label', ['class' => 'col-md-6'])->end()
        ;

        if (!$this->getSubject() || (!$this->getSubject()->isInternal() && !$this->getSubject()->isError())) {
            $formMapper
                ->with('form_page.group_main_label')
                    ->add('url', TextType::class, ['attr' => ['readonly' => true]])
                ->end()
            ;
        }

        if ($this->hasSubject() && !$this->getSubject()->getId()) {
            $formMapper
                ->with('form_page.group_main_label')
                    ->add('site', null, ['required' => true, 'attr' => ['readonly' => true]])
                ->end()
            ;
        }

        $formMapper
            ->with('form_page.group_main_label')
                ->add('name')
                ->add('enabled', null, ['required' => false])
                ->add('position')
            ->end()
        ;

        if ($this->hasSubject() && !$this->getSubject()->isInternal()) {
            $formMapper
                ->with('form_page.group_main_label')
                    ->add('type', PageTypeChoiceType::class, ['required' => false])
                ->end()
            ;
        }

        $formMapper
            ->with('form_page.group_main_label')
                ->add('templateCode', TemplateChoiceType::class, ['required' => true])
            ->end()
        ;

        if (!$this->getSubject() || ($this->getSubject() && $this->getSubject()->getParent()) || ($this->getSubject() && !$this->getSubject()->getId())) {
            $formMapper
                ->with('form_page.group_main_label')
                    ->add('parent', PageSelectorType::class, [
                        'page' => $this->getSubject() ?: null,
                        'site' => $this->getSubject() ? $this->getSubject()->getSite() : null,
                        'model_manager' => $this->getModelManager(),
                        'class' => $this->getClass(),
                        'required' => false,
                        'filter_choice' => ['hierarchy' => 'root'],
                    ], [
                        'admin_code' => $this->getCode(),
                        'link_parameters' => [
                            'siteId' => $this->getSubject() ? $this->getSubject()->getSite()->getId() : null,
                        ],
                    ])
                ->end()
            ;
        }

        if (!$this->getSubject() || !$this->getSubject()->isDynamic()) {
            $formMapper
                ->with('form_page.group_main_label')
                    ->add('pageAlias', null, ['required' => false])
                    ->add('parent', PageSelectorType::class, [
                        'page' => $this->getSubject() ?: null,
                        'site' => $this->getSubject() ? $this->getSubject()->getSite() : null,
                        'model_manager' => $this->getModelManager(),
                        'class' => $this->getClass(),
                        'filter_choice' => ['request_method' => 'all'],
                        'required' => false,
                    ], [
                        'admin_code' => $this->getCode(),
                        'link_parameters' => [
                            'siteId' => $this->getSubject() ? $this->getSubject()->getSite()->getId() : null,
                        ],
                    ])
                ->end()
            ;
        }

        if (!$this->getSubject() || !$this->getSubject()->isHybrid()) {
            $formMapper
                ->with('form_page.group_seo_label')
                    ->add('slug', TextType::class, ['required' => false])
                    ->add('customUrl', TextType::class, ['required' => false])
                ->end()
            ;
        }

        $formMapper
            ->with('form_page.group_seo_label', ['collapsed' => true])
                ->add('title', null, ['required' => false])
                ->add('metaKeyword', TextareaType::class, ['required' => false])
                ->add('metaDescription', TextareaType::class, ['required' => false])
            ->end()
        ;

        if ($this->hasSubject() && !$this->getSubject()->isCms()) {
            $formMapper
                ->with('form_page.group_advanced_label', ['collapsed' => true])
                    ->add('decorate', null, ['required' => false])
                ->end()
            ;
        }

        $formMapper
            ->with('form_page.group_advanced_label', ['collapsed' => true])
                ->add('javascript', null, ['required' => false])
                ->add('stylesheet', null, ['required' => false])
                ->add('rawHeaders', null, ['required' => false])
            ->end()
        ;

        $formMapper->setHelps([
            'name' => 'help_page_name',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        if (!$childAdmin && !in_array($action, ['edit'])) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;

        $id = $admin->getRequest()->get('id');

        $menu->addChild('sidemenu.link_edit_page',
            $admin->generateMenuUrl('edit', ['id' => $id])
        );

        $menu->addChild('sidemenu.link_compose_page',
            $admin->generateMenuUrl('compose', ['id' => $id])
        );

        $menu->addChild('sidemenu.link_list_blocks',
            $admin->generateMenuUrl('sonata.page.admin.block.list', ['id' => $id])
        );

        $menu->addChild('sidemenu.link_list_snapshots',
            $admin->generateMenuUrl('sonata.page.admin.snapshot.list', ['id' => $id])
        );

        $page = $this->getSubject();
        if (!$page->isHybrid() && !$page->isInternal()) {
            try {
                $path = $page->getUrl();
                $siteRelativePath = $page->getSite()->getRelativePath();
                if (!empty($siteRelativePath)) {
                    $path = $siteRelativePath.$path;
                }
                $menu->addChild('view_page', [
                    'uri' => $this->getRouteGenerator()->generate('page_slug', [
                        'path' => $path,
                    ]),
                ]);
            } catch (\Exception $e) {
                // avoid crashing the admin if the route is not setup correctly
                // throw $e;
            }
        }
    }
}
