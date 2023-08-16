<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user \rent\entities\User\User */

$confirmLink = Yii::$app->urlManager->createAbsoluteUrl(['auth/signup/confirm', 'token' => $user->email_confirm_token]);
?>
<div class="password-reset">
    <p>Добрый день <?= Html::encode($user->shortName) ?>,</p>

    <p>Для подтверждения регистрации перейдите по ссылке:</p>

    <p><?= Html::a(Html::encode($confirmLink), $confirmLink) ?></p>
</div>