<?php

namespace Solr\Factory\Filter;

use Interop\Container\ContainerInterface;
use Solr\Filter\JobBoardPaginationQuery;
use Zend\ServiceManager\Factory\FactoryInterface;
use Solr\Options\ModuleOptions;

/**
 * Class JobBoardPaginationQueryFactory
 * @package Solr\Filter
 */
class JobBoardPaginationQueryFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var ModuleOptions $options */
        $options = $container->get('Solr/Options/Module');

        $filter = new JobBoardPaginationQuery($options);
        return $filter;
    }

    /**
     * @param ContainerInterface $container
     * @return JobBoardPaginationQuery|mixed
     */
    public function createService(ContainerInterface $container)
    {
        return $this($container, JobBoardPaginationQuery::class);
    }
}
