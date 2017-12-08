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

namespace Sonata\PageBundle\Generator;

/**
 * Render a string using the mustache formatter : {{ var }}.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class Mustache
{
    /**
     * @static
     *
     * @param $string
     * @param array $parameters
     *
     * @return string
     */
    public static function replace($string, array $parameters)
    {
        $replacer = function ($match) use ($parameters) {
            return $parameters[$match[1]] ?? $match[0];
        };

        return preg_replace_callback('/{{\s*(.+?)\s*}}/', $replacer, $string);
    }
}
