<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

namespace Solr\Controller;

use Core\Console\ProgressBar;
use Jobs\Repository\Job as JobRepository;
use SolrClient;
use Laminas\Mvc\Controller\AbstractActionController;

/**
 * @author Anthonius Munthi <me@itstoni.com>
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.26
 * @package Solr\Controller
 */
class ConsoleController extends AbstractActionController
{

    /**
     * @var \Solr\Options\ModuleOptions
     */
    protected $options;

    /**
     * @var SolrClient
     */
    protected $solrClient;
    
    /**
     * @var JobRepository
     */
    protected $jobRepository;
    
    /**
     * @var callable
     */
    protected $progressBarFactory;
    
    /**
     * @param SolrClient $solrClient
     * @param JobRepository $jobRepository
     * @param callable $progressBarFactory
     * @since 0.27
     */
    public function __construct(SolrClient $solrClient, JobRepository $jobRepository, callable $progressBarFactory, $options)
    {
        $this->solrClient = $solrClient;
        $this->jobRepository = $jobRepository;
        $this->progressBarFactory = $progressBarFactory;
        $this->options = $options;
    }

    public function activeJobIndexAction()
    {
        $jobs = $this->jobRepository->findActiveJob();
        $count = $jobs->count();
        
        // check if there is any active job
        if (0 === $count) {
            return 'There is no active job' . PHP_EOL;
        }
        
        $i = 1;
        $progressBarFactory = $this->progressBarFactory;
        $progressBar = $progressBarFactory($count);
        $entityToDocument = new \Solr\Filter\EntityToDocument\JobEntityToSolrDocument($this->options);
        
        // add jobs in the Solr index
        foreach ($jobs as $job) {
            $document = $entityToDocument->filter($job);
            $this->solrClient->addDocument($document);
            $progressBar->update($i, 'Job ' . $i . ' / ' . $count);
            $i++;
        }
        
        $this->solrClient->commit(true, false);
        $this->solrClient->optimize(1, true, false);

        return PHP_EOL;
    }
    
    /**
     * @return callable
     */
    public function getProgressBarFactory()
    {
        return $this->progressBarFactory;
    }
}
