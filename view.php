<?php

use kartik\editable\Editable;
use kartik\file\FileInput;
use rent\entities\Support\Task\Task;
use rent\entities\User\User;use rent\forms\support\task\CommentForm;
use rent\forms\support\task\TaskForm;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;
use yii\widgets\ListView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $entity TaskForm */
/* @var $commentForm CommentForm */
/* @var $dataProviderComment ActiveDataProvider */

$this->title = $entity->name;
$this->params['breadcrumbs'][] = ['label' => 'Заявки', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="task-view">
    <p>
        <?if (\Yii::$app->user->can('super_admin')):?>
        <?= Html::a('Удалить', ['delete', 'id' => $entity->_task->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        <?endif;?>
    </p>
    <div class="box box-primary">
        <div class="box-header with-border">Общее</div>
        <div class="box-body">

            <div class="row">
                <div class="col-md-3">
                    <label>Название:</label>
                    <?
                    echo Editable::widget([
                        'model'=>$entity,
                        'attribute' => 'name',
                        'asPopover' => true,
                        'type'=>'success',
                        'format' => Editable::FORMAT_BUTTON,
                        'inputType'=>Editable::INPUT_TEXT,
                        'editableValueOptions'=>['class'=>'']
                    ]);
                    ?>
                </div>
                <div class="col-md-3">
                    <label>Тип:</label>
                    <?
                    echo Editable::widget([
                        'model'=>$entity,
                        'attribute' => 'type',
                        'data'=>Task::getTypeLabels(),
                        'asPopover' => true,
                        'type'=>'success',
                        'format' => Editable::FORMAT_BUTTON,
                        'inputType'=>Editable::INPUT_DROPDOWN_LIST,
                        'editableValueOptions'=>['class'=>''],
//                        'value'=>'123',
                        'displayValue'=>$entity->type?Task::getTypeLabel($entity->type):''
                    ]);
                    ?>
                </div>
                <div class="col-md-3">
                    <label>Приоритет:</label>
                    <?
                    echo Editable::widget([
                        'model'=>$entity,
                        'attribute' => 'priority',
                        'data'=>Task::getPriorityLabels(),
                        'asPopover' => true,
                        'type'=>'success',
                        'format' => Editable::FORMAT_BUTTON,
                        'inputType'=>Editable::INPUT_DROPDOWN_LIST,
                        'editableValueOptions'=>['class'=>''],
                        'displayValue'=>$entity->priority?Task::getPriorityLabel($entity->priority):''
                    ]);
                    ?>
                </div>
                <div class="col-md-3">
                    <?if (\Yii::$app->user->can('super_admin')):?>
                    <label>Клиент:</label>
                    <?
                    echo Editable::widget([
                        'model'=>$entity,
                        'attribute' => 'client_id',
                        'data'=>$entity->getClientsList(),
                        'asPopover' => true,
                        'type'=>'success',
                        'disabled'=>!\Yii::$app->user->can('super_admin'),
                        'format' => Editable::FORMAT_BUTTON,
                        'inputType'=>Editable::INPUT_DROPDOWN_LIST,
                        'editableValueOptions'=>['class'=>''],
                        'displayValue'=>$entity->client_id?$entity->getValue('client_id'):''
                    ]);
                    ?>
                    <?endif;?>
                </div>
                <div class="col-md-12">
                    <label>Описание:</label>
                    <?
                    echo Editable::widget([
                        'model'=>$entity,
                        'attribute' => 'text',
                        'asPopover' => true,
                        'type'=>'success',
                        'format' => Editable::FORMAT_BUTTON,
                        'inputType'=>Editable::INPUT_TEXTAREA,
                        'options' => [
                            'class'=>'form-control',
                            'rows'=>5,
                            'style'=>'width:400px',
                            'placeholder'=>'Enter notes...'
                        ]

                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?if (Yii::$app->user->can('super_admin')):?>
    <div class="box box-warning">
        <div class="box-header with-border">Администрирование</div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <label>Ответственный:</label>
                    <?
                    echo Editable::widget([
                        'model'=>$entity,
                        'attribute' => 'responsible_id',
                        'data'=>User::getResponsibleList(true),
                        'asPopover' => true,
                        'type'=>'success',
                        'disabled'=>!\Yii::$app->user->can('super_admin'),
                        'format' => Editable::FORMAT_BUTTON,
                        'inputType'=>Editable::INPUT_DROPDOWN_LIST,
                        'editableValueOptions'=>['class'=>''],
                        'displayValue'=>$entity->responsible_id?$entity->getValue('responsible_id'):''
                    ]);
                    ?>
                </div>
                <div class="col-md-2">
                    <label>Статус:</label>
                    <?
                    echo Editable::widget([
                        'model'=>$entity,
                        'attribute' => 'status',
                        'data'=>Task::getStatusLabels(),
                        'asPopover' => true,
                        'type'=>'success',
                        'disabled'=>!\Yii::$app->user->can('super_admin'),
                        'format' => Editable::FORMAT_BUTTON,
                        'inputType'=>Editable::INPUT_DROPDOWN_LIST,
                        'editableValueOptions'=>['class'=>''],
                        'displayValue'=>$entity->status?$entity->getValue('status'):''
                    ]);
                    ?>
                </div>
                <div class="col-md-1">
<!--                    <label>Выполнена:</label>-->
<!--                    --><?//
//                    echo Editable::widget([
//                        'model'=>$entity,
//                        'attribute' => 'is_completed',
//                        'asPopover' => true,
//                        'type'=>'success',
//                        'format' => Editable::FORMAT_BUTTON,
//                        'inputType'=>Editable::INPUT_CHECKBOX,
//                        'displayValue'=>$entity->is_completed?'Выполнена':'Не выполнена',
//                        'data' => [0 => 'pass', 1 => 'fail', 2 => 'waived', 3 => 'todo'],
//
//                    ]);
//                    ?>
                </div>
<!--                <div class="col-md-12">-->
<!--                    <label>Причина закрытия задачи без выполнения:</label>-->
<!--                    --><?//
//                    echo Editable::widget([
//                        'model'=>$entity,
//                        'attribute' => 'commentClosed',
//                        'asPopover' => true,
//                        'type'=>'success',
//                        'format' => Editable::FORMAT_BUTTON,
//                        'inputType'=>Editable::INPUT_TEXTAREA,
//                        'options' => [
//                            'class'=>'form-control',
//                            'rows'=>5,
//                            'style'=>'width:400px',
//                            'placeholder'=>'Enter notes...'
//                        ]
//
//                    ]);
//                    ?>
<!--                </div>-->
            </div>

        </div>
    </div>
<?endif;?>
<?php Pjax::begin([
    'id' => 'dashboard_pjax',
    'timeout' => 10000
]); ?>
<div class="task-comments">
    <div class="box box-warning">
        <div class="box-header with-border">Комментарии</div>
        <div class="box-body">
            <?
            $layout="<div class='nav-cat clearfix'>
                            <div class='pull-left'>
                                {pager}
                            </div>
                            {summary}
                         </div>
                         <div class='lst row'>
                            {items}
                         </div>
                         <div class='nav-cat clearfix'>
                            <div class='pull-left'>
                                {pager}
                            </div>
                            {summary}
                         </div>
                        ";
            $layout='
            <div class="direct-chat-messages">
            {items}
            </div>
            ';
            ?>
            <?= ListView::widget([
                'dataProvider' => $dataProviderComment,
                'itemView' => '_comment',
                'layout' => $layout,
//                'summary' => "<div class='pull-left nam-page'>Показано c {begin} по {end} из {totalCount} (всего {pageCount} страниц)</div>",
//                'summaryOptions' => [
//                    'tag' => 'div',
//                    'class' => 'pull-left nam-page',
//                ],
//                'options' => [
//                    'tag' => 'div',
//                    'id' => 'commentList',
//                ],
//                'itemOptions' => [
//                    'tag' => 'div',
//                    'class' => 'product-layout product-grid col-lg-3 col-md-3 col-sm-6 col-xs-12',
//                ],
//                'viewParams' => [
//                    'task'=>$entity->_task,
//                ],
            ]) ?>

            <?php $form = ActiveForm::begin(); ?>
            <?= $form->field($commentForm, 'message')->textarea(['maxlength' => true]) ?>
            <?if (Yii::$app->user->can('super_admin')):?>
                <?= $form->field($commentForm, 'waitingResponse')->checkbox() ?>
            <?endif;?>
            <?= $form->field($commentForm->files, 'files[]')->label(false)->widget(FileInput::class, [
                'options' => [
                    'multiple' => true,
                ],
                'pluginOptions' => [
                    'showPreview' => false,
                    'showCaption' => true,
                    'showRemove' => true,
                    'showUpload' => false
                ]
            ]) ?>
            <div class="form-group">
                <?= Html::submitButton('Отправить', ['class' => 'btn btn-success']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<?php Pjax::end(); ?>
