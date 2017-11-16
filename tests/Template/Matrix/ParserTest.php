<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Route;

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Template\Matrix\Parser;

class ParserTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid template matrix, a matrix should contain at least one row
     */
    public function testParserWithInvalidTemplateMatrix()
    {
        Parser::parse('', []);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid template matrix, inconsistent row length, row "1" should have a length of "4"
     */
    public function testParserWithInvalidRowLength()
    {
        Parser::parse("YYYY\nNNNNNN", ['Y' => 'top']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid template matrix, no mapping found for symbol "Y"
     */
    public function testParserWithInvalidMapping()
    {
        Parser::parse("YYYY\nNNNNNN", []);
    }

    public function testValidMapping()
    {
        $result = Parser::parse("TTTT\nLLRR", [
            'T' => 'top',
            'L' => 'left',
            'R' => 'right',
        ]);

        $expected = [
            'top' => [
                'x' => 0,
                'y' => 0,
                'width' => 100,
                'height' => 50.0,
                'right' => 0,
                'bottom' => 50.0,
            ],
            'left' => [
                'x' => 0,
                'y' => 50.0,
                'width' => 50.0,
                'height' => 50.0,
                'right' => 50.0,
                'bottom' => 0.0,
            ],
            'right' => [
                'x' => 50.0,
                'y' => 50.0,
                'width' => 50.0,
                'height' => 50.0,
                'right' => 0.0,
                'bottom' => 0.0,
            ],
        ];

        $this->assertEquals($expected, $result);
    }
}
