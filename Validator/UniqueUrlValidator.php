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

        // do not validated error page
        if ($value->isError()) {
            return;
        }

        $this->manager->fixUrl($value);

        $pages = $this->manager->findBy(array(
            'site' => $value->getSite(),
            'url' => $value->getUrl(),
        ));

        foreach ($pages as $page) {
            if ($page->isError() || $page->isInternal()) {
                continue;
            }

            if ($page->getUrl() == $value->getUrl() && $page != $value) {
                $this->context->addViolation('error.uniq_url', array('%url%' => $value->getUrl()));
            }
        }
    }
}
