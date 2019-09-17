<?php declare(strict_types=1);
/**
 * YAWIK-Solr
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

namespace Solr\Controller\Plugin;

use Jobs\Controller\Plugin\ProcessJsonRequest;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class FacetsInjectorProcessJsonRequestDecorator extends ProcessJsonRequest
{
    /**
     * @var ProcessJsonRequest
     */
    private $processJsonRequestPlugin;

    public function __construct(ProcessJsonRequest $processJsonRequestPlugin)
    {
        $this->processJsonRequestPlugin = $processJsonRequestPlugin;
    }

    public function setController(\Zend\Stdlib\DispatchableInterface $controller): void
    {
        parent::setController($controller);
        $this->processJsonRequestPlugin->setController($controller);
    }

    public function __invoke(array $data): array
    {
        $result = $this->processJsonRequestPlugin->__invoke($data);

        $paginator = $data['jobs'];

        if ($paginator instanceof \Solr\FacetsProviderInterface) {
            $facets = $paginator->getFacets();
            $result['facets'] = $facets->toArray();
            $result['activeFacets'] = $facets->getActiveValues();
        }

        return $result;
    }
}
