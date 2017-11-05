<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Controller\Api;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View as FOSRestView;
use JMS\Serializer\SerializationContext;

/**
 * @author Duchkina Anastasiya <duchkina.nast@gmail.com>
 */
abstract class FOSRestController
{
    /**
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return ParamFetcherInterface
     */
    final protected function setMapForOrderByParam(ParamFetcherInterface $paramFetcher)
    {
        $orderByQueryParam = new QueryParam();
        if (property_exists($orderByQueryParam, 'map')) {
            // support FOSRestApi 2.0
            $orderByQueryParam->map = true;
        } else {
            $orderByQueryParam->array = true;
        }
        $paramFetcher->addParam($orderByQueryParam);

        return $paramFetcher;
    }

    /**
     * @param $entity
     * @param array $groups
     *
     * @return FOSRestView
     */
    final protected function serializeContext($entity, array $groups)
    {
        $view = FOSRestView::create($entity);
        if (method_exists($view, 'setSerializationContext')) {
            // support FOSRestApi <= v1.7.9
            $serializationContext = SerializationContext::create();
            $serializationContext->setGroups($groups);
            $serializationContext->enableMaxDepthChecks();
            $view->setSerializationContext($serializationContext);
        } else {
            // since FOSRestApi >= v1.8
            $context = new Context();
            $context->setGroups($groups);
            $view->setContext($context);
        }

        return $view;
    }
}
