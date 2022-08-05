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

use Symfony\Component\Serializer\Exception\BadMethodCallException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class InterfaceDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface, CacheableSupportsMethodInterface
{
    use DenormalizerAwareTrait;

    public const SUPPORTED_INTERFACES_KEY = 'supported_interfaces';

    /**
     * @return mixed
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        if (null === $this->denormalizer) {
            throw new BadMethodCallException('Please set a denormalizer before calling denormalize()!');
        }

        if (!isset($context[self::SUPPORTED_INTERFACES_KEY][$type])) {
            throw new InvalidArgumentException('Unsupported class: '.$type);
        }

        return $this->denormalizer->denormalize($data, $context[self::SUPPORTED_INTERFACES_KEY][$type], $format, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        if (!interface_exists($type, false)) {
            return false;
        }

        if (!isset($context[self::SUPPORTED_INTERFACES_KEY][$type])) {
            return false;
        }

        /**
         * The interface doesn't have the context options yet.
         *
         * @see https://github.com/symfony/Serializer/blob/6.1/Normalizer/DenormalizerInterface.php#L59
         *
         * @psalm-suppress TooManyArguments
         * @phpstan-ignore-next-line
         */
        return $this->denormalizer->supportsDenormalization($data, $context[self::SUPPORTED_INTERFACES_KEY][$type], $format, $context);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return $this->denormalizer instanceof CacheableSupportsMethodInterface && $this->denormalizer->hasCacheableSupportsMethod();
    }
}
