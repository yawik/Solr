<?php

// chdir in config file so tests environment can chdir to this sandbox
chdir(dirname(__DIR__));
return [
    'modules' => [
        'Core',
        'Cv',
        'Auth',
        'Jobs',
        'Applications',
        'Settings',
        'Organizations',
        'Geo',
        'Solr',
    ],
];
