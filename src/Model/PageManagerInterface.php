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

use Sonata\Doctrine\Model\ManagerInterface;

/**
 * @extends ManagerInterface<PageInterface>
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface PageManagerInterface extends ManagerInterface
{
    /**
     * @param array<string, mixed> $defaults
     */
    public function createWithDefaults(array $defaults = []): PageInterface;

    public function getPageByUrl(SiteInterface $site, string $url): ?PageInterface;

    /**
     * Returns an array of Pages Entity where the id is the key.
     *
     * @return array<PageInterface>
     */
    public function loadPages(SiteInterface $site): array;

    /**
     * @return array<PageInterface>
     */
    public function getHybridPages(SiteInterface $site): array;

    public function fixUrl(PageInterface $page): void;
}
