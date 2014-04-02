<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Template\Matrix;

/**
 * Template Matrix Parser.
 * computes string based template matrix to position/sise.
 *
 * @author Raphaël Benitte <raphael.benitte@fullsix.com>
 */
class Parser
{
    /**
     * @param string $matrix
     * @param array  $mapping
     *
     * @throws \InvalidArgumentException
     */
    public static function parse($matrix, array $mapping)
    {
        $matrix = trim($matrix);
        $areas  = array();

        $rows     = preg_split("/\n/", $matrix);
        $rowCount = count($rows);
        if ($rowCount == 0 || strlen($rows[0]) == 0) {
            throw new \InvalidArgumentException('Invalid template matrix, a matrix should contain at least one row');
        }

        $colCount = strlen($rows[0]);

        foreach ($rows as $y => $row) {
            if (strlen($row) !== $colCount && $y > 0) {
                throw new \InvalidArgumentException(sprintf('Invalid template matrix, inconsistent row length, row "%d" should have a length of "%d"', $y, $colCount));
            }

            $cells = str_split($row);
            foreach ($cells as $x => $symbol) {
                if (!array_key_exists($symbol, $mapping)) {
                    throw new \InvalidArgumentException(sprintf('Invalid template matrix, no mapping found for symbol "%s"', $symbol));
                }
                if (!isset($areas[$symbol])) {
                    $areas[$symbol] = array(
                        'x'      => $x,
                        'y'      => $y,
                        'width'  => 1,
                        'height' => 1,
                    );
                } else {
                    // @todo handle non adjacent cells
                    if (false) {
                        //throw new \InvalidArgumentException(sprintf('Invalid template matrix, non adjacent symbol found "%s" at row "%s", col "%s"', $symbol, $y, $x));
                    }
                    $areas[$symbol]['width']  = $x - $areas[$symbol]['x'] + 1;
                    $areas[$symbol]['height'] = $y - $areas[$symbol]['y'] + 1;
                }
            }
        }

        foreach ($areas as &$area) {
            $area['x'] = $area['x'] / $colCount * 100;
            $area['y'] = $area['y'] / $rowCount * 100;

            $area['width']  = $area['width']  / $colCount * 100;
            $area['height'] = $area['height'] / $rowCount * 100;

            $area['right']  = 100 - ($area['width']  + $area['x']);
            $area['bottom'] = 100 - ($area['height'] + $area['y']);
        }

        $containers = array();
        foreach ($areas as $symbol => $config) {
            $containers[$mapping[$symbol]] = $config;
        }

        return $containers;
    }
}
