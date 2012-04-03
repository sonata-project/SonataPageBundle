<?php

namespace Sonata\PageBundle\Model;

use Sonata\BlockBundle\Model\BlockInterface;

class SnapshotPageProxy implements PageInterface
{
    /**
     * @var \Sonata\PageBundle\Model\SnapshotManagerInterface
     */
    private $manager;

    /**
     * @var \Sonata\PageBundle\Model\SnapshotInterface
     */
    private $snapshot;

    /**
     * @var \Sonata\PageBundle\Model\PageInterface
     */
    private $page;

    private $target;

    private $parents;

    private $site;

    /**
     * @param \Sonata\PageBundle\Model\SnapshotManagerInterface $manager
     * @param \Sonata\PageBundle\Model\SnapshotInterface $snapshot
     */
    public function __construct(SnapshotManagerInterface $manager, SnapshotInterface $snapshot)
    {
        $this->manager  = $manager;
        $this->snapshot = $snapshot;
    }

    /**
     * Get the page
     *
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    public function getPage()
    {
        $this->load();

        return $this->page;
    }

    /**
     * load the page
     */
    private function load()
    {
        if (!$this->page) {
            $this->page = $this->manager->load($this->snapshot);
        }
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array(array($this->getPage(), $method), $arguments);
    }

    /**
     * Add children
     *
     * @param \Sonata\PageBundle\Model\PageInterface $children
     */
    public function addChildren(PageInterface $children)
    {
        $this->getPage()->addChildren($children);
    }

    /**
     * @param array $headers
     * @return void
     */
    public function setHeaders(array $headers = array())
    {
        $this->getPage()->setHeaders($headers);
    }

    /**
     * @param $name
     * @param $value
     * @return void
     */
    public function addHeader($name, $value)
    {
        $this->getPage()->addHeader($name, $value);
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->getPage()->getHeaders();
    }

    /**
     * Get children
     *
     * @return array
     */
    public function getChildren()
    {
        if (!$this->getPage()->getChildren()->count()) {
            $this->getPage()->setChildren(new SnapshotChildrenCollection($this->manager, $this->getPage()));
        }

        return $this->getPage()->getChildren();
    }

    /**
     * Add blocs
     *
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     */
    public function addBlocks(BlockInterface $block)
    {
        $this->getPage()->addBlocks($block);
    }

    /**
     * Get blocs
     *
     * @return array $blocks
     */
    public function getBlocks()
    {
        if (!count($this->getPage()->getBlocks())) {

            $content = json_decode($this->snapshot->getContent(), true);

            foreach ($content['blocks'] as $block) {
                $this->addBlocks($this->manager->loadBlock($block, $this->getPage()));
            }
        }

        return $this->getPage()->getBlocks();
    }

    /**
     * Set target
     *
     * @param \Sonata\PageBundle\Model\PageInterface $target
     */
    public function setTarget(PageInterface $target)
    {
        $this->target = $target;
    }

    /**
     * @return \Sonata\PageBundle\Model\PageInterface|null
     */
    public function getTarget()
    {
        if ($this->target === null) {
            $content = json_decode($this->snapshot->getContent(), true);

            if (isset($content['target_id'])) {
                $target = $this->manager->getPageById($content['target_id']);

                if ($target) {
                    $this->setTarget($target);
                } else {
                    $this->target = false;
                }
            }
        }

        return $this->target ?: null;
    }

    /**
     * Get parent
     *
     * @param int $level
     * @return null|\Sonata\PageBundle\Model\PageInterface $parent
     */
    public function getParent($level = -1)
    {
        $parents = $this->getParents();

        if ($level < 0) {
            $level = count($parents) + $level;
        }

        return isset($parents[$level]) ? $parents[$level] : null;
    }

    /**
     * Set parent
     *
     * @param array $parents
     */
    public function setParents(array $parents)
    {
        $this->parents = $parents;
    }

    /**
     * get the tree of the page
     *
     * @return array of Application\Sonata\PageBundle\Entity\Page
     */
    public function getParents()
    {
        if (!$this->parents) {
            $parents = array();

            $snapshot = $this->snapshot;

            while ($snapshot) {
                $content = json_decode($snapshot->getContent(), true);

                $parentId = $content['parent_id'];

                $snapshot = $parentId ? $this->manager->getSnapshotByPageId($parentId) : null;

                if ($snapshot) {
                    $parents[] = new SnapshotPageProxy($this->manager, $snapshot);
                }
            }

            $this->setParents(array_reverse($parents));
        }

        return $this->parents;
    }


    /**
     * Set parent
     *
     * @param PageInterface $parent
     */
    public function setParent(PageInterface $parent)
    {
        $this->getPage()->setParent($parent);
    }

    /**
     * Set template
     *
     * @param string $templateCode
     */
    public function setTemplateCode($templateCode)
    {
        $this->getPage()->setTemplateCode($templateCode);
    }

    /**
     * Get template
     *
     * @return string $templateCode
     */
    public function getTemplateCode()
    {
        return $this->getPage()->getTemplateCode();
    }

    /**
     * @param boolean $decorate
     * @return void
     */
    public function setDecorate($decorate)
    {
        $this->getPage()->setDecorate($decorate);
    }

    /**
     * get decorate
     *
     * @return boolean $decorate
     */
    public function getDecorate()
    {
        return $this->getPage()->getDecorate();
    }

    /**
     * @return bool
     */
    public function isHybrid()
    {
        return $this->getPage()->isHybrid();
    }

    /**
     * @param $position
     * @return void
     */
    public function setPosition($position)
    {
        $this->getPage()->setPosition($position);
    }

    /**
     * get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->getPage()->getPosition();
    }

    /**
     * @param string $method
     * @return void
     */
    public function setRequestMethod($method)
    {
        $this->getPage()->setRequestMethod($method);
    }

    /**
     * get request method
     *
     * @return string
     */
    public function getRequestMethod()
    {
        return $this->getPage()->getRequestMethod();
    }

    /**
     * Get id of the page
     *
     * @return integer
     */
    public function getId()
    {
        return $this->getPage()->getId();
    }

    /**
     * set id of the page
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->getPage()->setId($id);
    }

    /**
     * Get routeName
     *
     * @return string $routeName
     */
    public function getRouteName()
    {
        return $this->getPage()->getRouteName();
    }

    /**
     * Set routeName
     *
     * @param string $routeName
     */
    public function setRouteName($routeName)
    {
        $this->getPage()->setRouteName($routeName);
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->getPage()->setEnabled($enabled);
    }

    /**
     * Get enabled
     *
     * @return boolean $enabled
     */
    public function getEnabled()
    {
        return $this->getPage()->getEnabled();
    }

    /**
     * Set showInMenu
     *
     * @param boolean $showInMenu
     */
    public function setShowInMenu($showInMenu)
    {
        $this->getPage()->setShowInMenu($showInMenu);
    }

    /**
     * Get showInMenu
     *
     * @return boolean $showInMenu
     */
    public function getShowInMenu()
    {
        return $this->getPage()->getShowInMenu();
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->getPage()->setName($name);
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->getPage()->getName();
    }

    /**
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->getPage()->setSlug($slug);
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->getPage()->getSlug();
    }

    /**
     * Set Url
     *
     * @param string $url
     * @return void
     */
    public function setUrl($url)
    {
        $this->getPage()->setUrl($url);
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getPage()->getUrl();
    }

    /**
     * Set customUrl
     *
     * @param string $customUrl
     */
    public function setCustomUrl($customUrl)
    {
        $this->getPage()->setCustomUrl($customUrl);
    }

    /**
     * Get customUrl
     *
     * @return integer $customUrl
     */
    public function getCustomUrl()
    {
        return $this->getPage()->getCustomUrl();
    }

    /**
     * Set metaKeyword
     *
     * @param string $metaKeyword
     */
    public function setMetaKeyword($metaKeyword)
    {
        $this->getPage()->setMetaKeyword($metaKeyword);
    }

    /**
     * Get metaKeyword
     *
     * @return string $metaKeyword
     */
    public function getMetaKeyword()
    {
        return $this->getPage()->getMetaKeyword();
    }

    /**
     * Set metaDescription
     *
     * @param string $metaDescription
     */
    public function setMetaDescription($metaDescription)
    {
        $this->getPage()->setMetaDescription($metaDescription);
    }

    /**
     * Get metaDescription
     *
     * @return string $metaDescription
     */
    public function getMetaDescription()
    {
        return $this->getPage()->getMetaDescription();
    }

    /**
     * Set javascript
     *
     * @param string $javascript
     */
    public function setJavascript($javascript)
    {
        $this->getPage()->setJavascript($javascript);
    }

    /**
     * Get javascript
     *
     * @return string $javascript
     */
    public function getJavascript()
    {
        return $this->getPage()->getJavascript();
    }

    /**
     * Set stylesheet
     *
     * @param string $stylesheet
     */
    public function setStylesheet($stylesheet)
    {
        $this->getPage()->setStylesheet($stylesheet);
    }

    /**
     * Get stylesheet
     *
     * @return string $stylesheet
     */
    public function getStylesheet()
    {
        return $this->getPage()->getStylesheet();
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->getPage()->setCreatedAt($createdAt);
    }

    /**
     * Get createdAt
     *
     * @return datetime $createdAt
     */
    public function getCreatedAt()
    {
        return $this->getPage()->getCreatedAt();
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->getPage()->setUpdatedAt($updatedAt);
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->getPage()->getUpdatedAt();
    }

    /**
     * @return boolean
     */
    public function isDynamic()
    {
        return $this->getPage()->isDynamic();
    }

    /**
     *
     * @return boolean
     */
    public function isCms()
    {
        return $this->getPage()->isCms();
    }

    /**
     *
     * @return boolean
     */
    public function isInternal()
    {
        return $this->getPage()->isInternal();
    }

    /**
     * Return the TTL value in second
     *
     *
     * @return integer
     */
    public function getTtl()
    {
        return $this->getPage()->getTtl();
    }

    /**
     *
     * @param string $method
     * @return bool
     */
    public function hasRequestMethod($method)
    {
        return $this->getPage()->hasRequestMethod($method);
    }

    /**
     * @param \Sonata\PageBundle\Model\SiteInterface $site
     * @return void
     */
    public function setSite(SiteInterface $site)
    {
        $this->getPage()->setSite($site);
    }

    /**
     * @return \Sonata\PageBundle\Model\SiteInterface
     */
    public function getSite()
    {
        return $this->getPage()->getSite();
    }

    /**
     * @param string $headers
     * @return
     */
    public function setRawHeaders($headers)
    {
        return $this->getPage()->setRawHeaders($headers);
    }

    /**
     * @return boolean
     */
    public function getEdited()
    {
        return $this->getPage()->getEdited();
    }

    /**
     * @return void
     */
    public function setEdited($edited)
    {
        $this->getPage()->setEdited($edited);
    }

    /**
     * @return void
     */
    public function isError()
    {
        return $this->getPage()->isError();
    }
}