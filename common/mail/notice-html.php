<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user \rent\entities\User\User */
/* @var $text string */

?>
<div class="password-reset">
    <p>Добрый день, <?= Html::encode($user->shortName) ?></p>

    <p>Новые уведомления в личном кабинете:</p>

    <p><?= $text ?></p>
</div>
