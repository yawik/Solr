<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

namespace Solr\Filter;

use Solr\Options\ModuleOptions;
use Jobs\Entity\Location;
use Jobs\Entity\Job;
use Solr\Bridge\Util;
use Solr\Entity\JobProxy;
use SolrDisMaxQuery;
use SolrQuery;
use ArrayAccess;
use Solr\Facets;
use DateTime;

/**
 * Class JobBoardPaginationQuery
 *
 * @author Anthonius Munthi <me@itstoni.com>
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 */
class JobBoardPaginationQuery extends AbstractPaginationQuery
{
    /**
     * @var ModuleOptions $options
     */
    protected $options;

    /**
     * @param $options
     */
    public function __construct(ModuleOptions $options)
    {
        $this->options = $options;
    }

    /**
     * @inheritdoc
     */
    public function createQuery(array $params, SolrDisMaxQuery $query, Facets $facets)
    {
        $search = isset($params[$this->options->getParameterName(ModuleOptions::FIELD_QUERY)])
            ? trim($params[$this->options->getParameterName(ModuleOptions::FIELD_QUERY)]) : '';

        if (!empty($search)) {
            $q = $search; //\SolrUtils::escapeQueryChars($search);
            $query->addSortField('score', SolrQuery::ORDER_DESC);
            $query->addSortField('title', SolrQuery::ORDER_ASC);
            $query->addQueryField('title', '4');
            $query->addQueryField('text', '1');
            $query->addQueryField('organizationName', '2');
            $query->addQueryField('location', '0.2');
            $query->setMinimumMatch(2);
        } else {
            $q = '*:*';
            $query->addSortField('datePublishStart', SolrQuery::ORDER_DESC);
        }

        $query->setQuery($q);
        $query->addFilterQuery('entityName:job');
        $query->addFilterQuery('isActive:1');
        $query->addField('*');

        if (isset($params[$this->options->getParameterName(ModuleOptions::FIELD_LOCATION)])) {
            /* @var Location $location */
            $location = $params[$this->options->getParameterName(ModuleOptions::FIELD_LOCATION)];
            if (is_object($location) and is_object($location->getCoordinates())) {
                $coordinate = Util::convertLocationCoordinates($location);
            } elseif (preg_match('/^c:[0-9]+,[0-9]+:[0-9]+,[0-9]+/', $location)) {
                $coordinate = Util::convertLocationString($location);
            }
            if (isset($coordinate)) {
                $query->addFilterQuery(
                    sprintf(
                        '{!parent which="entityName:job" childQuery="entityName:location"}{!geofilt pt=%s sfield=point d=%d score="kilometers"}',
                        $coordinate,
                        $params[$this->options->getParameterName(ModuleOptions::FIELD_DISTANCE)]
                    )
                );
                $query->addParam(
                    'locations.q',
                    sprintf(
                        'entityName:location AND {!terms f=_root_ v=$row.id} AND {!geofilt pt=%s sfield=point d=%s}',
                        $coordinate,
                        $params[$this->options->getParameterName(ModuleOptions::FIELD_DISTANCE)]
                    )
                ); // join

                $query->addField('locations:[subquery]')
                      ->addField('distance:min(geodist(points,' . $coordinate . '))');

                $query->addField('score');
            }
        }

        if (isset($params[$this->options->getParameterName(ModuleOptions::FIELD_ORGANIZATION)])) {
            $query->addFilterQuery(
             sprintf(
               "organizationName:'%s'",
               \SolrUtils::escapeQueryChars(
                 $params[
                 $this->options->getParameterName(ModuleOptions::FIELD_ORGANIZATION)
                 ]
               )
             )
           );
        }
        
        if (isset($params['publishedSince'])) {
            $publishedSince = $params['publishedSince'];
            
            if (!$publishedSince instanceof DateTime) {
                $publishedSince = new DateTime($publishedSince);
            }
            
            $query->addFilterQuery(sprintf('datePublishStart:[%s TO NOW]', Util::convertDateTime($publishedSince)));
        }

        // boost newest jobs
        $query->addParam('bf', 'recip(abs(ms(NOW/HOUR,datePublishStart)),3.16e-11,1,.1)');

        // adds an additional 'highlights' section into the result set
        $query->setHighlight(true);
        $query->addHighlightField('title');

        $query->setFacetLimit($this->options->getFacetLimit());

        foreach ($this->options->getFacetFields() as $facetField) { // facets
            $facets->addDefinition($facetField['name'], $facetField['label']);
        }

        $this->configureFilterQuery($query);

        $facets->setParams($params);
        $facets->setupQuery($query);

        $query->setFacetMinCount(1);

        /*
         * Add support for the 'publishedSince' parameter used by the JobsByMail-Module
         * This parameter is used to provide the date when the last mail was send.
         * So we want to only find jobs which are newer...
         */
        if (isset($params['publishedSince'])) {
            $publishedSince = $params['publishedSince'];

            if (!$publishedSince instanceof \DateTime) {
                $publishedSince = new \DateTime($publishedSince);
            }

            $query->addFilterQuery('datePublishStart:[' . Util::convertDateTime($publishedSince) . ' TO * ]');
        }
    }

    /**
     * @see \Solr\Filter\AbstractPaginationQuery::proxyFactory()
     */
    public function proxyFactory($entity, ArrayAccess $solrResult)
    {
        return new JobProxy($entity, $solrResult);
    }

    /**
     * @see \Solr\Filter\AbstractPaginationQuery::getRepositoryName()
     */
    public function getRepositoryName()
    {
        return 'Jobs/Job';
    }

    private function configureFilterQuery(SolrDisMaxQuery $query)
    {
        $filters = $this->options->getFilterQueries();

        foreach($filters as $filter){
            if(is_scalar($filter)){
                $query->addFilterQuery($filter);
            }elseif(is_callable($filter)){
                call_user_func_array($filter,[$query]);
            }
        }
    }
}
