<?php

/* @var $this yii\web\View */
/* @var $user \rent\entities\User\User */
/* @var $resetLink string */

?>
    Добрый день <?= $user->shortName ?>,

    Для сброса пароля перейдите по ссылке:

<?= $resetLink ?>