<?php

namespace Sonata\PageBundle\Cache;

use Sonata\PageBundle\Model\BlockInterface;
use Symfony\Component\HttpFoundation\Response;

final class CacheElement
{
    protected $lifetime;

    protected $keys = array();

    protected $value;

    protected $createdAt;

    public function __construct(array $keys, $ttl = 84600)
    {
        $this->createdAt = new \DateTime;
        $this->keys      = $keys;
        $this->ttl  = $ttl;
    }

    public function getKeys()
    {
        return $this->keys;
    }

    public function getTtl()
    {
        return $this->ttl;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue(Response $value)
    {
        return $this->value = $value;
    }

    public function isExpired()
    {
        return strtotime('now') > ($this->createdAt->format('U') + $this->ttl);
    }
}