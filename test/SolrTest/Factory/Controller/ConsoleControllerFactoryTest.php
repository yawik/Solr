<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.27
 */

namespace SolrTest\Factory\Controller;

use PHPUnit\Framework\TestCase;

use Interop\Container\ContainerInterface;
use Solr\Factory\Controller\ConsoleControllerFactory;
use Solr\Bridge\Manager;
use Solr\Options\ModuleOptions;
use SolrClient;
use Jobs\Repository\Job as JobRepository;
use Solr\Controller\ConsoleController;
use Core\Console\ProgressBar;

/**
 * @coversDefaultClass \Solr\Factory\Controller\ConsoleControllerFactory
 */
class ConsoleControllerFactoryTest extends TestCase
{

    /**
     * @covers ::__invoke
     */
    public function testInvokation()
    {
        $client = $this->getMockBuilder(SolrClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $manager = $this->getMockBuilder(Manager::class)
            ->setConstructorArgs([new ModuleOptions()])
            ->setMethods(['getClient'])
            ->getMock();
        $manager->expects($this->once())
            ->method('getClient')
            ->willReturn($client);

        $options = $this->getMockBuilder(ModuleOptions::class)
                       ->disableOriginalConstructor()
                       ->getMock();

        $repository = $this->getMockBuilder(JobRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $repositories = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $repositories->expects($this->once())
            ->method('get')
            ->with($this->equalTo('Jobs/Job'))
            ->willReturn($repository);
            
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $container->expects($this->exactly(3))
            ->method('get')
            ->will($this->returnValueMap([
                ['Solr/Manager', $manager],
                ['repositories', $repositories],
                ['Solr/Options/Module', $options]
            ]));
        
        $controllerFactory = new ConsoleControllerFactory();
        $controller = $controllerFactory($container,'SomeName');
        $this->assertInstanceOf(ConsoleController::class, $controller);
        $this->assertInstanceOf(ProgressBar::class, $controller->getProgressBarFactory()->__invoke(0, 'preventOutput'));
    }
}
