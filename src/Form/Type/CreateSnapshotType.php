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

namespace Sonata\PageBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Select a Page.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class CreateSnapshotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('page');
    }

    public function getBlockPrefix(): string
    {
        return 'sonata_page_create_snapshot';
    }
}
