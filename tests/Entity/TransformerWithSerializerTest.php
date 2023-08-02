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

namespace Sonata\PageBundle\Tests\Entity;

use Doctrine\Persistence\ManagerRegistry;
use Sonata\PageBundle\Entity\Transformer;
use Sonata\PageBundle\Serializer\BlockTypeExtractor;
use Sonata\PageBundle\Serializer\InterfaceTypeExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\LoaderChain;
use Symfony\Component\Serializer\Mapping\Loader\XmlFileLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class TransformerWithSerializerTest extends TransformerTest
{
    protected function setUpTransformer(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);

        $loaders = new LoaderChain([
            new XmlFileLoader(__DIR__.'/../../src/Resources/config/serialization/Model.Block.xml'),
            new XmlFileLoader(__DIR__.'/../../src/Resources/config/serialization/Model.Page.xml'),
        ]);

        $classMetadataFactory = new ClassMetadataFactory($loaders);
        $nameConverter = new MetadataAwareNameConverter($classMetadataFactory);

        $extractor = new PropertyInfoExtractor([], [
            new BlockTypeExtractor($this->blockManager),
            new InterfaceTypeExtractor(
                $this->pageManager,
                $this->blockManager,
            ),
            new ReflectionExtractor(),
        ]);

        $objectNormalizer = new ObjectNormalizer($classMetadataFactory, $nameConverter, null, $extractor);

        $encoders = [new JsonEncoder()];
        $normalizers = [
            new DateTimeNormalizer(),
            new ArrayDenormalizer(),
            $objectNormalizer,
        ];
        $serializer = new Serializer($normalizers, $encoders);

        $this->transformer = new Transformer(
            $this->snapshotManager,
            $this->pageManager,
            $this->blockManager,
            $registry,
            $serializer,
        );
    }
}
