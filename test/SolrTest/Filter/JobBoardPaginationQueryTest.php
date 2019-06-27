<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

namespace SolrTest\Filter;

use Interop\Container\ContainerInterface;
use Jobs\Entity\CoordinatesInterface;
use Jobs\Entity\JobInterface;
use Jobs\Entity\Location;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Solr\Bridge\Manager;
use Solr\Filter\JobBoardPaginationQuery;
use Solr\Options\ModuleOptions;
use Solr\Entity\JobProxy;
use ArrayObject;
use SolrDisMaxQuery;
use Solr\Facets;
use DateTime;
use Solr\Bridge\Util;

/**
 * Class JobBoardPaginationQueryTest
 *
 * @author  Anthonius Munthi <me@itstoni.com>
 * @author  Mathias Gelhausen <gelhausen@cross-solution.de>
 * @author  Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since   0.26
 * @package SolrTest\Filter
 * @covers  \Solr\Filter\JobBoardPaginationQuery
 * @covers  \Solr\Filter\AbstractPaginationQuery
 * @requires extension solr
 */
class JobBoardPaginationQueryTest extends TestCase
{
    /**
     * @var JobBoardPaginationQuery
     */
    protected $target;

    /**
     * @var MockObject
     */
    protected $manager;

    public function setUp(): void
    {
        $manager = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $sl = $this->getMockBuilder(ContainerInterface::class)
	        ->disableOriginalConstructor()
            ->setMethods(['get','has'])
            ->getMock()
        ;
        $sl->method('get')->with('Solr/Manager')->willReturn($manager);
        $this->target = new JobBoardPaginationQuery(new ModuleOptions());
        $this->manager = $manager;
    }

    public function testFilterWithoutQuery()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('$query must not be null');
        $this->target->filter([]);
    }

    public function testFilterWithoutFacets()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('$facets must not be null');

        $this->target->filter([], new SolrDisMaxQuery());
    }

    public function testFilterCallCreateQuery()
    {
        $params = ['one' => 1];
        $query = new SolrDisMaxQuery();
        $facets = new Facets();
        
        $target = $this->getMockBuilder(JobBoardPaginationQuery::class)
            ->disableOriginalConstructor()
            ->setMethods(['createQuery'])
            ->getMock();
        $target->expects($this->once())
            ->method('createQuery')
            ->with($this->identicalTo($params), $this->identicalTo($query), $this->identicalTo($facets));
        
        $target->filter($params, $query, $facets);
    }

    public function testCreateQuery()
    {
        $query  = $this->getMockBuilder(\SolrDisMaxQuery::class)
            ->setMethods([
                'setQuery',
                'addFilterQuery',
                'addField',
                'addParam',
                'setHighlight',
                'addHighlightField',
            ])
            ->getMock()
        ;
        $coordinates = $this->getMockBuilder(CoordinatesInterface::class)
            ->getMock()
        ;
        $location = $this->getMockBuilder(Location::class)
            ->getMock()
        ;
        $location->method('getCoordinates')->willReturn($coordinates);
        $coordinates->expects($this->once())
            ->method('getCoordinates')
            ->willReturn([2.1,1.2])
        ;
        $publishedSince = new DateTime();

        // expect to setQuery
        $query
            ->expects($this->exactly(2))
            ->method('setQuery')
            ->withConsecutive(['*:*'],['some'])
        ;

        // expect to handle location
        $query
            ->expects($this->exactly(7))
            ->method('addFilterQuery')
            ->withConsecutive(
                ['entityName:job'],
                ['isActive:1'],
                ['entityName:job'],
                ['isActive:1'],
                [$this->stringContains('{!geofilt pt=1.2,2.1 sfield=point d=10 score="kilometers"}')],
                [$this->equalTo(sprintf('datePublishStart:[%s TO NOW]', Util::convertDateTime($publishedSince)))]
           );

        $query->method('addField')->willReturn($query);

        $query->expects($this->exactly(2))->method('setHighlight')->with(true)->will($this->returnSelf());
        $query->expects($this->exactly(2))->method('addHighlightField')->with('title')->will($this->returnSelf());

        $params1 = ['q' => '','sort'=>'title'];
        $params2 = ['q' => 'some','sort'=>'-company','l'=>$location,'d'=>10, 'publishedSince' => $publishedSince->format(DateTime::ISO8601)];
        
        $facets = $this->getMockBuilder(Facets::class)
            ->getMock();
        $facets->expects($this->atLeastOnce())
            ->method('addDefinition')
            ->willReturnSelf();
        $facets->expects($this->exactly(2))
            ->method('setParams')
            ->withConsecutive([$this->identicalTo($params1)], [$this->identicalTo($params2)])
            ->willReturnSelf();
        $facets->expects($this->exactly(2))
            ->method('setupQuery')
            ->with($this->identicalTo($query))
            ->willReturnSelf();
        
        $target = $this->target;
        $target->createQuery($params1, $query, $facets);
        $target->createQuery($params2, $query, $facets);
    }
    
    public function testProxyFactory()
    {
        $entity = $this->getMockBuilder(JobInterface::class)
            ->getMock();
        
        $this->assertInstanceOf(JobProxy::class, $this->target->proxyFactory($entity, new ArrayObject()));
    }
    
    public function testGetRepositoryName()
    {
        $this->assertSame('Jobs/Job', $this->target->getRepositoryName());
    }

    public function testCreateQueryWithFilterQueries()
    {
        $callback = function(SolrDisMaxQuery $query){
            $query->addFilterQuery('from-callback');
        };

        $options = new ModuleOptions([
            'filterQueries' => [
                'some-filter',
                $callback
            ]
        ]);
        $facets = $this->createMock(Facets::class);
        $query = $this->createMock(SolrDisMaxQuery::class);

        $query->expects($this->exactly(4))
            ->method('addFilterQuery')
            ->withConsecutive(
                ['entityName:job'],
                ['isActive:1'],
                ['some-filter'],
                ['from-callback']
            );

        $target = new JobBoardPaginationQuery($options);
        $target->filter([],$query,$facets);
    }
}
