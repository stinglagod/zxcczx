<?php

namespace api\controllers;

use yii\web\Controller;
class TestController extends Controller
{
    public function actionIndex()
    {
        echo 'action test/index';
        \Yii::warning('test');
//        \Yii::$app->log->logger->log('test',Logger::LEVEL_INFO);
//        TagDependency::invalidate(Yii::$app->cache, ['products']);
    }
}