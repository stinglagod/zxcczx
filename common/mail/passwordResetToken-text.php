<?php

/* @var $this yii\web\View */
/* @var $user \rent\entities\User\User */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
?>
Добрый день, <?= $user->shortName ?>

Пройдите по следующей ссылке, что бы сбросить пароль:

<?= $resetLink ?>
