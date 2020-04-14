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

namespace Sonata\PageBundle\Model;

/**
 * SnapshotPageProxy.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SnapshotPageProxy implements SnapshotPageProxyInterface
{
    /**
     * @var SnapshotManagerInterface
     */
    private $manager;

    /**
     * @var SnapshotInterface
     */
    private $snapshot;

    /**
     * @var PageInterface
     */
    private $page;

    /**
     * @var PageInterface|null
     */
    private $target;

    /**
     * @var PageInterface[]
     */
    private $parents;

    /**
     * @var TransformerInterface
     */
    private $transformer;

    /**
     * @param SnapshotManagerInterface $manager     Snapshot manager
     * @param TransformerInterface     $transformer The transformer object
     * @param SnapshotInterface        $snapshot    Snapshot object
     */
    public function __construct(SnapshotManagerInterface $manager, TransformerInterface $transformer, SnapshotInterface $snapshot)
    {
        $this->manager = $manager;
        $this->snapshot = $snapshot;
        $this->transformer = $transformer;
    }

    public function __call($method, $arguments)
    {
        return \call_user_func_array([$this->getPage(), $method], $arguments);
    }

    /**
     * Returns a string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getPage()->__toString();
    }

    public function getPage()
    {
        $this->load();

        return $this->page;
    }

    public function addChildren(PageInterface $children)
    {
        $this->getPage()->addChildren($children);
    }

    public function setHeaders(array $headers = [])
    {
        $this->getPage()->setHeaders($headers);
    }

    public function addHeader($name, $value)
    {
        $this->getPage()->addHeader($name, $value);
    }

    public function getHeaders()
    {
        return $this->getPage()->getHeaders();
    }

    public function getChildren()
    {
        if (!$this->getPage()->getChildren()->count()) {
            $this->getPage()->setChildren(new SnapshotChildrenCollection($this->transformer, $this->getPage()));
        }

        return $this->getPage()->getChildren();
    }

    public function addBlocks(PageBlockInterface $block)
    {
        $this->getPage()->addBlocks($block);
    }

    public function getBlocks()
    {
        if (!\count($this->getPage()->getBlocks())) {
            $content = $this->snapshot->getContent();

            foreach ($content['blocks'] as $block) {
                $b = $this->transformer->loadBlock($block, $this);
                $this->addBlocks($b);

                $b->setPage($this);
            }
        }

        return $this->getPage()->getBlocks();
    }

    public function setTarget(?PageInterface $target = null)
    {
        $this->target = $target;
    }

    public function getTarget()
    {
        if (null === $this->target) {
            $content = $this->snapshot->getContent();

            if (isset($content['target_id'])) {
                $target = $this->manager->findEnableSnapshot([
                    'pageId' => $content['target_id'],
                ]);

                if ($target) {
                    $this->setTarget(new self($this->manager, $this->transformer, $target));
                } else {
                    $this->target = false;
                }
            }
        }

        return $this->target ?: null;
    }

    public function getParent($level = -1)
    {
        $parents = $this->getParents();

        if ($level < 0) {
            $level = \count($parents) + $level;
        }

        return $parents[$level] ?? null;
    }

    public function setParents(array $parents)
    {
        $this->parents = $parents;
    }

    public function getParents()
    {
        if (!$this->parents) {
            $parents = [];

            $snapshot = $this->snapshot;

            while ($snapshot) {
                $content = $snapshot->getContent();

                if (!$content['parent_id']) {
                    break;
                }

                $snapshot = $this->manager->findEnableSnapshot([
                    'pageId' => $content['parent_id'],
                ]);

                if (!$snapshot) {
                    break;
                }

                $parents[] = new self($this->manager, $this->transformer, $snapshot);
            }

            $this->setParents(array_reverse($parents));
        }

        return $this->parents;
    }

    public function setParent(?PageInterface $parent = null)
    {
        $this->getPage()->setParent($parent);
    }

    public function setTemplateCode($templateCode)
    {
        $this->getPage()->setTemplateCode($templateCode);
    }

    public function getTemplateCode()
    {
        return $this->getPage()->getTemplateCode();
    }

    public function setDecorate($decorate)
    {
        $this->getPage()->setDecorate($decorate);
    }

    public function getDecorate()
    {
        return $this->getPage()->getDecorate();
    }

    public function isHybrid()
    {
        return $this->getPage()->isHybrid();
    }

    public function setPosition($position)
    {
        $this->getPage()->setPosition($position);
    }

    public function getPosition()
    {
        return $this->getPage()->getPosition();
    }

    public function setRequestMethod($method)
    {
        $this->getPage()->setRequestMethod($method);
    }

    public function getRequestMethod()
    {
        return $this->getPage()->getRequestMethod();
    }

    public function getId()
    {
        return $this->getPage()->getId();
    }

    public function setId($id)
    {
        $this->getPage()->setId($id);
    }

    public function getRouteName()
    {
        return $this->getPage()->getRouteName();
    }

    public function setRouteName($routeName)
    {
        $this->getPage()->setRouteName($routeName);
    }

    public function setEnabled($enabled)
    {
        $this->getPage()->setEnabled($enabled);
    }

    public function getEnabled()
    {
        return $this->getPage()->getEnabled();
    }

    public function setName($name)
    {
        $this->getPage()->setName($name);
    }

    public function getName()
    {
        return $this->getPage()->getName();
    }

    public function setSlug($slug)
    {
        $this->getPage()->setSlug($slug);
    }

    public function getSlug()
    {
        return $this->getPage()->getSlug();
    }

    public function setUrl($url)
    {
        $this->getPage()->setUrl($url);
    }

    public function getUrl()
    {
        return $this->getPage()->getUrl();
    }

    public function setCustomUrl($customUrl)
    {
        $this->getPage()->setCustomUrl($customUrl);
    }

    public function getCustomUrl()
    {
        return $this->getPage()->getCustomUrl();
    }

    public function setMetaKeyword($metaKeyword)
    {
        $this->getPage()->setMetaKeyword($metaKeyword);
    }

    public function getMetaKeyword()
    {
        return $this->getPage()->getMetaKeyword();
    }

    public function setMetaDescription($metaDescription)
    {
        $this->getPage()->setMetaDescription($metaDescription);
    }

    public function getMetaDescription()
    {
        return $this->getPage()->getMetaDescription();
    }

    public function setJavascript($javascript)
    {
        $this->getPage()->setJavascript($javascript);
    }

    public function getJavascript()
    {
        return $this->getPage()->getJavascript();
    }

    public function setStylesheet($stylesheet)
    {
        $this->getPage()->setStylesheet($stylesheet);
    }

    public function getStylesheet()
    {
        return $this->getPage()->getStylesheet();
    }

    public function getPageAlias()
    {
        return $this->getPage()->getPageAlias();
    }

    public function setPageAlias($pageAlias)
    {
        return $this->getPage()->setPageAlias($pageAlias);
    }

    public function setCreatedAt(?\DateTime $createdAt = null)
    {
        $this->getPage()->setCreatedAt($createdAt);
    }

    public function getCreatedAt()
    {
        return $this->getPage()->getCreatedAt();
    }

    public function setUpdatedAt(?\DateTime $updatedAt = null)
    {
        $this->getPage()->setUpdatedAt($updatedAt);
    }

    public function getUpdatedAt()
    {
        return $this->getPage()->getUpdatedAt();
    }

    public function isDynamic()
    {
        return $this->getPage()->isDynamic();
    }

    public function isCms()
    {
        return $this->getPage()->isCms();
    }

    public function isInternal()
    {
        return $this->getPage()->isInternal();
    }

    public function hasRequestMethod($method)
    {
        return $this->getPage()->hasRequestMethod($method);
    }

    public function setSite(?SiteInterface $site = null)
    {
        $this->getPage()->setSite($site);
    }

    public function getSite()
    {
        return $this->getPage()->getSite();
    }

    public function setRawHeaders($headers)
    {
        $this->getPage()->setRawHeaders($headers);
    }

    public function getEdited()
    {
        return $this->getPage()->getEdited();
    }

    public function setEdited($edited)
    {
        $this->getPage()->setEdited($edited);
    }

    public function isError()
    {
        return $this->getPage()->isError();
    }

    public function getTitle()
    {
        return $this->getPage()->getTitle();
    }

    public function setTitle($title)
    {
        $this->getPage()->setTitle($title);
    }

    public function setType($type)
    {
        $this->getPage()->setType($type);
    }

    public function getType()
    {
        return $this->getPage()->getType();
    }

    /**
     * Serialize a snapshot page proxy.
     *
     * @return string
     */
    public function serialize()
    {
        if ($this->manager) {
            return serialize([
                'pageId' => $this->getPage()->getId(),
                'snapshotId' => $this->snapshot->getId(),
            ]);
        }

        return serialize([]);
    }

    /**
     * Unserialize a snapshot page proxy.
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        // TODO: Implement unserialize() method.
    }

    private function load()
    {
        if (!$this->page && $this->transformer) {
            $this->page = $this->transformer->load($this->snapshot);
        }
    }
}
