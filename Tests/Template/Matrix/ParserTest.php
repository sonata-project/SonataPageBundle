<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Route;
use Sonata\PageBundle\Template\Matrix\Parser;

/**
 *
 */
class ParserTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid template matrix, a matrix should contain at least one row
     */
    public function testParserWithInvalidTemplateMatrix()
    {
        Parser::parse("", array());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid template matrix, inconsistent row length, row "1" should have a length of "4"
     */
    public function testParserWithInvalidRowLength()
    {
        Parser::parse("YYYY\nNNNNNN", array('Y' => 'top'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid template matrix, no mapping found for symbol "Y"
     */
    public function testParserWithInvalidMapping()
    {
        Parser::parse("YYYY\nNNNNNN", array());
    }

    public function testValidMapping()
    {
        $result = Parser::parse("TTTT\nLLRR", array(
            'T' => 'top',
            'L' => 'left',
            'R' => 'right'
        ));

        $expected = array(
            'top' => array(
                'x' => 0,
                'y' => 0,
                'width' => 100,
                'height' => 50.0,
                'right' => 0,
                'bottom' => 50.0,
            ),
            'left' => array(
                'x' => 0,
                'y' => 50.0,
                'width' => 50.0,
                'height' => 50.0,
                'right' => 50.0,
                'bottom' => 0.0,
            ),
            'right' => array(
                'x' => 50.0,
                'y' => 50.0,
                'width' => 50.0,
                'height' => 50.0,
                'right' => 0.0,
                'bottom' => 0.0,
            ),
        );

        $this->assertEquals($expected, $result);
    }
}
