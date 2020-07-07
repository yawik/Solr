<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

namespace SolrTest\Controller;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use Core\Console\ProgressBar;
use Doctrine\MongoDB\CursorInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Solr\Options\ModuleOptions;
use SolrClient;
use Jobs\Entity\Job;
use Jobs\Repository\Job as JobRepository;
use Solr\Controller\ConsoleController;
use stdClass;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\ServiceManager\ServiceManager;

/**
 * Class ConsoleControllerTest
 *
 * @author Anthonius Munthi <me@itstoni.com>
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.26
 * @package SolrTest\Controller
 * @coversDefaultClass \Solr\Controller\ConsoleController
 */
class ConsoleControllerTest extends TestCase
{

    /**
     * @var ConsoleController
     */
    protected $controller;

    /**
     * @var SolrClient|MockObject
     */
    protected $client;

    /**
     * @var CursorInterface|MockObject
     */
    protected $cursor;

    /**
     * @var ProgressBar|MockObject
     */
    protected $progressBar;

    /**
     * @var MockObject
     */
    protected $progressBarFactory;

    /**
     * @var \Solr\Options\ModuleOptions
     */
    protected $options;

    protected $params;

    protected $qb;

    /**
     * {@inheritDoc}
     */
    protected function setUp():void
    {
        $this->client = $this->getMockBuilder(SolrClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cursor = $this->getMockBuilder(CursorInterface::class)
            ->getMock();

        $jobRepo = $this->getMockBuilder(JobRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dm = $this->getMockBuilder(DocumentManager::class)->disableOriginalConstructor()->getMock();
        $dm->expects($this->any())->method('clear');

        $jobRepo->expects($this->any())->method('getDocumentManager')->willReturn($dm);

        $query = $this->getMockBuilder(\Doctrine\MongoDb\Query\Query::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();
        $query->expects($this->any())->method('execute')->willReturn($this->cursor);

        $qb = $this->getMockBuilder(\Doctrine\MongoDB\Query\Builder::class)
            ->disableOriginalConstructor()
            ->setMethods(['hydrate', 'field', 'in', 'equals', 'readOnly', 'limit', 'skip', 'getQuery'])
            ->getMock();
        $qb->expects($this->any())->method('hydrate')->will($this->returnSelf());
        $qb->expects($this->any())->method('field')->will($this->returnSelf());
        $qb->expects($this->any())->method('in')->will($this->returnSelf());
        $qb->expects($this->any())->method('equals')->will($this->returnSelf());
        $qb->expects($this->any())->method('readOnly')->will($this->returnSelf());
        $qb->expects($this->any())->method('getQuery')->willReturn($query);
        $jobRepo->expects($this->any())
            ->method('createQueryBuilder')
            ->willReturn($qb);

        $this->qb = $qb;
        $this->progressBar = $this->getMockBuilder(ProgressBar::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->options = $this->getMockBuilder(ModuleOptions::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();

        $this->progressBarFactory = $this->getMockBuilder(stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();
        $this->progressBarFactory->expects($this->any())
            ->method('__invoke')
            ->willReturn($this->progressBar);

        $this->controller = new ConsoleController($this->client, $jobRepo, $this->progressBarFactory, $this->options);

        $this->params = $this->getMockBuilder(\Laminas\Mvc\Controller\Plugin\Params::class)
            ->disableOriginalConstructor()
            ->setMethods(['__invoke'])
            ->getMock();
        $plugins = new PluginManager(new ServiceManager());
        $plugins->setService('params', $this->params);

        $this->controller->setPluginManager($plugins);
    }

    /**
     * @covers ::__construct()
     * @covers ::activeJobIndexAction()
     */
    public function testActiveJobIndexActionWithoutJobs()
    {
        $this->qb->expects($this->never())->method('limit');
        $this->qb->expects($this->never())->method('skip');
        $this->params->expects($this->once())->method('__invoke')->willReturn(null);
        $this->cursor->expects($this->once())
            ->method('count')
            ->willReturn(0);
        $this->cursor->expects($this->never())
            ->method('rewind');

        $this->progressBarFactory->expects($this->never())
            ->method('__invoke');

        $this->progressBar->expects($this->never())
            ->method('update');

        $this->client->expects($this->never())
            ->method('addDocument');
        $this->client->expects($this->never())
            ->method('commit');
        $this->client->expects($this->never())
            ->method('optimize');

        $this->assertStringContainsString('no active job', $this->controller->activeJobIndexAction());
    }

    /**
     * @covers ::__construct()
     * @covers ::activeJobIndexAction()
     */
    public function testActiveJobIndexActionWithJobs()
    {
        $job = new Job();
        $count = 2;

        $this->cursor->expects($this->once())
            ->method('count')
            ->willReturn($count);
        $this->cursor->expects($this->exactly($count + 1))
            ->method('valid')
            ->willReturnOnConsecutiveCalls(true, true, false);
        $this->cursor->expects($this->exactly($count))
            ->method('current')
            ->willReturn($job);

        $this->progressBar->expects($this->exactly($count))
            ->method('update')
            ->withConsecutive([1, 'Job 1 / 2'], [2, 'Job 2 / 2']);

        $this->client->expects($this->exactly($count))
            ->method('addDocument');
        $this->client->expects($this->once())
            ->method('commit');
        $this->client->expects($this->once())
            ->method('optimize');

        $this->assertEmpty(trim($this->controller->activeJobIndexAction()));
    }

    /**
     * @covers ::getProgressBarFactory()
     */
    public function testGetProgressBarFactory()
    {
        $progressBarFactory = $this->controller->getProgressBarFactory();
        $this->assertIsCallable($progressBarFactory);
    }
}
