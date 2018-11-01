<?php

return [
    'doctrine' =>[
        'connection' =>[
            'odm_default' =>[
                'connectionString' => 'mongodb://172.20.0.1:27017/YAWIK',
            ]
        ],
        'configuration' => [
            'odm_default' => [
                'default_db' => 'YAWIK'
            ]
        ]
    ]
];
