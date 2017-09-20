<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

namespace Solr\Listener;


use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\PostFlushEventArgs;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Interop\Container\ContainerInterface;
use Jobs\Entity\Job;
use Jobs\Entity\StatusInterface;
use Solr\Bridge\Manager;
use Solr\Filter\EntityToDocument\JobEntityToSolrDocument as EntityToDocumentFilter;
use SolrClient;

/**
 * @author Anthonius Munthi <me@itstoni.com>
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.26
 * @package Solr\Listener
 */
class JobEventSubscriber implements EventSubscriber
{

    /**
     * @var Manager
     */
    protected $solrManager;
    
    /**
     * @var EntityToDocumentFilter
     */
    protected $entityToDocumentFilter;

    /**
     * @var SolrClient
     */
    protected $solrClient;
    
    /**
     * @var Job[]
     */
    protected $add = [];
    
    /**
     * @var Job[]
     */
    protected $delete = [];
    
    /**
     * @param Manager $manager
     * @param EntityToDocumentFilter $entityToDocumentFilter
     */
    public function __construct(Manager $manager, EntityToDocumentFilter $entityToDocumentFilter)
    {
        $this->solrManager = $manager;
        $this->entityToDocumentFilter = $entityToDocumentFilter;
    }
    
    /**
     * Define what event this subscriber listen to
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate,
            Events::postFlush
        ];
    }
    
    /**
     * @param LifecycleEventArgs $eventArgs
     * @since 0.27
     */
    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();
        
        // check for a job instance
        if (!$document instanceof Job) {
            return;
        }
        
        // check if the status or isDeleted flag has been changed
        if (!$eventArgs->hasChangedField('status') && !$eventArgs->hasChangedField('isDeleted')) {
            return;
        }
            
        // check if the job is active
        if ($document->isActive() && !$document->isDeleted()) {
            // mark it for commit
            $this->add[] = $document;
        } else {
            $status = $document->getStatus();
            
            // check if the status has been changed to inactive or expired
            // or isDeleted Flag is set.
            if ($document->isDeleted()
                || (
                    isset($status)
                    && in_array($status->getName(), [StatusInterface::INACTIVE, StatusInterface::EXPIRED])
                )
            ) {
                // mark it for delete
                $this->delete[] = $document;
            }
        }
    }
    
    /**
     * @param LifecycleEventArgs $eventArgs
     * @since 0.27
     */
    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        // check if there is any job to process
        if (!$this->add && !$this->delete) {
            return;
        }
        
        $client = $this->getSolrClient();
        
        // process jobs for commit
        foreach ($this->add as $job) {
            $document = $this->entityToDocumentFilter->filter($job);
            $client->addDocument($document);
        }
        
        // process jobs for delete
        foreach ($this->delete as $job) {
            $client->deleteByIds($this->entityToDocumentFilter->getDocumentIds($job));
        }
        
        // commit to index & optimize it
        $client = $this->getSolrClient();
        $client->commit();
        $client->optimize();
    }
    
    /**
	 * @return SolrClient
	 * @since 0.27
	 */
	protected function getSolrClient()
    {
        if (!isset($this->solrClient)) {
            $path = $this->solrManager->getOptions()->getJobsPath();
            $this->solrClient = $this->solrManager->getClient($path);
        }
        
        return $this->solrClient;
    }

    /**
     * @param ContainerInterface $container
     * @return JobEventSubscriber
     */
    static public function factory(ContainerInterface $container)
    {
        $options = $container->get('Solr/Options/Module');
        return new static($container->get('Solr/Manager'), new EntityToDocumentFilter($options));
    }
}
