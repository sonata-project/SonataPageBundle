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

namespace Sonata\PageBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueUrl extends Constraint
{
    public string $message = 'error.uniq_url';

    public function validatedBy(): string
    {
        return 'sonata.page.validator.unique_url';
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
