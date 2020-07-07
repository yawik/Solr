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
use Jobs\Entity\StatusInterface;
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

        $limit = $this->params('batch', false);
        if ($limit) {
            $file = getcwd() . '/var/cache/solr-index.dat';
            echo $file; exit;
            $skip = file_exists($file) ? file_get_contents($file) : 0;
            file_put_contents($file, ($skip + $limit));
        }

        $qb = $this->jobRepository->createQueryBuilder()
            ->hydrate(true)
            ->field('status.name')->in([StatusInterface::ACTIVE])
            ->field('isDraft')->equals(false)
            ->readOnly()
        ;
        if ($limit) {
            $qb->limit($limit)->skip($skip);
        }
        $q  = $qb->getQuery();
        $jobs  = $q->execute();

        $count = $jobs->count(true);

        // check if there is any active job
        if (0 === $count) {
            if ($limit) {
                unlink($file);
            }
            return 'There is no active job' . PHP_EOL;
        }

        if ($count > 2500 && !$limit) {
            return 'There are to many active jobs, please use --batch';
        }

        if ($limit) {
            $upper = ($skip + $limit);
            $total = $jobs->count();
            $upper = $upper > $total ? $total : $upper;
            echo "Processing jobs $skip - $upper of $total", PHP_EOL;
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
            if ($i % 1000 === 0) {
                $this->solrClient->commit(true, false);
                $this->solrClient->optimize(1, true, false);
            }
        }

        $this->solrClient->commit(true, false);
        $this->solrClient->optimize(1, true, false);

        $this->jobRepository->getDocumentManager()->clear();
        if ($limit && $count < $limit) {
            echo "No jobs left.";
            unlink($file);
            exit(1);
        }
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
