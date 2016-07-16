<?php
Yii::setAlias('@tests', dirname(__DIR__) . '/tests');
Yii::setAlias('@logs', realpath(dirname(__FILE__)).'/../logs');
Yii::setAlias('@data', realpath(dirname(__FILE__)).'/../data');
Yii::setAlias('@components', realpath(dirname(__FILE__)).'/../components');
Yii::setAlias('@modules/admin', realpath(dirname(__FILE__)).'/../modules/admin');

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'app',
    'defaultRoute' => 'main/default/index',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'defaultRoles' => ['guest'],
        ],
        'urlManager' => [
            'class' => 'yii\web\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'main/default/index',
                'contact' => 'main/contact/index',
                '<_a:error>' => 'main/default/<_a>',
                '<_a:(login|logout|signup|email-confirm|request-password-reset|password-reset)>' => 'user/default/<_a>',
                '<_m:[\w\-]+>/<_c:[\w\-]+>/<_a:[\w\-]+>/<id:\d+>' => '<_m>/<_c>/<_a>',
                '<_m:[\w\-]+>/<_c:[\w\-]+>/<id:\d+>' => '<_m>/<_c>/view',
                '<_m:[\w\-]+>' => '<_m>/default/index',
                '<_m:[\w\-]+>/<_c:[\w\-]+>' => '<_m>/<_c>/index',
                // '<controller>/<action>' => '<controller>/<action>'
            ],

        ],
        'errorHandler' => [
            'errorAction' => 'main/default/error',
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '123',
        ],
        'cache' => [
            'class' => 'yii\caching\MemCache',
        ],
        'user' => [
            'identityClass' => 'app\modules\user\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['user/default/login'],
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            //'_viewPath'=>
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'cache' => [
            'class' => 'yii\caching\DummyCache',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'Parser' => [
            'class' => 'app\components\parser\cornersstats\Parser',
        ],
        'Logger' => [
            'class' => 'app\components\Logger',
        ],
        'Alert' => [
            'class' => 'app\components\widgets\Alert',
        ],

        //loggers for parsing
        'parsing_logger' =>[
            'class'=>'app\components\Logger',
            'log_path' => 'parsing.log'
        ],
        'html_search_logger' =>[
            'class'=>'app\components\Logger',
            'log_path' => 'html_search.log'
        ],
        'db_save_logger' =>[
            'class'=>'app\components\Logger',
            'log_path' => 'db_save.log'
        ],

        'db' => require(__DIR__ . '/db.php'),
    ],
    'modules' => [
        /*'admin' => [
            'class' => 'app\modules\admin\Module',
        ],*/
        'admin' => [
            'class' => 'app\modules\admin\Module',
        ],
        'betting' => [
            'class' => 'app\modules\betting\Module',
        ],
        'main' => [
            'class' => 'app\modules\main\Module',
        ],
        'user' => [
            'class' => 'app\modules\user\Module',
        ],
        'soccer' => [
            'class' => 'app\modules\soccer\Module',
            'controllerNamespace' => 'app\modules\soccer\controllers',
            'viewPath' => '@app/modules/soccer/views',
        ],
        'parser' => [
            'class' => 'app\modules\parser\Parser',
        ],
        'forecast' => [
            'class' => 'app\modules\forecast\Module',
        ],

    ],
    'as access' => [
        'class' => 'app\modules\admin\components\AccessControl',
        'allowActions' => [
            'main/*',
            'user/*',
        ]
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
