<?php
return [
    'class' => yii\web\UrlManager::class,
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        'terms-conditions' => 'dashboard/terms-conditions',
        // Api
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'api/v1/user',
            'only' => ['index', 'view', 'options']],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'api/v1/product',
            'extraPatterns' => [
                'GET count' => 'count',
                'POST create' => 'create',
                'PUT, PATCH inventory-update' => 'inventory-update',
                'DELETE delete' => 'delete'
            ]
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'api/v1/order',
            'extraPatterns' => [
                'GET count' => 'count',
                'GET printawb' => 'printawb',
                'PUT, PATCH status-update' => 'status-update',
                'DELETE delete' => 'delete'
            ]
        ],
    ],
];
