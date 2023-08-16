<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user \rent\entities\User\User */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['client/user/reset/confirm', 'token' => $user->password_reset_token]);
?>
<div class="password-reset">
    <p>Добрый день <?= Html::encode($user->name) ?>,</p>

    <p>Для продолжения вам нужно перейти по ссылки и придумать ваш новый пароль:</p>

    <p><?= Html::a(Html::encode($resetLink), $resetLink) ?></p>
</div>