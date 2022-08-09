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
     * @return Type[]|null
     */
    public function getTypes(string $class, string $property, array $context = [])
    {
        if ($class === $this->blockManager->getClass()) {
            if ('position' === $property) {
                return [
                    new Type(Type::BUILTIN_TYPE_INT, true),
                    new Type(Type::BUILTIN_TYPE_STRING, true),
                ];
            } else if (in_array($property, self::NULLABLE_STRINGS)) {
                return [new Type(Type::BUILTIN_TYPE_STRING, true)];
            }
        }
        return null;
    }
}
