<?php

namespace api\controllers;

use yii\web\Controller;
class SiteController extends Controller
{
    public function actionError()
    {
        return '404';
    }
}