<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

/**
 * Select a Page
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class CreateSnapshotType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('page');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sonata_page_create_snapshot';
    }
}
