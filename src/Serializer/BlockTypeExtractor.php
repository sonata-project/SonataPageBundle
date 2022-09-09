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
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\PropertyInfo\Type;

class BlockTypeExtractor implements PropertyTypeExtractorInterface
{
    public const NULLABLE_STRINGS = [
        'name',
        'type',
        'javascript',
        'stylesheet',
        'raw_headers',
        'title',
        'meta_description',
        'meta_keyword',
        'template_code',
        'request_method',
        'slug',
    ];

    /**
     * @var ManagerInterface<PageBlockInterface>
     */
    private ManagerInterface $blockManager;

    /**
     * @param ManagerInterface<PageBlockInterface> $blockManager
     */
    public function __construct(
        ManagerInterface $blockManager
    ) {
        $this->blockManager = $blockManager;
    }

    /**
     * @param string                  $class
     * @param string                  $property
     * @param array<array-key, mixed> $context
     *
     * @return Type[]|null
     */
    public function getTypes($class, $property, array $context = []): ?array
    {
        if ($class !== $this->blockManager->getClass()) {
            return null;
        }
        if ('position' === $property) {
            return [
                new Type(Type::BUILTIN_TYPE_INT, true),
                new Type(Type::BUILTIN_TYPE_STRING, true),
            ];
        } elseif ('enabled' === $property) {
            return [
                new Type(Type::BUILTIN_TYPE_BOOL, true),
                new Type(Type::BUILTIN_TYPE_INT, true),
                new Type(Type::BUILTIN_TYPE_STRING, true),
            ];
        } elseif ('settings' === $property) {
            // fix for faulty property-info in 4.4, it didn't understand arrays with keys
            return [
                new Type(
                    Type::BUILTIN_TYPE_ARRAY,
                    false,
                    null,
                    true,
                    new Type(Type::BUILTIN_TYPE_STRING),
                    null
                ),
            ];
        } elseif (\in_array($property, self::NULLABLE_STRINGS, true)) {
            return [new Type(Type::BUILTIN_TYPE_STRING, true)];
        }

        return null;
    }
}
