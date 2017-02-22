<?php

namespace Solr\Factory\Filter;

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
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return JobBoardPaginationQuery|mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var $services \Zend\ServiceManager\ServiceManager */
        $services = $serviceLocator->getServiceLocator();
        /* @var ModuleOptions $options */
        $options = $services->get('Solr/Options/Module');

        $filter = new JobBoardPaginationQuery($options);
        return $filter;
    }
}
