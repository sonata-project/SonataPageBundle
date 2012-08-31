<?php

/*
 * This file is part of sonata-project.
 *
 * (c) 2010 Thomas Rabaix
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Generator;

use Sonata\PageBundle\Model\PageInterface;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

/**
 * URL generator class
 *
 * @author Rémi Marseille <marseille@ekino.com>
 */
class UrlGenerator implements UrlGeneratorInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var string
     */
    private $numericPrefix;

    /**
     * @var string
     */
    private $argSeparator;

    /**
     * Constructor
     *
     * @param RouterInterface $router        A router
     * @param string          $numericPrefix Prefix used in parameters (optional)
     * @param string          $argSeparator  Separator of parameters (optional)
     */
    public function __construct(RouterInterface $router, $numericPrefix = '', $argSeparator = '&')
    {
        $this->router = $router;
        $this->numericPrefix = $numericPrefix;
        $this->argSeparator = $argSeparator;
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->router->getContext();
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context)
    {
        $this->router->setContext($context);
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = array(), $absolute = false)
    {
        if ($name instanceof PageInterface) {
            if ($name->isDynamic()) {
                throw new \RunTimeException('Unable to generate path for dynamic page');
            }

            $url = $name->getCustomUrl() ?: $name->getUrl();
        } else {
            $url = $name;
        }

        if (!$this->getContext()) {
            throw new \RuntimeException('Please set a request context');
        }

        $url = sprintf('%s%s', $this->getContext()->getBaseUrl(), $url);

        if ($absolute && $this->getContext()->getHost()) {
            $scheme = $this->getContext()->getScheme();
            $port = '';

            if ('http' === $scheme && 80 != $this->getContext()->getHttpPort()) {
                $port = ':'.$this->getContext()->getHttpPort();
            } elseif ('https' === $scheme && 443 != $this->getContext()->getHttpsPort()) {
                $port = ':'.$this->getContext()->getHttpsPort();
            }

            $url = sprintf('%s://%s%s%s', $scheme, $this->getContext()->getHost(), $port, $url);
        }

        if (count($parameters) == 0) {
            return $url;
        }

        return sprintf('%s?%s', $url, http_build_query($parameters, $this->numericPrefix, $this->argSeparator));
    }
}
