<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

namespace SolrTest\Options;

use PHPUnit\Framework\TestCase;

use Cross\TestUtils\TestCase\SetupTargetTrait;
use Cross\TestUtils\TestCase\TestSetterAndGetterTrait;
use Solr\Options\ModuleOptions;

/**
 * Class ModuleOptionsTest
 *
 * @author  Anthonius Munthi <me@itstoni.com>
 * @author  Mathias Gelhausen <gelhausen@cross-solution.de>
 * @author  Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since   0.26
 * @covers \Solr\Options\ModuleOptions
 * @package SolrTest\Options
 */
class ModuleOptionsTest extends TestCase
{
    use TestSetterAndGetterTrait, SetupTargetTrait;

    protected $target = [
        'class' => ModuleOptions::class
    ];

    public function setterAndGetterData()
    {
        return [
            ['hostname', [
                'value' => 'some-hostname',
                'default' => 'localhost'
            ]],
            ['port', [
                'default' => 8983,
                'value' => 4568
            ]],
            ['path', [
                'default' => '/solr',
                'value' => '/some-path'
            ]],
            ['username', [
                'default' => '',
                'value' => 'some_username',
            ]],
            ['password', [
                'default' => '',
                'value' => 'some_password'
            ]],
            ['secure',[
                'default' => false,
                'value' => true,
                'getter' => 'isSecure',
            ]],
            ['jobsPath', [
                'default' => '/solr/YawikJobs',
                'value' => '/some/Path',
            ]],
            ['facetFields', [
                'default' => [
                    [
                        'name' => 'region_MultiString',
                        'label' => 'Region',
                    ],
                    [
                        'name' => 'city_MultiString',
                        'label' => 'City'
                    ]
                ],
                'value' => [[
                    'name' => 'test',
                    'label' => 'TEST',
                ]],
            ]],
            ['facetLimit',[
                'default' => 10,
                'value' => 20,
            ]],
            ['facetMinCount',[
                'default' => 1,
                'value' => 10,
            ]],
            ['parameterNames', [
                'default' => [
                    'q' => [
                        'name' => 'q'
                    ],
                    'l' => [
                        'name' => 'l'
                    ],
                    'd' => [
                        'name' => 'd'
                    ],
                    'o' => [
                      'name' => 'o'
                    ]
                ],
                'value' => [
                    'q' => [
                        'name' => 'MyQuery'
                    ],
                    'l' => [
                        'name' => 'MyLocation'
                    ],
                    'd' => [
                        'name' => 'MyDistance'
                    ],
                    'o' => [
                      'name' => 'MyOrganization'
                    ]
                ],
            ]],
            ['mappings', [
                'default' => [
                    'profession' => 'profession_MultiString',
                    'employmentType' => 'employmentType_MultiString',
                ],
                'value' => [
                    'profession' => 'MyProfession',
                    'employmentType' => 'MyEmploymentType',
                ],
            ]],
            ['sorts', [
                'default' => [
                    ['datePublishStart' => \SolrQuery::ORDER_DESC]
                ],
                'value' => [
                    'foo' => 'bar',
                    'hello' => 'world'
                ]
            ]]
        ];
    }
}
