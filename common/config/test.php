<?php
$params = array_merge(
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php',
    require __DIR__ . '/params-test.php',
);
return [
    'id' => 'app-common-tests2',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
        'log',
        'common\bootstrap\SetUp',
    ],
    'aliases' => [
        '@staticRoot' => $params['staticPath'],
        '@static'   => $params['staticHostInfo'],
    ],
    'components' => [
        'settings'=>[
            'class' => '\rent\settings\Settings',
            'useSaveToSessionCache'=>false,
        ],
        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'rent\entities\User\User',
        ],
        'cache' => [
            'class' => 'yii\caching\MemCache',
            'useMemcached' => true,
            'servers' => [
                [
                      'host' => '192.168.83.138',
                      'port' => 11211,
                      'weight' => 60,
                ],
            ]
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],

    ],
    'params' => $params,
];
