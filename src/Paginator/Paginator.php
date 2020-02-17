<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.27
 */
namespace Solr\Paginator;

use Laminas\Paginator\Exception\InvalidArgumentException;
use Solr\FacetsProviderInterface;

class Paginator extends \Laminas\Paginator\Paginator implements FacetsProviderInterface
{

    /**
     * @see \Laminas\Paginator\Paginator::__construct()
     */
    public function __construct($adapter)
    {
        if (!$adapter instanceof FacetsProviderInterface) {
            throw new InvalidArgumentException(sprintf('adapter must implement %s interface', FacetsProviderInterface::class));
        }
        
        parent::__construct($adapter);
    }

    /**
     * @see \Solr\FacetsProviderInterface::getFacets()
     */
    public function getFacets()
    {
        return $this->adapter->getFacets();
    }
}
