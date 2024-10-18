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
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\PropertyInfo\Type;

final class BlockTypeExtractor implements PropertyTypeExtractorInterface
{
    public const NULLABLE_STRINGS = [
        'name',
        'type',
    ];

    /**
     * @param class-string<BlockInterface> $blockClass
     */
    public function __construct(
        private string $blockClass,
    ) {
    }

    /**
     * @param array<array-key, mixed> $context
     *
     * @return Type[]|null
     */
    public function getTypes(string $class, string $property, array $context = []): ?array
    {
        if ($class !== $this->blockClass) {
            return null;
        }
        if ('position' === $property) {
            return [
                new Type(Type::BUILTIN_TYPE_INT, true),
                new Type(Type::BUILTIN_TYPE_STRING, true),
            ];
        }
        if ('enabled' === $property) {
            return [
                new Type(Type::BUILTIN_TYPE_BOOL, true),
                new Type(Type::BUILTIN_TYPE_INT, true),
                new Type(Type::BUILTIN_TYPE_STRING, true),
            ];
        }
        if (\in_array($property, self::NULLABLE_STRINGS, true)) {
            return [new Type(Type::BUILTIN_TYPE_STRING, true)];
        }

        return null;
    }
}
