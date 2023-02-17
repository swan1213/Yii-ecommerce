<?php

$config_domain = env('GLOBAL_DOMAIN');

$config = [
    'homeUrl' => Yii::getAlias('@frontendUrl'),
    'controllerNamespace' => 'frontend\controllers',
    'defaultRoute' => 'dashboard/index',
    'bootstrap' => ['maintenance'],
    'modules' => [
        'user' => [
            'class' => 'frontend\modules\user\Module',
            'shouldBeActivated' => false
        ],
        'api' => [
            'class' => 'frontend\modules\api\Module',
            'modules' => [
                'v1' => 'frontend\modules\api\v1\Module'
            ]
        ],
        'hooklistener' => [
            'class' => 'frontend\modules\hooklistener\Module',
        ],
        'feed' => [
            'class' => 'frontend\modules\feed\Module',
        ]
    ],
    'components' => [
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                'github' => [
                    'class' => 'yii\authclient\clients\GitHub',
                    'clientId' => env('GITHUB_CLIENT_ID'),
                    'clientSecret' => env('GITHUB_CLIENT_SECRET')
                ],
                'facebook' => [
                    'class' => 'yii\authclient\clients\Facebook',
                    'clientId' => env('FACEBOOK_CLIENT_ID'),
                    'clientSecret' => env('FACEBOOK_CLIENT_SECRET'),
                    'scope' => 'email,public_profile',
                    'attributeNames' => [
                        'name',
                        'email',
                        'first_name',
                        'last_name',
                    ]
                ]
            ]
        ],
        'errorHandler' => [
            'errorAction' => 'site/error'
        ],
        'maintenance' => [
            'class' => 'common\components\maintenance\Maintenance',
            'enabled' => function ($app) {
                return $app->keyStorage->get('frontend.maintenance') === 'enabled';
            }
        ],
        'session' => [
            'name'=> 'multi-app',
            'cookieParams' => [
                'path' => '/',
                'domain' => $config_domain,
                'httpOnly' => true,
            ],
            'class' => 'yii\web\DbSession',
            'sessionTable' => 'app_session',
        ],
        'request' => [
            'cookieValidationKey' => env('FRONTEND_COOKIE_VALIDATION_KEY'),
		    'baseUrl' => env('FRONTEND_BASE_URL'),
            'csrfCookie' => [
                'name' => '_csrf',
                'path' => '/',
                'domain' => $config_domain,
                'httpOnly' => true,
            ],
        ],
        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'common\models\User',
            'loginUrl' => ['/user/sign-in/login'],
            'enableAutoLogin' => env('ENABLE_AUTO_LOGIN'),
            'authTimeout' => env('AUTH_TIME_OUT'),
            'as afterLogin' => common\behaviors\LoginTimestampBehavior::class,
            'identityCookie' => [
                'name' => '_identity-frontend',
                'path' => '/',
                'domain' => $config_domain,
                'httpOnly' => true
            ],
        ]
    ],
    'as globalAccess' => [
        'class' => common\behaviors\GlobalAccessBehavior::class,
        'rules' => [
            [
                'controllers' => ['feed/facebook'],
                'allow' => true,
                'roles' => ['?'],
            ],
            [
                'controllers' => ['hooklistener/shopify', 'hooklistener/bigcommerce', 'hooklistener/woocommerce'],
                'allow' => true,
                'roles' => ['?'],
            ],
            [
                'controllers' => ['user/sign-in'],
                'allow' => true,
                'roles' => ['?'],
                'actions' => ['login', 'signup']
            ],
            [
                'controllers' => ['user/sign-in'],
                'allow' => true,
                'roles' => ['@'],
                'actions' => ['logout']
            ],
            [
                'controllers' => ['dashboard'],
                'allow' => true,
                'roles' => ['@'],
            ],
            [
                'controllers' => ['site'],
                'allow' => true,
                'roles' => ['?', '@'],
                'actions' => ['error']
            ],
            [
                'controllers' => ['debug/default'],
                'allow' => true,
                'roles' => ['?', '@'],
            ],
            [
                'allow' => true,
                'roles' => ['user', 'manager', 'administrator'],
            ]
        ]
    ]
];

if (YII_ENV_DEV) {
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'generators' => [
            'crud' => [
                'class' => 'yii\gii\generators\crud\Generator',
                'messageCategory' => 'frontend'
            ]
        ],
    ];
}

return $config;
