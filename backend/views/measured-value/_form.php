<?php

use app\commands\MainFunctions;
use common\models\Equipment;
use common\models\MeasureType;
use common\models\Operation;
use dosamigos\datetimepicker\DateTimePicker;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model common\models\MeasuredValue */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="measured-value-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-input-documentation',
        'options' => [
            'class' => 'form-horizontal col-lg-11',
            'enctype' => 'multipart/form-data'
        ],
    ]);
    ?>

    <?php
    if (!$model->isNewRecord) {
        echo $form->field($model, 'uuid')->hiddenInput()->label(false);
    } else {
        echo $form->field($model, 'uuid')->hiddenInput(['value' => (new MainFunctions)->GUID()])->label(false);
    }
    ?>

    <?php
    $equipment = Equipment::find()->all();
    $items = ArrayHelper::map($equipment, 'uuid', 'title');
    echo $form->field($model, 'equipmentUuid')->dropDownList($items);

    $operation = Operation::find()->all();
    $items = ArrayHelper::map($operation, 'uuid', 'operationTemplate.title');
    echo $form->field($model, 'operationUuid')->dropDownList($items);

    $measureType = MeasureType::find()->all();
    $items = ArrayHelper::map($measureType, 'uuid', 'title');
    echo $form->field($model, 'measureTypeUuid')->dropDownList($items);
    ?>

    <div class="pole-mg" style="margin: 0 -15px 20px -15px;">
        <p style="width: 200px; margin-bottom: 0;"><?php echo Yii::t('app', 'Дата измерения') ?></p>
        <?= DateTimePicker::widget([
            'model' => $model,
            'attribute' => 'date',
            'language' => Yii::t('app', 'ru'),
            'size' => 'ms',
            'clientOptions' => [
                'autoclose' => true,
                'linkFormat' => 'yyyy-mm-dd H:ii:ss',
                'todayBtn' => true
            ]
        ]);
        ?>
    </div>

    <?= $form->field($model, 'value')->textInput(['maxlength' => true]) ?>

    <div class="form-group text-center">

        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Создать') : Yii::t('app', 'Обновить'), [
            'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
        ]) ?>

    </div>

    <?php ActiveForm::end(); ?>

</div>
