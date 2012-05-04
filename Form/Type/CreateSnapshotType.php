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

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;

use Sonata\PageBundle\CmsManager\CmsPageManager;

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
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('page');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'create_snapshot';
    }
}