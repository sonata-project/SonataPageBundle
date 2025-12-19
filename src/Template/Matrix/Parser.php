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

namespace Sonata\PageBundle\Template\Matrix;

/**
 * computes string based template matrix to position/sise.
 *
 * @author Raphaël Benitte <raphael.benitte@fullsix.com>
 *
 * @phpstan-type Placement array{
 *   x: int|float,
 *   y: int|float,
 *   width: int|float,
 *   height: int|float,
 *   right: int|float,
 *   bottom: int|float
 * }
 */
final class Parser
{
    /**
     * @param array<string> $mapping
     *
     * @throws \InvalidArgumentException
     *
     * @return array<string, Placement>
     */
    public static function parse(string $matrix, array $mapping): array
    {
        $matrix = trim($matrix);
        $areas = [];

        $rows = preg_split("/\n/", $matrix);

        if (false === $rows) {
            throw new \InvalidArgumentException('Invalid template matrix.');
        }

        $rowCount = \count($rows);
        if ('' === $rows[0]) {
            throw new \InvalidArgumentException('Invalid template matrix, a matrix should contain at least one row');
        }

        $colCount = \strlen($rows[0]);

        foreach ($rows as $y => $row) {
            if (\strlen($row) !== $colCount && $y > 0) {
                throw new \InvalidArgumentException(\sprintf('Invalid template matrix, inconsistent row length, row "%d" should have a length of "%d"', $y, $colCount));
            }

            $cells = str_split($row);
            foreach ($cells as $x => $symbol) {
                if (!\array_key_exists($symbol, $mapping)) {
                    throw new \InvalidArgumentException(\sprintf('Invalid template matrix, no mapping found for symbol "%s"', $symbol));
                }
                if (!isset($areas[$symbol])) {
                    $areas[$symbol] = [
                        'x' => $x,
                        'y' => $y,
                        'width' => 1,
                        'height' => 1,
                    ];
                } else {
                    $areas[$symbol]['width'] = $x - $areas[$symbol]['x'] + 1;
                    $areas[$symbol]['height'] = $y - $areas[$symbol]['y'] + 1;
                }
            }
        }

        $containers = [];

        foreach ($areas as $symbol => $config) {
            $x = $config['x'] / $colCount * 100;
            $y = $config['y'] / $rowCount * 100;
            $width = $config['width'] / $colCount * 100;
            $height = $config['height'] / $rowCount * 100;

            $containers[$mapping[$symbol]] = [
                'x' => $x,
                'y' => $y,
                'width' => $width,
                'height' => $height,
                'right' => 100 - ($width + $x),
                'bottom' => 100 - ($height + $y),
            ];
        }

        return $containers;
    }
}
