<?php

/* @var $this yii\web\View */
/* @var $user \rent\entities\User\User */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['client/user/reset/confirm', 'token' => $user->password_reset_token]);
?>
    Добрый день <?= $user->name ?>,

    Для продолжения вам нужно перейти по ссылки и придумать ваш новый пароль:

<?= $resetLink ?>