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

namespace Sonata\PageBundle\Service;

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Service\Contract\FixPageUrlInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

final class FixPageUrlService implements FixPageUrlInterface
{
    public function __construct(private SluggerInterface $slugger)
    {
    }

    public function fix(PageInterface $page): void
    {
        if ($page->isInternal()) {
            $page->setUrl(null); // internal routes do not have any url ...

            return;
        }

        // hybrid page cannot be altered
        if (!$page->isHybrid()) {
            $parent = $page->getParent();

            if (null !== $parent) {
                $slug = $page->getSlug();

                if (null === $slug) {
                    $slug = $this->slugger
                        ->slug($page->getName() ?? '')
                        ->lower()
                        ->toString();

                    $page->setSlug(\sprintf('%s', $slug));
                }

                $parentUrl = $parent->getUrl();

                if ('/' === $parentUrl) {
                    $base = '/';
                } elseif (!str_ends_with($parentUrl ?? '', '/')) {
                    $base = $parentUrl.'/';
                } else {
                    $base = $parentUrl;
                }

                $url = $page->getCustomUrl() ?? $slug;
                $page->setUrl('/'.ltrim($base.$url, '/'));
            } else {
                $page->setSlug(null);

                $url = $page->getCustomUrl() ?? '';
                $page->setUrl('/'.ltrim($url, '/'));
            }
        }

        foreach ($page->getChildren() as $child) {
            $this->fix($child);
        }
    }
}
