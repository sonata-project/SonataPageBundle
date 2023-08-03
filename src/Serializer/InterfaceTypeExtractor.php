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

use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\PropertyInfo\Type;

final class InterfaceTypeExtractor implements PropertyTypeExtractorInterface
{
    /**
     * @param class-string $pageClass
     * @param class-string $blockClass
     */
    public function __construct(
        private string $pageClass,
        private string $blockClass
    ) {
    }

    /**
     * @param array<array-key, mixed> $context
     *
     * @return Type[]|null
     */
    public function getTypes(string $class, string $property, array $context = []): ?array
    {
        if ($this->pageClass === $class) {
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
        } elseif ($this->blockClass === $class) {
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
        return new Type(Type::BUILTIN_TYPE_OBJECT, false, $this->pageClass);
    }

    private function getBlockType(): Type
    {
        return new Type(Type::BUILTIN_TYPE_OBJECT, false, $this->blockClass);
    }
}
