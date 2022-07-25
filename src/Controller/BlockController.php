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

namespace Sonata\PageBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class BlockController
{
    public function emptyAction(): Response
    {
        return new Response('Empty response');
    }
}
