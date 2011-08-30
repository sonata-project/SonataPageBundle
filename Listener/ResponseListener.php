<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\PageBundle\Listener;

/**
 * This class redirect the onCoreResponse event to the correct
 * cms manager upon user permission
 */
class ResponseListener
{
    protected $selector;

    public function __construct($selector)
    {
        $this->selector = $selector;
    }

    public function onCoreResponse($event)
    {
        return $this->selector->retrieve()->onCoreResponse($event);
    }
}