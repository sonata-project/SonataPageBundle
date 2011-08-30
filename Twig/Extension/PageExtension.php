<?php

/*
 * This file is part of sonata-project.
 *
 * (c) 2010 Thomas Rabaix
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Twig\Extension;

use Symfony\Component\Routing\Router;
use Sonata\PageBundle\Model\PageInterface;

class PageExtension extends \Twig_Extension
{
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'page_url'  => new \Twig_Function_Method($this, 'url'),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'sonata_path';
    }

    /**
     * @param string $page
     * @param boolean $absolute
     * @return string
     */
    public function url($page = null, $absolute = false)
    {
        if (!$page) {
             return '';
        }

        $context = $this->router->getContext();

        if ($page instanceof PageInterface) {
            if ($page->isDynamic()) {
                if ($this->environment->isDebug()) {
                    throw new \RunTimeException('Unable to generate path for hybrid and dynamic page');
                }

                return '';
            }

            $url = $page->getCustomUrl() ?: $page->getUrl();
        } else {
            $url = $page;
        }

        $url = sprintf('%s%s', $context->getBaseUrl(), $url);

        if ($absolute && $context->getHost()) {
            $scheme = $context->getScheme();

            $port = '';
            if ('http' === $scheme && 80 != $context->getHttpPort()) {
                $port = ':'.$context->getHttpPort();
            } elseif ('https' === $scheme && 443 != $context->getHttpsPort()) {
                $port = ':'.$context->getHttpsPort();
            }

            $url = $scheme.'://'.$context->getHost().$port.$url;
        }

        return $url;
    }
}

