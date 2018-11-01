<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.27
 */
namespace Solr\Factory\Controller;

use Interop\Container\ContainerInterface;
use Solr\Controller\ConsoleController;
use Zend\ServiceManager\Factory\FactoryInterface;
use Core\Console\ProgressBar;

class ConsoleControllerFactory implements FactoryInterface
{
	public function __invoke( ContainerInterface $container, $requestedName, array $options = null )
	{
		$manager = $container->get('Solr/Manager');
		$options = $container->get('Solr/Options/Module');
		$client = $manager->getClient($manager->getOptions()->getJobsPath());
		$jobRepository = $container->get('repositories')->get('Jobs/Job');
		$progressBarFactory = function ($count, $persistenceNamespace = null) {
			return new ProgressBar($count, $persistenceNamespace);
		};
		
		return new ConsoleController($client, $jobRepository, $progressBarFactory, $options);
	}
}
