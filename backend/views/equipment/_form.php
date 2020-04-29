<?php

use common\components\MainFunctions;
use common\models\CriticalType;
use common\models\EquipmentModel;
use common\models\EquipmentStatus;
use common\models\Objects;
use kartik\date\DatePicker;
use kartik\file\FileInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Equipment */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="equipment-form">

    <?php $form = ActiveForm::begin(
        [
            'id' => 'form-input-documentation',
            'options' => [
                'class' => 'form-horizontal col-lg-12 col-sm-12 col-xs-12',
                'enctype' => 'multipart/form-data'
            ],
        ]
    ); ?>

    <?php
    $uuid = (new MainFunctions)->GUID();
    if (!$model->isNewRecord) {
        echo $form->field($model, 'uuid')->hiddenInput()->label(false);
    } else {
        echo $form->field($model, 'uuid')->hiddenInput(['value' => $uuid])->label(false);
    }
    ?>

    <?php

    $equipmentModel = EquipmentModel::find()->all();
    $items = ArrayHelper::map($equipmentModel, 'uuid', 'title');
    echo $form->field($model, 'equipmentModelUuid',
        ['template' => MainFunctions::getAddButton("/equipment-model/create")])->dropDownList($items);
    ?>

    <?php

    $equipmentStatus = EquipmentStatus::find()->all();
    $items = ArrayHelper::map($equipmentStatus, 'uuid', 'title');
    echo $form->field($model, 'equipmentStatusUuid',
        ['template' => MainFunctions::getAddButton("/equipment-status/create")])->dropDownList($items);
    ?>

    <?php
    echo $form->field($model, 'title')->textInput(['maxlength' => true])
    ?>

    <div class="pole-mg" style="margin: 0 -15px 20px -15px">
        <p style="width: 300px; margin-bottom: 0;"><?php echo Yii::t('app', 'Дата ввода в эксплуатацию') ?></p>
        <?php echo DatePicker::widget(
            [
                'model' => $model,
                'attribute' => 'startDate',
                'language' => Yii::t('app', 'ru'),
                'size' => 'ms',
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                ]
            ]
        );
        ?>
    </div>

    <?php

    $criticalType = CriticalType::find()->all();
    $items = ArrayHelper::map($criticalType, 'uuid', 'title');
    echo $form->field($model, 'criticalTypeUuid',
        ['template' => MainFunctions::getAddButton("/critical-type/create")])
        ->dropDownList($items);
    ?>

    <?php
    if (empty($model['tagId'])) {
        $model['tagId'] = $uuid;
    }

    echo $form->field($model, 'tagId')->textInput(['maxlength' => true])
    ?>

    <?php echo $form->field($model, 'image')->widget(
        FileInput::class,
        ['options' => ['accept' => '*'],]
    ); ?>

    <?php
    echo $form->field($model, 'inventoryNumber')->textInput(['maxlength' => true]);
    ?>

    <?php
    echo $form->field($model, 'serialNumber')->textInput(['maxlength' => true]);
    ?>

    <?php

    $objectType = Objects::find()->where(['deleted' => 0])->all();
    $items = ArrayHelper::map($objectType, 'uuid', 'title');
    $countItems = count($items);
    $isItems = $countItems != 0;

    if ($isItems) {
        echo $form->field($model, 'locationUuid',
            ['template' => MainFunctions::getAddButton("/objects/create")])
            ->dropDownList($items);
    } else {
        echo $form->field($model, 'locationUuid')->dropDownList(
            [
                '00000000-0000-0000-0000-000000000004' => Yii::t('app', 'Данных нет'),
            ]
        );
    }

    ?>

    <div class="form-group text-center">

        <?php
        if ($model->isNewRecord) {
            $buttonText = Yii::t('app', 'Создать');
            $buttonClass = 'btn btn-success';
        } else {
            $buttonText = Yii::t('app', 'Обновить');
            $buttonClass = 'btn btn-primary';
        }

        echo Html::submitButton($buttonText, ['class' => $buttonClass]);
        ?>

    </div>

    <?php ActiveForm::end(); ?>

</div>
