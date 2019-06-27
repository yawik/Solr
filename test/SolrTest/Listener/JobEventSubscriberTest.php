<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

namespace SolrTest\Listener;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Core\Options\ModuleOptions;
use Doctrine\ODM\MongoDB\Event\PostFlushEventArgs;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Interop\Container\ContainerInterface;
use Jobs\Entity\Job;
use Jobs\Entity\StatusInterface;
use Solr\Bridge\Manager;
use Solr\Filter\EntityToDocument\JobEntityToSolrDocument as EntityToDocumentFilter;
use Solr\Listener\JobEventSubscriber;
use SolrInputDocument;
use stdClass;

/**
 * Class TestJobEventSubscriber
 * making it possible to test add and delete property on JobEventSubscriber
 *
 * @package SolrTest\Listener
 */
class TestJobEventSubscriber extends JobEventSubscriber
{
    /**
     * @return Job[]
     */
    public function getAdd(): array
    {
        return $this->add;
    }

    /**
     * @return Job[]
     */
    public function getDelete(): array
    {
        return $this->delete;
    }
}


/**
 * Test for Solr\Listener\JobEventSubscriber
 *
 * @author Anthonius Munthi <me@itstoni.com>
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.26
 * @requires extension solr
 * @package SolrTest\Listener
 * @coversDefaultClass \Solr\Listener\JobEventSubscriber
 */
class JobEventSubscriberTest extends TestCase
{
    /**
     * @var TestJobEventSubscriber
     */
    protected $subscriber;

    /**
     * @var MockObject
     */
    protected $manager;

    /**
     * @var MockObject
     */
    protected $client;

    /**
     * @var MockObject
     */
    protected $entityToDocumentFilter;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $options = $this->getMockBuilder(ModuleOptions::class)
            ->setMethods(['getJobsPath'])
            ->getMock();
        $options->method('getJobsPath')
            ->willReturn('/some/path');
        
        $this->client = $this->getMockBuilder(\SolrClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->manager = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->manager->method('getClient')
            ->willReturn($this->client);
        $this->manager->method('getOptions')
            ->willReturn($options);
        
        $this->entityToDocumentFilter = $this->getMockBuilder(EntityToDocumentFilter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subscriber = new TestJobEventSubscriber($this->manager, $this->entityToDocumentFilter);
    }

    /**
     * @covers ::__construct()
     * @covers ::getSubscribedEvents()
     */
    public function testShouldSubscribeToDoctrineEvent()
    {
        $subscribedEvents = $this->subscriber->getSubscribedEvents();

        $this->assertContains(Events::prePersist, $subscribedEvents);
        $this->assertContains(Events::preUpdate, $subscribedEvents);
        $this->assertContains(Events::postFlush, $subscribedEvents);
    }

    /**
     * @covers ::prePersist()
     */
    public function testPrePersistShouldNotProcessNonJobDocument()
    {
        $job = $this->getMockBuilder(stdClass::class)
            ->setMethods(['isActive'])
            ->getMock();
        $job->expects($this->never())
            ->method('isActive');
        
        $event = $this->getMockBuilder(PreUpdateEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getDocument')
            ->willReturn($job);
        
        $this->subscriber->prePersist($event);
    }
    
    /**
     * @param string $status
     * @param bool $shouldBeAdded
     * @covers ::prePersist()
     * @dataProvider jobStateData()
     */
    public function testPrePersistWithJobDocument($status, $shouldBeAdded)
    {
        $job = new Job();
        $job->setStatus($status);
        $event = $this->getMockBuilder(LifecycleEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getDocument')
            ->willReturn($job);

        $subscriber = $this->subscriber;
        $subscriber->prePersist($event);

        if ($shouldBeAdded) {
            $this->assertContains($job,  $subscriber->getAdd());
        } else {
            $this->assertNotContains($job, $subscriber->getAdd());
        }
    }

    /**
     * @covers ::preUpdate()
     */
    public function testPreUpdateShouldNotProcessNonJobDocument()
    {
        $event = $this->getMockBuilder(PreUpdateEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getDocument');
        $event->expects($this->never())
            ->method('hasChangedField');
        
        $this->subscriber->preUpdate($event);
    }

    /**
     * @covers ::preUpdate()
     */
    public function testPreUpdateShouldNotProcessDocumentWithUnchangedStatus()
    {
        $job = $this->getMockBuilder(Job::class)
            ->getMock();
        $job->expects($this->never())
            ->method('isActive');
        $event = $this->getMockBuilder(PreUpdateEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getDocument')
            ->willReturn($job);
        $event->expects($this->exactly(2))
            ->method('hasChangedField')
            ->withConsecutive(
                [$this->equalTo('status')],
                [$this->equalTo('isDeleted')]
            )
            ->willReturn(false);

        $this->subscriber->preUpdate($event);
    }

    /**
     * @param string $status
     * @param bool $shouldBeAdded
     * @param bool $shouldBeDeleted
     * @covers ::preUpdate()
     * @dataProvider jobStateData()
     */
    public function testPreUpdateWithChangedStatus($status, $shouldBeAdded, $shouldBeDeleted)
    {
        $job = new Job();
        $job->setStatus($status);
        $event = $this->getMockBuilder(PreUpdateEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getDocument')
            ->willReturn($job);
        $event->expects($this->once())
            ->method('hasChangedField')
            ->with($this->equalTo('status'))
            ->willReturn(true);

        $subscriber = $this->subscriber;
        $subscriber->preUpdate($event);
        
        if ($shouldBeAdded) {
            $this->assertContains($job, $subscriber->getAdd());
        } else {
            $this->assertNotContains($job, $subscriber->getAdd());
        }

        if ($shouldBeDeleted) {
            $this->assertContains($job,$subscriber->getDelete());
        } else {
            $this->assertNotContains($job, $subscriber->getDelete());
        }
    }
    
    /**
     * @covers ::postFlush()
     */
    public function testPostFlushWithNoJobsToProcess()
    {
        $subscriber = $this->getMockBuilder(JobEventSubscriber::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSolrClient'])
            ->getMock();
        $subscriber->expects($this->never())
            ->method('getSolrClient');
        
        $event = $this->getMockBuilder(PostFlushEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $subscriber->postFlush($event);
    }
    
    /**
     * @param string $status
     * @param bool $shouldBeAdded
     * @param bool $shouldBeDeleted
     * @covers ::postFlush()
     * @covers ::getSolrClient()
     * @dataProvider jobStateData()
     */
    public function testPostFlushWithJobsToProcess($status, $shouldBeAdded, $shouldBeDeleted)
    {
        $job = new Job();
        $job->setStatus($status);
        $event = $this->getMockBuilder(PreUpdateEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getDocument')
            ->willReturn($job);
        $event->expects($this->once())
            ->method('hasChangedField')
            ->with($this->equalTo('status'))
            ->willReturn(true);
        
        $this->subscriber->preUpdate($event);
        
        if ($shouldBeAdded) {
            $document = new SolrInputDocument();
            $this->entityToDocumentFilter->expects($this->once())
                ->method('filter')
                ->with($this->identicalTo($job))
                ->willReturn($document);
            
            $this->client->expects($this->once())
                ->method('addDocument')
                ->with($this->identicalTo($document));
        }
        
        if ($shouldBeDeleted) {
            $ids = [1, 2, 3];
            $this->entityToDocumentFilter->expects($this->once())
                ->method('getDocumentIds')
                ->with($this->identicalTo($job))
                ->willReturn($ids);
            
            $this->client->expects($this->once())
                ->method('deleteByIds')
                ->with($this->identicalTo($ids));
        }
        
        if ($shouldBeAdded || $shouldBeDeleted) {
            $this->client->expects($this->once())
                ->method('commit');
            $this->client->expects($this->once())
                ->method('optimize');
        }
        
        $event = $this->getMockBuilder(PostFlushEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->subscriber->postFlush($event);
    }
    
    /**
     * @covers ::factory()
     */
    public function testFactory()
    {
        $options = new \Solr\Options\ModuleOptions();
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $container->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive([$this->equalTo('Solr/Options/Module')],
                              [$this->equalTo('Solr/Manager')])
            ->will($this->onConsecutiveCalls($options,$this->manager));
        
        $this->assertInstanceOf(JobEventSubscriber::class, JobEventSubscriber::factory($container));
    }
    
    /**
     * @return array
     */
    public function jobStateData()
    {
        return [
            [StatusInterface::ACTIVE, true, false],
            [StatusInterface::CREATED, false, false],
            [StatusInterface::EXPIRED, false, true],
            [StatusInterface::INACTIVE, false, true],
            [StatusInterface::WAITING_FOR_APPROVAL, false, false],
            [StatusInterface::PUBLISH, false, false],
            [StatusInterface::REJECTED, false, false]
        ];
    }
}
