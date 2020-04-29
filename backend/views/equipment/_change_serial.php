<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Equipment */
/* @var $form yii\widgets\ActiveForm */
/* @var $source */
?>

<div class="equipment-status-form">

    <?php $form = ActiveForm::begin([
        'enableAjaxValidation' => false,
        'options' => [
            'id' => 'form4'
        ],
    ]);
    ?>

    <?php
    echo $form->field($model, '_id')->hiddenInput(['value' => $model["_id"]])->label(false);
    echo Html::hiddenInput("reference", $source);
    echo $form->field($model, 'serialNumber')->textInput(['maxlength' => true, 'value' => $model['serialNumber']]);

    ?>

    <div class="form-group text-center">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Сменить') : Yii::t('app', 'Сменить'), [
            'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
        ]) ?>
    </div>
    <script>
        $(document).on("beforeSubmit", "#form4", function (e) {
            e.preventDefault();
        }).on('submit', "#form4", function (e) {
            e.preventDefault();
            $.ajax({
                url: "../equipment/serial",
                type: "post",
                data: $('#form4').serialize(),
                success: function () {
                    $('#modalSN').modal('hide');
                },
                error: function () {
                }
            })
        });
    </script>

    <?php ActiveForm::end(); ?>

</div>
