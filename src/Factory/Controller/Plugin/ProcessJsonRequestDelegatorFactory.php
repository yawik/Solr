<?php declare(strict_types=1);
/**
 * YAWIK-Solr
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

namespace Solr\Factory\Controller\Plugin;

use Interop\Container\ContainerInterface;
use Solr\Controller\Plugin\FacetsInjectorProcessJsonRequestDecorator;
use Zend\ServiceManager\Factory\DelegatorFactoryInterface;

/**
 * Factory for \Solr\Factory\Controller\Plugin\ProcessJsonRequestDelegator
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class ProcessJsonRequestDelegatorFactory implements DelegatorFactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $name,
        callable $callback,
        ?array $options = null
    ): FacetsInjectorProcessJsonRequestDecorator {
        return new FacetsInjectorProcessJsonRequestDecorator(
            $callback()
        );
    }
}
