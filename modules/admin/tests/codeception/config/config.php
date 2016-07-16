<?php
/**
 * Application configuration shared by all test types
 */
return [
    'id' => 'modules-admin-test',
    'basePath' => dirname(dirname(__DIR__)), // @tests
    'vendorPath' => dirname(dirname(dirname(__DIR__))) . '/vendor',
    'language' => 'en-US',
    'aliases' => [
        'app/modules/admin' => dirname(dirname(dirname(__DIR__))),
    ],
    'modules' => [
        'admin' => [
            'class' => 'app\modules\admin\Module',
        ]
    ],
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\console\controllers\FixtureController',
            'namespace' => 'tests\codeception\fixtures',
        ],
    ],
    'components' => [
        'db' => require(__DIR__ . '/db.php'),
        'mailer' => [
            'useFileTransport' => true,
        ],
        'urlManager' => [
            'showScriptName' => true,
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager'
        ],
        'cache' => [
            'class' => 'yii\caching\DummyCache',
        ],
        'i18n' => [
            'translations' => [
                'rbac-admin' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'en',
                    'basePath' => '@modules/admin/messages'
                ]
            ]
        ]
    ],
];
