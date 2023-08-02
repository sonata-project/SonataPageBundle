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

namespace Sonata\PageBundle\Serializer;

use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\PropertyInfo\Type;

final class InterfaceTypeExtractor implements PropertyTypeExtractorInterface
{
    /**
     * @param ManagerInterface<PageBlockInterface> $blockManager
     */
    public function __construct(
        private PageManagerInterface $pageManager,
        private ManagerInterface $blockManager
    ) {
    }

    /**
     * @param array<array-key, mixed> $context
     *
     * @return Type[]|null
     */
    public function getTypes(string $class, string $property, array $context = []): ?array
    {
        if ($this->pageManager->getClass() === $class) {
            if ('children' === $property) {
                return [new Type(
                    Type::BUILTIN_TYPE_OBJECT,
                    false,
                    null,
                    true,
                    null,
                    $this->getPageType()
                )];
            } elseif ('blocks' === $property) {
                return [new Type(
                    Type::BUILTIN_TYPE_OBJECT,
                    false,
                    null,
                    true,
                    null,
                    $this->getBlockType()
                )];
            } elseif ('parent' === $property) {
                return [$this->getPageType()];
            }
        } elseif ($this->blockManager->getClass() === $class) {
            if ('children' === $property) {
                return [new Type(
                    Type::BUILTIN_TYPE_OBJECT,
                    false,
                    null,
                    true,
                    null,
                    $this->getBlockType()
                )];
            }
        }

        return null;
    }

    private function getPageType(): Type
    {
        return new Type(Type::BUILTIN_TYPE_OBJECT, false, $this->pageManager->getClass());
    }

    private function getBlockType(): Type
    {
        return new Type(Type::BUILTIN_TYPE_OBJECT, false, $this->blockManager->getClass());
    }
}
