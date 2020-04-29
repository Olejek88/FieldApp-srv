<?php

use kartik\select2\Select2;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Equipment */
/* @var $form yii\widgets\ActiveForm */
/* @var $stages */
/* @var $orders */
/* @var $comment */
/* @var $allStageOperations */
/* @var $requestUuid */
/* @var $source */

?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?php echo Yii::t('app', 'Добавить задачу') ?></h4>
</div>
<div class="modal-body">
    <?php $form = ActiveForm::begin([
        'enableAjaxValidation' => false,
        'options' => [
            'id' => 'form5'
        ],
    ]);
    ?>

    <?php
    echo Html::hiddenInput("source", $source);
    echo Html::hiddenInput("equipmentUuid", $model['uuid']);
    echo Html::hiddenInput("comment", $comment);
    echo Html::hiddenInput("requestUuid", $requestUuid);
    // TODO: что это вообще всё делает? (при строгом режиме группировка не срабатывает!)
    $items = ArrayHelper::map($allStageOperations, 'stageTemplateUuid', 'stageTemplate.title');
    echo Select2::widget(
        [
            'id' => 'stageTemplateUuid',
            'name' => 'stageTemplateUuid',
            'language' => Yii::t('app', 'ru'),
            'data' => $items,
            'options' => ['placeholder' => Yii::t('app', 'Выберите задачу ...')],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);
    echo "<br/>";
    $items = ArrayHelper::map($orders, 'uuid', function ($data) {
        return '[' . $data['_id'] . '] [' . $data['startDate'] . '] ' . $data['title'] . ' (' . $data->getUserName() . ')';
    });
    echo Select2::widget([
        'id' => 'orderUuid',
        'name' => 'orderUuid',
        'language' => Yii::t('app', 'ru'),
        'data' => $items,
        'options' => ['placeholder' => Yii::t('app', 'Выберите наряд ...')],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ]);
    echo "<br/>";
    ?>

    <div class="form-group text-center">
        <?= Html::submitButton(Yii::t('app', 'Создать задачу'), [
            'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
        ]) ?>
    </div>

    <div class="form-group text-center">
        <?php GridView::widget([
            'dataProvider' => $stages,
            'tableOptions' => [
                'class' => 'table-striped table table-bordered table-hover table-condensed'
            ],
            'columns' => [
                [
                    'attribute' => '_id',
                    'contentOptions' => [
                        'class' => 'table_class',
                        'style' => 'width: 50px; text-align: center;'
                    ],
                    'headerOptions' => ['class' => 'text-center'],
                    'content' => function ($data) {
                        return $data->_id;
                    }
                ],
                [
                    'contentOptions' => [
                        'class' => 'table_class'
                    ],
                    'header' => Yii::t('app', 'Наряд'),
                    'headerOptions' => ['class' => 'text-center'],
                    'value' => 'task.order.title',
                ],
                [
                    'attribute' => 'stageTemplateUuid',
                    'contentOptions' => [
                        'class' => 'table_class'
                    ],
                    'headerOptions' => ['class' => 'text-center'],
                    'value' => 'stageTemplate.title',
                ],
                [
                    'attribute' => 'stageStatusUuid',
                    'contentOptions' => [
                        'class' => 'table_class'
                    ],
                    'headerOptions' => ['class' => 'text-center'],
                    'value' => 'stageStatus.title',
                ],
            ],
        ]); ?>
    </div>

    <script>
        var send = false;
        $(document).on("beforeSubmit", "#form5", function () {
        }).on('submit', "#form5", function (e) {
            e.preventDefault();
            var form = $('#form5');
            if (!send) {
                send = true;
                $.ajax({
                    url: "../equipment/select-task",
                    type: "post",
                    data: form.serialize(),
                    success: function () {
                        $('#modalAddTask').modal('hide');
                    },
                    error: function () {
                    }
                });
            }
        });
    </script>

    <?php ActiveForm::end(); ?>

</div>
