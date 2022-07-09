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
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Validator\Constraints\UniqueUrl;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @final since sonata-project/page-bundle 3.26
 */
class UniqueUrlValidator extends ConstraintValidator
{
    /**
     * @var PageManagerInterface
     */
    protected $manager;

    public function __construct(PageManagerInterface $manager)
    {
        $this->manager = $manager;
    }

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

            if ('/' === $value->getUrl() && !$value->getParent()) {
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
