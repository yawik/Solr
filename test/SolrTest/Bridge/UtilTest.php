<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

namespace SolrTest\Bridge;

use PHPUnit\Framework\TestCase;


use Core\Entity\LocationInterface;
use Jobs\Entity\Location;
use Solr\Bridge\Manager;
use Solr\Bridge\Util;
use InvalidArgumentException;

/**
 * Class UtilTest
 *
 * @author Anthonius Munthi <me@itstoni.com>
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since  0.26
 * @package SolrTest\Bridge
 */
class UtilTest extends TestCase
{
    public function testConvertDateTime()
    {
        $date = new \DateTime();
        $expected = $date->setTimezone(new \DateTimeZone('UTC'))->format(Manager::SOLR_DATE_FORMAT);

        $this->assertEquals($expected,Util::convertDateTime($date));
    }

    public function testConvertLocationCoordinates()
    {
        $coordinates = $this->getMockBuilder(LocationInterface::class)
            ->getMock()
        ;
        $location = $this->getMockBuilder(Location::class)
            ->setMethods(['getCoordinates'])
            ->getMock()
        ;

        $location->expects($this->once())
            ->method('getCoordinates')
            ->willReturn($coordinates)
        ;

        $coordinates->expects($this->once())
            ->method('getCoordinates')
            ->willReturn([0.2,0.1])
        ;

        $this->assertEquals('0.1,0.2',Util::convertLocationCoordinates($location));
    }

    public function testConvertLocationString()
    {
        $location = "c:0.1:0.2";
        $this->assertEquals('0.1,0.2',Util::convertLocationString($location));
    }
    
    /**
     * @dataProvider convertSolrDateToPhpDateTimeProvider
     */
    public function testConvertSolrDateToPhpDateTime($solrDate, $expected)
    {
        if (!$expected) {
            $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid format');
        }
        
        $this->assertEquals($expected, Util::convertSolrDateToPhpDateTime($solrDate));
    }

    public function convertSolrDateToPhpDateTimeProvider()
    {
        return [
            [
                '2016-06-28T08:48:37Z',
                new \DateTime('2016-06-28T08:48:37')
            ],
            [
                'bogus',
                false,
            ]
        ];
    }
}
