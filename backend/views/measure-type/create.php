<?php
/* @var $model common\models\MeasureType */

use kartik\grid\GridView;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Создать тип измерения');
?>
<div class="order-status-view box-padding" style="width: 95%; min-height: 1224px">
    <?php
    echo $this->render('@backend/views/yii2-app/layouts/references-menu.php');
    ?>
    <div class="panel panel-default" style="float: right; width: 75%">
        <div class="panel-heading" style="background: #fff;">
            <h3 class="text-center" style="color: #333;">
                <?= Html::encode($this->title) ?>
            </h3>
        </div>
        <div class="panel-body">

            <div id="myTabContent" class="tab-content">
                <div class="tab-pane fade active in" id="list">
                    <?= $this->render('_form', [
                        'model' => $model,
                    ]) ?>
                    <div style="min-height: 10px; clear: both;"></div>
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'tableOptions' => [
                            'class' => 'table-striped table table-bordered table-hover table-condensed'
                        ],
                        'columns' => [
                            [
                                'attribute' => '_id',
                                'contentOptions' => [
                                    'class' => 'table_class',
                                    'style' => 'width: 50px'
                                ],
                                'headerOptions' => ['class' => 'text-center'],
                                'content' => function ($data) {
                                    return $data->_id;
                                }
                            ],
                            [
                                'attribute' => 'title',
                                'contentOptions' => [
                                    'class' => 'table_class',
                                ],
                                'headerOptions' => ['class' => 'text-center'],
                                'content' => function ($data) {
                                    return $data->title;
                                }
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => Yii::t('app', 'Действия'),
                                'headerOptions' => ['class' => 'text-center', 'width' => '70'],
                                'contentOptions' => [
                                    'class' => 'text-center',
                                ],
                                'template' => '{view} {update} {delete}{link}',
                            ],
                        ],
                    ]); ?>
                </div>
            </div>

        </div>
    </div>

</div>
