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
 *
 * @final since sonata-project/page-bundle 3.26
 *
 * @deprecated since 3.27, and it will be removed in 4.0.
 *
 * NEXT_MAJOR: Remove this class.
 */
class Mustache
{
    public function __construct()
    {
        @trigger_error(
            sprintf(
                'This %s is deprecated since sonata-project/page-bundle 3.27.0'.
                ' and it will be removed in 4.0',
                self::class
            ),
            \E_USER_DEPRECATED
        );
    }

    /**
     * @static
     *
     * @param $string
     *
     * @return string
     */
    public static function replace($string, array $parameters)
    {
        $replacer = static fn ($match) => $parameters[$match[1]] ?? $match[0];

        return preg_replace_callback('/{{\s*(.+?)\s*}}/', $replacer, $string);
    }
}
