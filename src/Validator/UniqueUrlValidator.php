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

namespace Sonata\PageBundle\Validator;

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Validator\Constraints\UniqueUrl;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class UniqueUrlValidator extends ConstraintValidator
{
    public function __construct(private PageManagerInterface $manager)
    {
    }

    /**
     * @param mixed $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueUrl) {
            throw new UnexpectedTypeException($constraint, UniqueUrl::class);
        }
        // do not validate on null, use NotNull instead
        if (null === $value) {
            return;
        }
        if (!$value instanceof PageInterface) {
            throw new UnexpectedValueException($value, PageInterface::class);
        }

        if (null === $value->getSite()) {
            $this->context->buildViolation('error.uniq_url.no_site')
                ->atPath('site')
                ->addViolation();

            return;
        }

        // do not validate error or dynamic pages
        if ($value->isError() || $value->isDynamic()) {
            return;
        }

        $this->manager->fixUrl($value);

        $similarPages = $this->manager->findBy([
            'site' => $value->getSite(),
            'url' => $value->getUrl(),
        ]);

        foreach ($similarPages as $similarPage) {
            if ($similarPage->isError() || $similarPage->isInternal() || $similarPage === $value) {
                continue;
            }

            if ($similarPage->getUrl() !== $value->getUrl()) {
                continue;
            }

            if ('/' === $value->getUrl() && null === $value->getParent()) {
                $this->context->buildViolation('error.uniq_url.parent_unselect')
                    ->atPath('parent')
                    ->addViolation();

                return;
            }

            $this->context->buildViolation('error.uniq_url', ['%url%' => $value->getUrl()])
                ->atPath('url')
                ->addViolation();
        }
    }
}
