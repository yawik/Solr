<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

namespace Solr\Paginator;

use Core\Paginator\PaginatorService;
use Interop\Container\ContainerInterface;
use Solr\Bridge\ResultConverter;
use Solr\Options\ModuleOptions;
use Solr\Paginator\Adapter\SolrAdapter;
use Solr\Facets;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Abstract class for Solr paginator factory
 *
 * @author Anthonius Munthi <me@itstoni.com>
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @since 0.26
 * @since 0.30 Made factory ZF3 compatible
 */
abstract class PaginatorFactoryAbstract implements FactoryInterface
{

	public function __invoke( ContainerInterface $container, $requestedName, array $options = null )
	{
		/* @var PaginatorService $serviceLocator */
		/* @var ResultConverter $resultConverter */
		$filter             = $container->get('FilterManager')->get($this->getFilter());
		$moduleOptions      = $container->get('Solr/Options/Module');
		$connectPath        = $this->getConnectPath($moduleOptions);
		$solrClient         = $container->get('Solr/Manager')->getClient($connectPath);
		$resultConverter    = $container->get('Solr/ResultConverter');
		$adapter            = new SolrAdapter($solrClient, $filter, $resultConverter, new Facets(), $options);
		$service            = new Paginator($adapter);
		
		return $service;
	}
	
    /**
     * pagination service name
     *
     * @return string
     */
    abstract protected function getFilter();

    /**
     *
     * Get connection path for this paginator
     *
     * @param   ModuleOptions $options
     * @return  string
     */
    abstract protected function getConnectPath(ModuleOptions $options);
}
