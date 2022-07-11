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

namespace Sonata\PageBundle\Block;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\BlockContextManager as BaseBlockContextManager;
use Sonata\BlockBundle\Block\BlockContextManagerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class BlockContextManager implements BlockContextManagerInterface
{
    /** @var BaseBlockContextManager|BlockContextManagerInterface */
    private $blockContextManager;

    private OptionsResolver $optionsResolver;

    /**
     * @param BaseBlockContextManager|BlockContextManagerInterface $blockContextManager
     */
    public function __construct($blockContextManager)
    {
        $this->blockContextManager = $blockContextManager;
        $this->optionsResolver = new OptionsResolver();
    }

    public function getOptionsResolver(): OptionsResolver
    {
        return $this->optionsResolver;
    }

    /**
     * @return BaseBlockContextManager|BlockContextManagerInterface
     */
    public function getBlockContextManager()
    {
        return $this->blockContextManager;
    }

    public function addSettingsByType(string $type, array $settings, bool $replace = false): void
    {
        $this->blockContextManager->addSettingsByType($type, $settings, $replace);
    }

    public function addSettingsByClass(string $class, array $settings, bool $replace = false): void
    {
        $this->blockContextManager->addSettingsByClass($class, $settings, $replace);
    }

    public function get($meta, array $settings = []): BlockContextInterface
    {
        $this->configureSettings($this->optionsResolver);

        return $this->blockContextManager->get($meta, [
            'manager' => false,
            'page_id' => false,
        ]);
    }

    public function exists(string $type): bool
    {
        return $this->blockContextManager->exists($type);
    }

    private function configureSettings(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'manager' => false,
            'page_id' => false,
        ]);

        $optionsResolver
            ->addAllowedTypes('manager', ['string', 'bool'])
            ->addAllowedTypes('page_id', ['int', 'string', 'bool']);

        $optionsResolver->setRequired([
            'manager',
            'page_id',
        ]);
    }
}
