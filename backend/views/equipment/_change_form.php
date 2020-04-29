<?php

use common\models\EquipmentStatus;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Equipment */
/* @var $equipmentStatuses */
/* @var $source */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="equipment-status-form">

    <?php $form = ActiveForm::begin([
        'enableAjaxValidation' => false,
        'options' => [
            'id' => 'form3'
        ],
    ]);
    ?>

    <?php
        echo $form->field($model, '_id')->hiddenInput(['value' => $model["_id"]])->label(false);
    echo Html::hiddenInput("reference", $source);

    $items = ArrayHelper::map(EquipmentStatus::getStatusListTranslated(), 'uuid', 'title');
        echo $form->field($model, 'equipmentStatusUuid')->widget(Select2::class,
        [
            'name' => 'status',
            'language' => Yii::t('app', 'ru'),
            'value' => $model["equipmentStatus"]["title"],
            'data' => $items,
            'options' => ['placeholder' => Yii::t('app', 'Выберите статус ...')],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ])->label(false);
    ?>

    <div class="form-group text-center">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Сменить') : Yii::t('app', 'Сменить'), [
            'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
        ]) ?>
    </div>
    <script>
        var send = false;
        $(document).on("beforeSubmit", "#form3", function () {
        }).on('submit', "#form3", function (e) {
            e.preventDefault();
            if (!send) {
                send = true;
                $.ajax({
                    url: "../equipment/status",
                    type: "post",
                    data: $('#form3').serialize(),
                    success: function () {
                        $('#modalStatus').modal('hide');
                    },
                    error: function () {
                    }
                });
            }
        });
    </script>

    <?php ActiveForm::end(); ?>

</div>
