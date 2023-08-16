<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user \rent\entities\User\User */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
?>
<div class="password-reset">
    <p>Добрый день, <?= Html::encode($user->shortName) ?></p>

    <p>Пройдите по следующей ссылке, что бы сбросить пароль:</p>

    <p><?= Html::a(Html::encode($resetLink), $resetLink) ?></p>
</div>
