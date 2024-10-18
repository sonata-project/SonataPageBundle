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

use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\PropertyInfo\Type as LegacyType;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\ObjectType;

final class InterfaceTypeExtractor implements PropertyTypeExtractorInterface
{
    /**
     * @param class-string<PageInterface>  $pageClass
     * @param class-string<BlockInterface> $blockClass
     */
    public function __construct(
        private string $pageClass,
        private string $blockClass,
    ) {
    }

    /**
     * @param array<array-key, mixed> $context
     *
     * @return LegacyType[]|null
     */
    public function getTypes(string $class, string $property, array $context = []): ?array
    {
        if ($this->pageClass === $class) {
            if ('children' === $property) {
                return [new LegacyType(
                    LegacyType::BUILTIN_TYPE_OBJECT,
                    false,
                    null,
                    true,
                    null,
                    $this->getPageType()
                )];
            }
            if ('blocks' === $property) {
                return [new LegacyType(
                    LegacyType::BUILTIN_TYPE_OBJECT,
                    false,
                    null,
                    true,
                    null,
                    $this->getBlockType()
                )];
            }
            if ('parent' === $property) {
                return [$this->getPageType()];
            }
        } elseif ($this->blockClass === $class) {
            if ('children' === $property) {
                return [new LegacyType(
                    LegacyType::BUILTIN_TYPE_OBJECT,
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

    /**
     * @param array<array-key, mixed> $context
     */
    public function getType(string $class, string $property, array $context = []): ?Type
    {
        if ($this->pageClass === $class) {
            if ('children' === $property) {
                return new ObjectType($this->pageClass);
            }
            if ('blocks' === $property) {
                return new ObjectType($this->blockClass);
            }
            if ('parent' === $property) {
                return new ObjectType($this->pageClass);
            }
        } elseif ($this->blockClass === $class) {
            if ('children' === $property) {
                return new ObjectType($this->blockClass);
            }
        }

        return null;
    }

    private function getPageType(): LegacyType
    {
        return new LegacyType(LegacyType::BUILTIN_TYPE_OBJECT, false, $this->pageClass);
    }

    private function getBlockType(): LegacyType
    {
        return new LegacyType(LegacyType::BUILTIN_TYPE_OBJECT, false, $this->blockClass);
    }
}
