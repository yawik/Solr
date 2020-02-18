<?php

/**
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.27
 */

namespace SolrTest\Paginator;

use PHPUnit\Framework\TestCase;
use Solr\Paginator\Paginator;
use Solr\Paginator\Adapter\SolrAdapter;
use Solr\Facets;
use Laminas\Paginator\Exception\InvalidArgumentException;

/**
 * @coversDefaultClass \Solr\Paginator\Paginator
 */
class PaginatorTest extends TestCase
{

    /**
     * @covers ::__construct()
     */
    public function testConstructorThrowInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('adapter must implement');

        new Paginator('invalid');
    }
    
    /**
     * @covers ::getFacets()
     * @covers ::__construct()
     */
    public function testGetFacets()
    {
        $facets = new Facets();
        
        $adapter = $this->getMockBuilder(SolrAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adapter->expects($this->once())
            ->method('getFacets')
            ->willReturn($facets);
        
        $paginator = new Paginator($adapter);
        $this->assertSame($facets, $paginator->getFacets());
    }
}

