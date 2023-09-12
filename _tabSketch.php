<?php if (!empty($model->sketch)): ?>
    <h3>Загруженные эскизы:</h3>
    <?php foreach ($model->sketch as $file): ?>
        <?= Html::a($file->name, [$file->url]) ?><br>
    <?php endforeach; ?>
<?php else: ?>
    <p>Нет загруженных эскизов.</p>
<?php endif; ?>
