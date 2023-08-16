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
    'apiHostInfo' => 'http://api.rent4b.ru',

    'clientId'=>1,
    'siteDomain'=> 'rent4b.ru',
    'siteId'=> 1,

    'mainClientId'=>1,
    'mainSiteId'=>1,
    'mainSiteDomain'=>'rent4b.ru',

    //Settings
    'settingsCacheDuration'=>300,               //Время кеша для общих настроек (Пользватель, клиент, сайта)

    'numbUsersOfClient'=>10,         //кол-во пользователей у клиента
    'numbSitesOfClient'=>5,         //кол-во сайтов у одного клиента

    //TelegramBot
    'telegram_botApiKey'=>'6232788955:AAE6OX0D3qJ2TSoOOuGFb1t8n6mhzH_WFkk',
    'telegram_username'=>'Rent4b_bot',
    'telegram_hookUrl'=>'https://api.rent4b.ru/hooks/telegram/handle',

];
