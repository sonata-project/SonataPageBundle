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
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

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

    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof PageInterface) {
            $this->context->addViolation('The page is not valid, expected a PageInterface');

            return;
        }

        if (!$value->getSite() instanceof SiteInterface) {
            $this->context->addViolation('The page is not linked to a Site');

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
