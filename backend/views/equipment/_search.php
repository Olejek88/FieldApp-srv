<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\EquipmentSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="equipment-search box-padding">

    <?php $form = ActiveForm::begin(
        [
            'action' => ['index'],
            'method' => 'get',
        ]
    ); ?>

    <?php echo $form->field($model, '_id') ?>

    <?php echo $form->field($model, 'uuid') ?>

    <?php echo $form->field($model, 'equipmentModelUuid') ?>

    <?php echo $form->field($model, 'title') ?>

    <?php echo $form->field($model, 'criticalTypeUuid') ?>

    <?php // echo $form->field($model, 'startDate') ?>

    <?php // echo $form->field($model, 'latitude') ?>

    <?php // echo $form->field($model, 'longitude') ?>

    <?php // echo $form->field($model, 'tagId') ?>

    <?php // echo $form->field($model, 'image') ?>

    <?php // echo $form->field($model, 'equipmentStatusUuid') ?>

    <?php // echo $form->field($model, 'inventoryNumber') ?>

    <?php // echo $form->field($model, 'location') ?>

    <?php // echo $form->field($model, 'createdAt') ?>

    <?php // echo $form->field($model, 'changedAt') ?>

    <div class="form-group">
        <?php echo Html::submitButton(
            Yii::t('app', 'Поиск'),
            ['class' => 'btn btn-primary']
        ) ?>
        <?php
        echo Html::resetButton(
            Yii::t('app', 'Сбросить'),
            ['class' => 'btn btn-default']
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
