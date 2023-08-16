<?php
return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'user.passwordResetTokenExpire' => 3600,
    'image_filter'=>true,
    'user.rememberMeDuration' => 3600 * 24 * 30,
    'cookieDomain' => '.example.com',
    'frontendHostInfo' => 'http://example.com',
    'backendHostInfo' => 'http://backend.example.com',
    'staticHostInfo' => 'http://static.example.com',
    'staticPath' => dirname(__DIR__, 2) . '/static',

    'clientId'=>1001,
    'siteDomain'=> 'rent4b.test',
    'siteId'=> 1001,

    'mainClientId'=>1001,
    'mainSiteId'=>1001,
    'mainSiteDomain'=>'rent4b.test',

    'numbUsersOfClient'=>5,         //кол-во пользователей у клиента
    'numbSitesOfClient'=>5,         //кол-во сайтов у одного клиента
];
