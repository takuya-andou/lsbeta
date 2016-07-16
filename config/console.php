<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');
Yii::setAlias('@logs', realpath(dirname(__FILE__)).'/../logs');
Yii::setAlias('@data', realpath(dirname(__FILE__)).'/../data');
Yii::setAlias('@components', realpath(dirname(__FILE__)).'/../components');
Yii::setAlias('@mdm/admin', realpath(dirname(__FILE__)).'/../modules/admin');

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

return [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'gii'],
    'controllerNamespace' => 'app\commands',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        /*'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => [
                'hostname' => 'localhost',
                'port' => 6379,
                'database' => 0,
            ]
        ],*/
        /*'cache' => [
            'class' => 'yii\caching\MemCache',
        ],*/
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'Parser' => [
            'class' => 'app\components\parser\cornersstats\Parser',
        ],
        'Logger' => [
            'class' => 'app\components\Logger',
        ],
        'LinearModel' => [
            'class' => 'app\components\models\LinearModel',
        ],
        'Alert' => [
            'class' => 'app\components\widgets\Alert',
        ],
        'db' => require(__DIR__ . '/db.php'),
    ],
    'modules' => [
        'main' => [
            'class' => 'app\modules\main\Module',
        ],
        'user' => [
            'class' => 'app\modules\user\Module',
        ],
        'soccer' => [
            'class' => 'app\modules\soccer\Module',
        ],
        'gii' => 'yii\gii\Module',
    ],
    'params' => $params,
];
