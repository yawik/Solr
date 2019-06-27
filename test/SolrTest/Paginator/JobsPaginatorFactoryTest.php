<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

namespace SolrTest\Paginator;

use PHPUnit\Framework\TestCase;

use Interop\Container\ContainerInterface;
use Solr\Bridge\Manager;
use Solr\Bridge\ResultConverter;
use Solr\Filter\JobBoardPaginationQuery;
use Solr\Options\ModuleOptions;
use Solr\Paginator\Adapter\SolrAdapter;
use Solr\Paginator\JobsBoardPaginatorFactory;
use Zend\Filter\FilterPluginManager;
use Zend\Paginator\Paginator;

/**
 * Class JobsPaginatorFactoryTest
 *
 * @author  Anthonius Munthi <me@itstoni.com>
 * @author  Mathias Gelhausen <gelhausen@cross-solution.de>
 * @since   0.26
 * @package SolrTest\Paginator
 * @covers  \Solr\Paginator\JobsBoardPaginatorFactory
 * @covers  \Solr\Paginator\PaginatorFactoryAbstract
 * @requires extension solr
 */
class JobsPaginatorFactoryTest extends TestCase
{

    public function testInvokation()
    {
        $filterManager = $this->getMockBuilder(FilterPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $paginationQuery = $this->getMockBuilder(JobBoardPaginationQuery::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $filterManager
            ->expects($this->once())
            ->method('get')
            ->with('Solr/Jobs/PaginationQuery')
            ->willReturn($paginationQuery)
        ;

        $solrManager = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $solrManager->expects($this->once())
            ->method('getClient')
            ->with('/some/path');
        $converter = $this->getMockBuilder(ResultConverter::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $options = $this->getMockBuilder(ModuleOptions::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $options->expects($this->once())
            ->method('getJobsPath')
            ->willReturn('/some/path')
        ;
        $sl = $this->getMockBuilder(ContainerInterface::class)
            ->setMethods(['get','has'])
            ->getMock()
        ;
        $sl->expects($this->exactly(4))
            ->method('get')
            ->withConsecutive(
                ['FilterManager'],
                ['Solr/Options/Module'],
                ['Solr/Manager'],
                ['Solr/ResultConverter']
            )
            ->willReturnOnConsecutiveCalls($filterManager,$options,$solrManager,$converter)
        ;

        $target = new JobsBoardPaginatorFactory();

        $retVal = $target($sl,'SomeName', ['name' => 'value']);
        $this->assertInstanceOf(
            Paginator::class,
            $retVal,
            '::createService should return paginator service'
        );

        $this->assertInstanceOf(
            SolrAdapter::class,
            $retVal->getAdapter(),
            '::createService should create paginator with SolrAdapter as adapter'
        );

    }
}
