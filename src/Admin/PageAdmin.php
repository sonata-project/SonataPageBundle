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

namespace Sonata\PageBundle\Admin;

use Knp\Menu\ItemInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\PageBundle\Exception\InternalErrorException;
use Sonata\PageBundle\Form\Type\PageSelectorType;
use Sonata\PageBundle\Form\Type\PageTypeChoiceType;
use Sonata\PageBundle\Form\Type\TemplateChoiceType;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @extends AbstractAdmin<PageInterface>
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class PageAdmin extends AbstractAdmin
{
    protected $classnameLabel = 'Page';

    private PageManagerInterface $pageManager;

    private SiteManagerInterface $siteManager;

    public function __construct(
        PageManagerInterface $pageManager,
        SiteManagerInterface $siteManager
    ) {
        parent::__construct();

        $this->pageManager = $pageManager;
        $this->siteManager = $siteManager;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('compose', $this->getRouterIdParameter().'/compose');
        $collection->add('compose_container_show', 'compose/container/'.$this->getRouterIdParameter());
        $collection->add('tree', 'tree');
    }

    protected function preUpdate(object $object): void
    {
        $object->setEdited(true);
    }

    protected function prePersist(object $object): void
    {
        $object->setEdited(true);
    }

    protected function getAccessMapping(): array
    {
        return [
            'tree' => AdminPermissionMap::PERMISSION_LIST,
            'compose' => AdminPermissionMap::PERMISSION_EDIT,
        ];
    }

    protected function configureBatchActions(array $actions): array
    {
        $actions = parent::configureBatchActions($actions);

        $actions['snapshot'] = [
            'label' => 'create_snapshot',
            'ask_confirmation' => true,
        ];

        return $actions;
    }

    protected function alterNewInstance(object $object): void
    {
        if (!$this->hasRequest()) {
            return;
        }

        $site = $this->getSite();
        $object->setSite($site);

        if (null !== $site && null !== $this->getRequest()->get('url')) {
            $slugs = explode('/', $this->getRequest()->get('url'));
            $slug = array_pop($slugs);

            $parent = $this->pageManager->getPageByUrl($site, implode('/', $slugs)) ??
                $this->pageManager->getPageByUrl($site, '/');

            if (null === $parent) {
                throw new InternalErrorException('Unable to find the root url, please create a route with url = /');
            }

            $object->setSlug(urldecode($slug));
            $object->setParent($parent);
            $object->setName(urldecode($slug));
        }
    }

    protected function configurePersistentParameters(): array
    {
        $parameters = [];
        $key = sprintf('%s.current_site', $this->getCode());

        if (!$this->hasRequest()) {
            return $parameters;
        }

        $request = $this->getRequest();

        $site = $request->get('site', null);

        if (null !== $site) {
            $request->getSession()->set($key, $site);
        }

        $site = $request->getSession()->get($key, null);

        if (null !== $site) {
            $parameters['site'] = $site;
        }

        return $parameters;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('site')
            ->add('routeName')
            ->add('pageAlias')
            ->add('type')
            ->add('enabled')
            ->add('decorate')
            ->add('name')
            ->add('slug')
            ->add('customUrl')
            ->add('edited');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('hybrid', null, ['template' => '@SonataPage/PageAdmin/field_hybrid.html.twig'])
            ->addIdentifier('name')
            ->add('type')
            ->add('pageAlias')
            ->add('site', null, [
                'sortable' => 'site.name',
            ])
            ->add('decorate', null, ['editable' => true])
            ->add('enabled', null, ['editable' => true])
            ->add('edited', null, ['editable' => true])
            ->add(ListMapper::NAME_ACTIONS, ListMapper::TYPE_ACTIONS, [
                'translation_domain' => 'SonataAdminBundle',
                'actions' => [
                    'edit' => [],
                ],
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('site')
            ->add('name')
            ->add('type', null, ['field_type' => PageTypeChoiceType::class])
            ->add('pageAlias')
            ->add('parent')
            ->add('edited')
            ->add('hybrid', CallbackFilter::class, [
                'callback' => static function (ProxyQueryInterface $queryBuilder, string $alias, string $field, array $data): void {
                    $builder = $queryBuilder->getQueryBuilder();

                    if (\in_array($data['value'], ['hybrid', 'cms'], true)) {
                        $builder->andWhere(sprintf('%s.routeName %s :routeName', $alias, 'cms' === $data['value'] ? '=' : '!='));
                        $builder->setParameter('routeName', PageInterface::PAGE_ROUTE_CMS_NAME);
                    }
                },
                'field_options' => [
                    'required' => false,
                    'choices' => [
                        'hybrid' => 'hybrid',
                        'cms' => 'cms',
                    ],
                    'choice_translation_domain' => 'SonataPageBundle',
                ],
                'field_type' => ChoiceType::class,
            ]);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        // define group zoning
        $form
             ->with('main', ['class' => 'col-md-6'])->end()
             ->with('seo', ['class' => 'col-md-6'])->end()
             ->with('advanced', ['class' => 'col-md-6'])->end();

        $page = $this->hasSubject() ? $this->getSubject() : null;
        $site = null !== $page ? $page->getSite() : null;

        if (null === $page || (!$page->isInternal() && !$page->isError())) {
            $form
                ->with('main')
                    ->add('url', TextType::class, ['attr' => ['readonly' => true]])
                ->end();
        }

        if (null !== $page && null === $page->getId()) {
            $form
                ->with('main')
                    ->add('site', null, ['required' => true, 'attr' => ['readonly' => true]])
                ->end();
        }

        $form
            ->with('main')
                ->add('name', null, ['help' => 'help_page_name'])
                ->add('enabled', null, ['required' => false])
                ->add('position')
            ->end();

        if (null !== $page && !$page->isInternal()) {
            $form
                ->with('main')
                    ->add('type', PageTypeChoiceType::class, ['required' => false])
                ->end();
        }

        $form
            ->with('main')
                ->add('templateCode', TemplateChoiceType::class, ['required' => true])
            ->end();

        if (null === $page || null === $page->getParent() || null === $page->getId()) {
            $form
                ->with('main')
                    ->add('parent', PageSelectorType::class, [
                        'page' => $page,
                        'site' => $site,
                        'model_manager' => $this->getModelManager(),
                        'class' => $this->getClass(),
                        'required' => false,
                        'filter_choice' => ['hierarchy' => 'root'],
                    ], [
                        'admin_code' => $this->getCode(),
                        'link_parameters' => [
                            'siteId' => null !== $site ? $site->getId() : null,
                        ],
                    ])
                ->end();
        }

        if (null === $page || !$page->isDynamic()) {
            $form
                ->with('main')
                    ->add('pageAlias', null, ['required' => false])
                    ->add('parent', PageSelectorType::class, [
                        'page' => $page,
                        'site' => $site,
                        'model_manager' => $this->getModelManager(),
                        'class' => $this->getClass(),
                        'filter_choice' => ['request_method' => 'all'],
                        'required' => false,
                    ], [
                        'admin_code' => $this->getCode(),
                        'link_parameters' => [
                            'siteId' => null !== $site ? $site->getId() : null,
                        ],
                    ])
                ->end();
        }

        if (null === $page || !$page->isHybrid()) {
            $form
                ->with('seo')
                    ->add('slug', TextType::class, ['required' => false])
                    ->add('customUrl', TextType::class, ['required' => false])
                ->end();
        }

        $form
            ->with('seo', ['collapsed' => true])
                ->add('title', null, ['required' => false])
                ->add('metaKeyword', TextareaType::class, ['required' => false])
                ->add('metaDescription', TextareaType::class, ['required' => false])
            ->end();

        if (null !== $page && !$page->isCms()) {
            $form
                ->with('advanced', ['collapsed' => true])
                    ->add('decorate', null, ['required' => false])
                ->end();
        }

        $form
            ->with('advanced', ['collapsed' => true])
                ->add('javascript', null, ['required' => false])
                ->add('stylesheet', null, ['required' => false])
                ->add('rawHeaders', null, ['required' => false])
            ->end();
    }

    protected function configureTabMenu(ItemInterface $menu, string $action, ?AdminInterface $childAdmin = null): void
    {
        if (null === $childAdmin && 'edit' !== $action) {
            return;
        }

        if (!$this->hasRequest()) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;

        $id = $admin->getRequest()->get('id');

        $menu->addChild(
            'sidemenu.link_edit_page',
            $admin->generateMenuUrl('edit', ['id' => $id])
        );

        $menu->addChild(
            'sidemenu.link_compose_page',
            $admin->generateMenuUrl('compose', ['id' => $id])
        );

        $menu->addChild(
            'sidemenu.link_list_blocks',
            $admin->generateMenuUrl('sonata.page.admin.block.list', ['id' => $id])
        );

        $menu->addChild(
            'sidemenu.link_list_snapshots',
            $admin->generateMenuUrl('sonata.page.admin.snapshot.list', ['id' => $id])
        );

        $page = $this->getSubject();
        if (!$page->isHybrid() && !$page->isInternal()) {
            try {
                $path = $page->getUrl();
                $site = $page->getSite();

                if (null !== $site) {
                    $siteRelativePath = $site->getRelativePath();

                    if (null !== $siteRelativePath) {
                        $path = $siteRelativePath.$path;
                    }
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

    /**
     * @throws \RuntimeException
     */
    private function getSite(): ?SiteInterface
    {
        if (!$this->hasRequest()) {
            return null;
        }

        $siteId = null;

        if ('POST' === $this->getRequest()->getMethod()) {
            $values = $this->getRequest()->get($this->getUniqId());
            $siteId = $values['site'] ?? null;
        }

        $siteId ??= $this->getRequest()->get('siteId');

        if (null !== $siteId) {
            $site = $this->siteManager->findOneBy(['id' => $siteId]);

            if (null === $site) {
                throw new \RuntimeException('Unable to find the site with id='.$this->getRequest()->get('siteId'));
            }

            return $site;
        }

        return null;
    }
}
