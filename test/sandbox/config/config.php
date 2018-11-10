<?php

// chdir in config file so tests environment can chdir to this sandbox
chdir(dirname(__DIR__));
return [
    'modules' => \Core\Yawik::generateModuleConfiguration([
        'Core',
        'Cv',
        'Auth',
        'Jobs',
        'Applications',
        'Settings',
        'Organizations',
        'Geo',
        'Solr',
    ]),
    'core_options' => [
        'systemMessageEmail' => 'developer@yawik.org',
    ],
];
