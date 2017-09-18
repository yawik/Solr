<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

namespace Solr\Listener;

use Core\Listener\Events\CreatePaginatorEvent;

/**
 * Class CreatePaginatorListener
 *
 * @author Anthonius Munthi <me@itstoni.com>
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.26
 * @package Solr\Event\Listener
 */
class CreatePaginatorListener
{
    /**
     * Replace paginator like Jobs/Board with Solr/Jobs/Board
     *
     * @param CreatePaginatorEvent $event
     */
    public function onCreatePaginator(CreatePaginatorEvent $event)
    {
    	/* @TODO: [ZF3] get parameters from $event->getPaginators is not working anymore */
    	$target = $event->getTarget();
        $params = $target->getPaginatorParams();
        $paginatorName = $target->getPaginatorName();
        $paginators = $target->getPaginators();
        $serviceName = 'Solr/' . $paginatorName;
        if (is_object($paginators) && $paginators->has($serviceName)) {
            /* @var \Zend\Paginator\Paginator $paginator */
            // yes, we have that solr paginator to replace $paginatorName
            $paginator = $paginators->get($serviceName, $params);
            $event->setPaginator($paginator);
        }
    }
}