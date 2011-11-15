<?php

namespace Sonata\PageBundle\Model;

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

    /**
     * @param SnapshotManagerInterface $manager
     * @param SnapshotInterface $snapshot
     */
    public function __construct(SnapshotManagerInterface $manager, SnapshotInterface $snapshot)
    {
        $this->manager  = $manager;
        $this->snapshot = $snapshot;
    }

    /**
     * Get the page
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
    function addChildren(PageInterface $children)
    {
        $this->getPage()->addChildren($children);
    }

    /**
     * @param array $headers
     * @return void
     */
    function setHeaders(array $headers = array())
    {
        $this->getPage()->setHeaders($headers);
    }

    /**
     * @param $name
     * @param $value
     * @return void
     */
    function addHeader($name, $value)
    {
        $this->getPage()->addHeader($name, $value);
    }

    /**
     * @return array
     */
    function getHeaders()
    {
        return $this->getPage()->getHeaders();
    }

    /**
     * Get children
     *
     * @return array
     */
    function getChildren()
    {
        if (!$this->getPage()->getChildren()->count()) {
            $this->getPage()->setChildren(new SnapshotChildrenCollection($this->manager, $this->getPage()));
        }

        return $this->getPage()->getChildren();
    }

    /**
     * Add blocs
     *
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     */
    function addBlocks(BlockInterface $block)
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
    function setTarget(PageInterface $target)
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
     * @params array $parents
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
    function setParent(PageInterface $parent)
    {
        $this->getPage()->setParent($parent);
    }

    /**
     * Set template
     *
     * @param string $templateCode
     */
    function setTemplateCode($templateCode)
    {
        $this->getPage()->setTemplateCode($templateCode);
    }

    /**
     * Get template
     *
     * @return string $templateCode
     */
    function getTemplateCode()
    {
        return $this->getPage()->getTemplateCode();
    }

    /**
     * @param boolean $decorate
     * @return void
     */
    function setDecorate($decorate)
    {
        $this->getPage()->setDecorate($decorate);
    }

    /**
     * get decorate
     *
     * @return boolean $decorate
     */
    function getDecorate()
    {
        return $this->getPage()->getDecorate();
    }

    /**
     * @return bool
     */
    function isHybrid()
    {
        return $this->getPage()->isHybrid();
    }

    /**
     * @param $position
     * @return void
     */
    function setPosition($position)
    {
        $this->getPage()->setPosition($position);
    }

    /**
     * get position
     *
     * @return integer
     */
    function getPosition()
    {
        return $this->getPage()->getPosition();
    }

    /**
     * @param string $method
     * @return void
     */
    function setRequestMethod($method)
    {
        $this->getPage()->setRequestMethod($method);
    }

    /**
     * get request method
     *
     * @return string
     */
    function getRequestMethod()
    {
        return $this->getPage()->getRequestMethod();
    }

    /**
     * Get id of the page
     *
     * @return integer
     */
    function getId()
    {
        return $this->getPage()->getId();
    }

    /**
     * set id of the page
     *
     * @param integer $id
     */
    function setId($id)
    {
        $this->getPage()->setId($id);
    }

    /**
     * Get routeName
     *
     * @return string $routeName
     */
    function getRouteName()
    {
        return $this->getPage()->getRouteName();
    }

    /**
     * Set routeName
     *
     * @param string $routeName
     */
    function setRouteName($routeName)
    {
        $this->getPage()->setRouteName($routeName);
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     */
    function setEnabled($enabled)
    {
        $this->getPage()->setEnabled($enabled);
    }

    /**
     * Get enabled
     *
     * @return boolean $enabled
     */
    function getEnabled()
    {
        return $this->getPage()->getEnabled();
    }

    /**
     * Set name
     *
     * @param string $name
     */
    function setName($name)
    {
        $this->getPage()->setName($name);
    }

    /**
     * Get name
     *
     * @return string $name
     */
    function getName()
    {
        return $this->getPage()->getName();
    }

    /**
     * Set slug
     *
     * @param string $slug
     */
    function setSlug($slug)
    {
        $this->getPage()->setSlug($slug);
    }

    /**
     * Get slug
     *
     * @return string
     */
    function getSlug()
    {
        return $this->getPage()->getSlug();
    }

    /**
     * Set Url
     *
     * @param string $url
     * @return void
     */
    function setUrl($url)
    {
        $this->getPage()->setUrl($url);
    }

    /**
     * Get url
     *
     * @return string
     */
    function getUrl()
    {
        return $this->getPage()->getUrl();
    }

    /**
     * Set customUrl
     *
     * @param string $customUrl
     */
    function setCustomUrl($customUrl)
    {
        $this->getPage()->setCustomUrl($customUrl);
    }

    /**
     * Get customUrl
     *
     * @return integer $customUrl
     */
    function getCustomUrl()
    {
        return $this->getPage()->getCustomUrl();
    }

    /**
     * Set metaKeyword
     *
     * @param string $metaKeyword
     */
    function setMetaKeyword($metaKeyword)
    {
        $this->getPage()->setMetaKeyword($metaKeyword);
    }

    /**
     * Get metaKeyword
     *
     * @return string $metaKeyword
     */
    function getMetaKeyword()
    {
        return $this->getPage()->getMetaKeyword();
    }

    /**
     * Set metaDescription
     *
     * @param string $metaDescription
     */
    function setMetaDescription($metaDescription)
    {
        $this->getPage()->setMetaDescription($metaDescription);
    }

    /**
     * Get metaDescription
     *
     * @return string $metaDescription
     */
    function getMetaDescription()
    {
        return $this->getPage()->getMetaDescription();
    }

    /**
     * Set javascript
     *
     * @param string $javascript
     */
    function setJavascript($javascript)
    {
        $this->getPage()->setJavascript($javascript);
    }

    /**
     * Get javascript
     *
     * @return string $javascript
     */
    function getJavascript()
    {
        return $this->getPage()->getJavascript();
    }

    /**
     * Set stylesheet
     *
     * @param string $stylesheet
     */
    function setStylesheet($stylesheet)
    {
        $this->getPage()->setStylesheet($stylesheet);
    }

    /**
     * Get stylesheet
     *
     * @return string $stylesheet
     */
    function getStylesheet()
    {
        return $this->getPage()->getStylesheet();
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     */
    function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->getPage()->setCreatedAt($createdAt);
    }

    /**
     * Get createdAt
     *
     * @return datetime $createdAt
     */
    function getCreatedAt()
    {
        return $this->getPage()->getCreatedAt();
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     */
    function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->getPage()->setUpdatedAt($updatedAt);
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime $updatedAt
     */
    function getUpdatedAt()
    {
        return $this->getPage()->getUpdatedAt();
    }

    /**
     * @return boolean
     */
    function isDynamic()
    {
        return $this->getPage()->isDynamic();
    }
    
        
    /**
     * Return the TTL value in second
     *
     * @return integer
     */
    public function getTtl() {
        return $this->getPage()->getTtl();
    }
    
    /**
     * @param string $method
     * @return bool
     */
    public function hasRequestMethod($method) {
        return $this->getPage()->hasRequestMethod($method);
    }
    
    /**
     * @return bool
     */
    public function isCms() {
        return $this->getPage()->isCms();
    }
    
    /**
     * @return bool
     */
    public function isInternal() {
        return $this->getPage()->isInternal();
    }
}