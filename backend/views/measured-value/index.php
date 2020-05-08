<?php

use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\MeasuredSearchValue */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Измеренные значения');
?>
<div class="measured-value-index box-padding-index">

    <div class="box box-default">
        <div class="box-header with-border">
            <h2><?= Html::encode($this->title) ?></h2>
            <div class="box-tools pull-right">
                <span class="label label-default"></span>
            </div>
        </div>
        <div class="box-body" style="padding: 0 10px 0 10px;">
            <p>
                <?= Html::a(Yii::t('app', 'Новое измерение'), ['create'], ['class' => 'btn btn-success']) ?>
            </p>
            <div class="box-body-table">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'tableOptions' => [
                        'class' => 'table-striped table table-bordered table-hover table-condensed'
                    ],
                    'columns' => [
                        [
                            'attribute'=>'_id',
                            'contentOptions' =>[
                                'class' => 'table_class',
                                'style'=>'width: 50px; text-align: center; padding: 18px 10px 15px 10px;'
                            ],
                            'headerOptions' => ['class' => 'text-center'],
                            'content'=>function($data){
                                return $data->_id;
                            }
                        ],
                        [
                            'attribute' => 'channelUuid',
                            'contentOptions' =>[
                                'class' => 'table_class',
                            ],
                            'headerOptions' => ['class' => 'text-center'],
                            'value' => 'channel.title',
                        ],
                        [
                            'attribute'=>'value',
                            'contentOptions' =>[
                                'class' => 'table_class',
                            ],
                            'headerOptions' => ['class' => 'text-center']
                        ],
                        [
                            'attribute'=>'date',
                            'contentOptions' =>[
                                'class' => 'table_class',
                            ],
                            'headerOptions' => ['class' => 'text-center'],
                            'content'=>function($data){
                                return $data->date;
                            }
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'header' => Yii::t('app', 'Действия'),
                            'headerOptions' => ['class' => 'text-center', 'width' => '70'],
                            'contentOptions' => [
                                'class' => 'text-center',
                            ],
                            'template' => '{delete}',
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
