<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\MeasuredValue */

$this->title = $model->_id;
?>
<div class="box box-default">
    <div class="box-header with-border">
        <h2><?= Html::encode($this->title) ?></h2>
        <div class="box-tools pull-right">
            <span class="label label-default"></span>
        </div>
    </div>
    <div class="box-body" style="padding: 30px;">
        <p>
            <?= Html::a(Yii::t('app', 'Обновить'), ['update', 'id' => $model->_id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a(Yii::t('app', 'Удалить'), ['delete', 'id' => $model->_id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('app', 'Вы действительно хотите удалить данный элемент?'),
                    'method' => 'post',
                ],
            ]) ?>
        </p>

        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                '_id',
                'uuid',
                'equipmentUuid',
                'operationUuid',
                'measureTypeUuid',
                'date',
                'value',
                'createdAt',
                'changedAt',
            ],
        ]) ?>
    </div>
</div>
