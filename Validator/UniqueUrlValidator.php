<?php

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

    /**
     * @param PageManagerInterface $manager
     */
    public function __construct(PageManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($currentPage, Constraint $constraint)
    {
        if (!$currentPage instanceof PageInterface) {
            $this->context->addViolation('The page is not valid, expected a PageInterface');

            return;
        }

        if (!$currentPage->getSite() instanceof SiteInterface) {
            $this->context->addViolation('The page is not linked to a Site');

            return;
        }

        // do not validate error or dynamic pages
        if ($currentPage->isError() || $currentPage->isDynamic()) {
            return;
        }

        $this->manager->fixUrl($currentPage);

        $similarPages = $this->manager->findBy(array(
            'site' => $currentPage->getSite(),
            'url' => $currentPage->getUrl(),
        ));

        foreach ($similarPages as $similarPage) {
            if ($similarPage->isError() || $similarPage->isInternal() || $similarPage == $currentPage) {
                continue;
            }

            if ($similarPage->getUrl() != $currentPage->getUrl()) {
                continue;
            }

            if ($currentPage->getUrl() == '/' && !$currentPage->getParent()) {
                // NEXT_MAJOR: remove the if block below
                if (!method_exists($this->context, 'buildViolation')) {
                    $this->context->addViolationAt(
                        'parent',
                        'error.uniq_url.parent_unselect'
                    );

                    return;
                }
                $this->context->buildViolation('error.uniq_url.parent_unselect')
                    ->atPath('parent')
                    ->addViolation();

                return;
            }
            // NEXT_MAJOR: remove the if block below
            if (!method_exists($this->context, 'buildViolation')) {
                $this->context->addViolationAt(
                    'url',
                    'error.uniq_url',
                    array('%url%' => $currentPage->getUrl())
                );

                return;
            }
            $this->context->buildViolation('error.uniq_url', array('%url%' => $currentPage->getUrl()))
                ->atPath('url')
                ->addViolation();
        }
    }
}
