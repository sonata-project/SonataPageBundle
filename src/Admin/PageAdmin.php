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

use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\PageBundle\Exception\InternalErrorException;
use Sonata\PageBundle\Exception\PageNotFoundException;
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
 * Admin definition for the Page class.
 *
 * @extends AbstractAdmin<PageInterface>
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class PageAdmin extends AbstractAdmin
{
    protected $classnameLabel = 'Page';

    /**
     * @var PageManagerInterface
     */
    protected $pageManager;

    /**
     * @var SiteManagerInterface
     */
    protected $siteManager;

    protected $accessMapping = [
        'tree' => 'LIST',
        'compose' => 'EDIT',
    ];

    public function configureRoutes(RouteCollection $collection): void
    {
        $collection->add('compose', '{id}/compose', [
            'id' => null,
        ]);
        $collection->add('compose_container_show', 'compose/container/{id}', [
            'id' => null,
        ]);

        $collection->add('tree', 'tree');
    }

    public function preUpdate($object): void
    {
        $object->setEdited(true);
    }

    public function prePersist($object): void
    {
        $object->setEdited(true);
    }

    public function setPageManager(PageManagerInterface $pageManager): void
    {
        $this->pageManager = $pageManager;
    }

    /**
     * @throws \RuntimeException
     *
     * @return SiteInterface|bool
     */
    public function getSite()
    {
        if (!$this->hasRequest()) {
            return false;
        }

        $siteId = null;

        if ('POST' === $this->getRequest()->getMethod()) {
            $values = $this->getRequest()->get($this->getUniqid());
            $siteId = $values['site'] ?? null;
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

    public function setSiteManager(SiteManagerInterface $siteManager): void
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

    protected function configureBatchActions($actions): array
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

        if ($site = $this->getSite()) {
            $object->setSite($site);
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

            $object->setSlug(urldecode($slug));
            $object->setParent($parent ?: null);
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

        if ($site = $this->request->get('site', null)) {
            $this->request->getSession()->set($key, $site);
        }

        if ($site = $this->request->getSession()->get($key, null)) {
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
            ->add('hybrid', 'text', ['template' => '@SonataPage/PageAdmin/field_hybrid.html.twig'])
            ->addIdentifier('name')
            ->add('type')
            ->add('pageAlias')
            ->add('site', null, [
                'sortable' => 'site.name',
            ])
            ->add('decorate', null, ['editable' => true])
            ->add('enabled', null, ['editable' => true])
            ->add('edited', null, ['editable' => true]);
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
                'callback' => static function ($queryBuilder, $alias, $field, $data): void {
                    if (\in_array($data['value'], ['hybrid', 'cms'], true)) {
                        $queryBuilder->andWhere(sprintf('%s.routeName %s :routeName', $alias, 'cms' === $data['value'] ? '=' : '!='));
                        $queryBuilder->setParameter('routeName', PageInterface::PAGE_ROUTE_CMS_NAME);
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
             ->with('form_page.group_main_label', ['class' => 'col-md-6'])->end()
             ->with('form_page.group_seo_label', ['class' => 'col-md-6'])->end()
             ->with('form_page.group_advanced_label', ['class' => 'col-md-6'])->end();

        $page = $this->hasSubject() ? $this->getSubject() : null;

        if (null === $page || (!$page->isInternal() && !$page->isError())) {
            $form
                ->with('form_page.group_main_label')
                    ->add('url', TextType::class, ['attr' => ['readonly' => true]])
                ->end();
        }

        if (null !== $page && null === $page->getId()) {
            $form
                ->with('form_page.group_main_label')
                    ->add('site', null, ['required' => true, 'attr' => ['readonly' => true]])
                ->end();
        }

        $form
            ->with('form_page.group_main_label')
                ->add('name')
                ->add('enabled', null, ['required' => false])
                ->add('position')
            ->end();

        if (null !== $page && !$page->isInternal()) {
            $form
                ->with('form_page.group_main_label')
                    ->add('type', PageTypeChoiceType::class, ['required' => false])
                ->end();
        }

        $form
            ->with('form_page.group_main_label')
                ->add('templateCode', TemplateChoiceType::class, ['required' => true])
            ->end();

        if (null === $page || null === $page->getParent() || null === $page->getId()) {
            $form
                ->with('form_page.group_main_label')
                    ->add('parent', PageSelectorType::class, [
                        'page' => $page ?: null,
                        'site' => $page ? $page->getSite() : null,
                        'model_manager' => $this->getModelManager(),
                        'class' => $this->getClass(),
                        'required' => false,
                        'filter_choice' => ['hierarchy' => 'root'],
                    ], [
                        'admin_code' => $this->getCode(),
                        'link_parameters' => [
                            'siteId' => $page && $page->getSite() ? $page->getSite()->getId() : null,
                        ],
                    ])
                ->end();
        }

        if (null === $page || !$page->isDynamic()) {
            $form
                ->with('form_page.group_main_label')
                    ->add('pageAlias', null, ['required' => false])
                    ->add('parent', PageSelectorType::class, [
                        'page' => $page ?: null,
                        'site' => $page ? $page->getSite() : null,
                        'model_manager' => $this->getModelManager(),
                        'class' => $this->getClass(),
                        'filter_choice' => ['request_method' => 'all'],
                        'required' => false,
                    ], [
                        'admin_code' => $this->getCode(),
                        'link_parameters' => [
                            'siteId' => null !== $page && null !== $page->getSite() ? $page->getSite()->getId() : null,
                        ],
                    ])
                ->end();
        }

        if (null === $page || !$page->isHybrid()) {
            $form
                ->with('form_page.group_seo_label')
                    ->add('slug', TextType::class, ['required' => false])
                    ->add('customUrl', TextType::class, ['required' => false])
                ->end();
        }

        $form
            ->with('form_page.group_seo_label', ['collapsed' => true])
                ->add('title', null, ['required' => false])
                ->add('metaKeyword', TextareaType::class, ['required' => false])
                ->add('metaDescription', TextareaType::class, ['required' => false])
            ->end();

        if (null !== $page && !$page->isCms()) {
            $form
                ->with('form_page.group_advanced_label', ['collapsed' => true])
                    ->add('decorate', null, ['required' => false])
                ->end();
        }

        $form
            ->with('form_page.group_advanced_label', ['collapsed' => true])
                ->add('javascript', null, ['required' => false])
                ->add('stylesheet', null, ['required' => false])
                ->add('rawHeaders', null, ['required' => false])
            ->end();

        $form->setHelps([
            'name' => 'help_page_name',
        ]);
    }

    protected function configureTabMenu(MenuItemInterface $menu, $action, ?AdminInterface $childAdmin = null): void
    {
        if (!$childAdmin && !\in_array($action, ['edit'], true)) {
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
