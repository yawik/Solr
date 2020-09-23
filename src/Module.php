<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

namespace Solr;

use Core\ModuleManager\Feature\VersionProviderInterface;
use Core\ModuleManager\Feature\VersionProviderTrait;
use Core\ModuleManager\ModuleConfigLoader;
use Laminas\Console\Adapter\AdapterInterface;
use Laminas\ModuleManager\Feature\ConsoleUsageProviderInterface;

/**
 * Class Module
 *
 * @author  Anthonius Munthi <me@itstoni.com>
 * @author  Mathias Gelhausen <gelhausen@cross-solution.de>
 * @since   0.26
 * @package Solr
 */
class Module implements ConsoleUsageProviderInterface, VersionProviderInterface
{
    use VersionProviderTrait;

    const VERSION = '1.1.1';

    public function getConsoleUsage(AdapterInterface $console)
    {
        return [
            'solr index job' => 'Indexing active jobs',
            ['--batch=<int>', 'Indexing jobs in batches of <int> jobs.'],
            ['', 'Each invokation will continue the indexing with the next batch.'],
            ['', 'When the last batch is indexed, it exists with a non-zero exit code.'],
            ['', 'So you can do something like:'],
            ['', 'while true; do [yawik] solr index job --batch 2500 || break; done'],
            ['', ''],
            ['--orgId=<MongoId>', 'Only index the jobs from the specified organization'],
            ['', 'by its id.'],
            ['', ''],
            ['--drop', 'Prior to index jobs, delete all indexed jobs from the solr index.'],
            ['', 'Only works with --orgId'],
        ];
    }

    /**
     * Loads module specific configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return ModuleConfigLoader::load(__DIR__.'/../config');
    }
}
