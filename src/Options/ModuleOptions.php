<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

namespace Solr\Options;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Zend\Stdlib\AbstractOptions;

/**
 * Provide available options for Solr Module
 *
 * @author  Anthonius Munthi <me@itstoni.com>
 * @since   0.26
 * @package Solr\Options
 */
class ModuleOptions extends AbstractOptions
{
    const FIELD_QUERY = 'q';
    const FIELD_LOCATION = 'l';
    const FIELD_DISTANCE = 'd';
    const FIELD_ORGANIZATION = 'o';

    private $validFields = [
      self::FIELD_QUERY,
      self::FIELD_LOCATION,
      self::FIELD_DISTANCE,
      self::FIELD_ORGANIZATION
    ];

    /**
     * @var bool
     */
    protected $secure = false;

    /**
     * @var string
     */
    protected $hostname = 'localhost';

    /**
     * @var integer
     */
    protected $port = 8983;

    /**
     * @var string
     */
    protected $path = '/solr';

    /**
     * @var string
     */
    protected $username = '';

    /**
     * @var string
     */
    protected $password = '';

    /**
     * @var string
     */
    protected $jobsPath = '/solr/YawikJobs';

    /**
     * @var $facetFields array
     */
    protected $facetFields = [
            [
                'name' => 'region_MultiString',
                'label' => 'Region',
            ],
            [
                'name' => 'city_MultiString',
                'label' => 'City'
            ]
        ];

    /**
     * The maximum number of constraint counts
     *
     * @var int
     */
    protected $facetLimit = 10;

    /**
     * The minimum count
     *
     * @var int
     */
    protected $facetMinCount = 1;

    /**
     * @var $parameterNames array
     */
    protected $parameterNames = [
        self::FIELD_QUERY => [
            'name' => 'q'
        ],
        self::FIELD_LOCATION => [
            'name' => 'l'
        ],
        self::FIELD_DISTANCE => [
            'name' => 'd'
        ],
        self::FIELD_ORGANIZATION => [
          'name' => 'o'
        ]
    ];

    protected $mappings = [
        'profession' => 'profession_MultiString',
        'employmentType' => 'employmentType_MultiString',
    ];

    /**
     * @var array
     */
    protected $filterQueries = [];

    /**
     * @return array
     */
    public function getFilterQueries()
    {
        return $this->filterQueries;
    }

    /**
     * @param array $filterQueries
     */
    public function setFilterQueries(array $filterQueries)
    {
        $this->filterQueries = $filterQueries;
    }

    /**
     * @return boolean
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * @param boolean $secure
     * @return ModuleOptions
     */
    public function setSecure($secure)
    {
        $this->secure = $secure;

        return $this;
    }
    
    /**
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * @param string $hostname
     * @return ModuleOptions
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;

        return $this;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int $port
     * @return ModuleOptions
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return ModuleOptions
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return ModuleOptions
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return ModuleOptions
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getJobsPath()
    {
        return $this->jobsPath;
    }

    /**
     * @param string $jobsPath
     * @return ModuleOptions
     */
    public function setJobsPath($jobsPath)
    {
        $this->jobsPath = $jobsPath;

        return $this;
    }

    /**
     * @return array
     */
    public function getFacetFields()
    {
        return $this->facetFields;
    }

    /**
     * @param $facetFields
     *
     * @return $this
     */
    public function setFacetFields($facetFields)
    {
        $this->facetFields=$facetFields;
        return $this;
    }

    /**
     * @return array
     */
    public function getParameterNames()
    {
        return $this->parameterNames;
    }

    /**
     * @param $parameterNames
     *
     * @return $this
     */
    public function setParameterNames($parameterNames)
    {
        $this->parameterNames=$parameterNames;
        return $this;
    }

    /**
     * @return array
     */
    public function getFacetLimit()
    {
        return $this->facetLimit;
    }

    /**
     * @param $facetLimit
     *
     * @return $this
     */
    public function setFacetLimit($facetLimit)
    {
        $this->facetLimit=$facetLimit;
        return $this;
    }

    /**
     * @return array
     */
    public function getFacetMinCount()
    {
        return $this->facetMinCount;
    }

    /**
     * @param $facetMinCount
     *
     * @return $this
     */
    public function setFacetMinCount($facetMinCount)
    {
        $this->facetMinCount=$facetMinCount;
        return $this;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getParameterName($key)
    {
        if (!in_array($key, $this->validFields)) {
            throw new \InvalidArgumentException('an invalid field name was passed. Valid fieldnames are: (' . implode('|', $this->validFields).')');
        }
        return $this->parameterNames[$key]['name'];
    }

    public function getMappings()
    {
        return $this->mappings;
    }

    public function setMappings($mappings)
    {
        $this->mappings=$mappings;
        return $this;
    }
}
