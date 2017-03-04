<?php

namespace Solr\Factory\Filter;

use Interop\Container\ContainerInterface;
use Solr\Filter\JobBoardPaginationQuery;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Solr\Options\ModuleOptions;

/**
 * Class JobBoardPaginationQueryFactory
 * @package Solr\Filter
 */
class JobBoardPaginationQueryFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var $services \Zend\ServiceManager\ServiceManager */
        $services = $container->getServiceLocator();
        /* @var ModuleOptions $options */
        $options = $services->get('Solr/Options/Module');

        $filter = new JobBoardPaginationQuery($options);
        return $filter;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return JobBoardPaginationQuery|mixed
     */
    public function createService(ServiceLocatorInterface $services)
    {
        return $this($services, JobBoardPaginationQuery::class);
    }
}
