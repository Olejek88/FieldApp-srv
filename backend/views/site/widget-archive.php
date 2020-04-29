<?php

use common\models\Measure;
use common\models\MeasureType;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;

/* @var $node
 */
?>

<div class="box box-primary">
    <!-- /.box-header -->
    <div class="box-body">
        <?php
        $gridColumns = [
            [
                'attribute' => 'date',
                'contentOptions' => [
                    'class' => 'table_class'
                ],
                'headerOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'value',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'headerOptions' => ['class' => 'kv-sticky-column'],
                'contentOptions' => ['class' => 'kv-sticky-column'],
            ],
            [
                'attribute' => 'sensorChannel.title',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'contentOptions' => [
                    'class' => 'table_class'
                ],
                'headerOptions' => ['class' => 'text-center'],
            ],
            [
                'hAlign' => 'center',
                'header' => 'Тип измерения',
                'vAlign' => 'middle',
                'value' => function ($data) {
                    if ($data['type'] == MeasureType::MEASURE_TYPE_CURRENT)
                        return 'Текущее значение';
                    if ($data['type'] == MeasureType::MEASURE_TYPE_HOUSE)
                        return 'Часовое значение';
                    if ($data['type'] == MeasureType::MEASURE_TYPE_DAYS)
                        return 'Дневное значение';
                    if ($data['type'] == MeasureType::MEASURE_TYPE_MONTH)
                        return 'Архив за месяц';
                    if ($data['type'] == MeasureType::MEASURE_TYPE_TOTAL)
                        return 'Накопительное значение';
                    if ($data['type'] == MeasureType::MEASURE_TYPE_INTERVAL)
                        return 'Интервальное значение';
                    if ($data['type'] == MeasureType::MEASURE_TYPE_TOTAL_CURRENT)
                        return 'Текущее накопительное';
                },
                'contentOptions' => [
                    'class' => 'table_class'
                ],
                'headerOptions' => ['class' => 'text-center'],
            ]
        ];

        $measures = Measure::find()->orderBy('date DESC')->limit(8);
        $provider = new ActiveDataProvider(
            [
                'query' => $measures,
                'sort' =>false,
            ]
        );
        $provider->pagination->pageSize = 8;

        echo GridView::widget(
            [
                'dataProvider' => $provider,
                'columns' => $gridColumns,
                'headerRowOptions' => ['class' => 'kartik-sheet-style'],
                'filterRowOptions' => ['class' => 'kartik-sheet-style'],
                'containerOptions' => ['style' => 'overflow: auto'],
                'beforeHeader' => [
                    '{toggleData}'
                ],
                'toolbar' => [
                    []
                ],
                'pjax' => true,
                'showPageSummary' => false,
                'pageSummaryRowOptions' => ['style' => 'line-height: 0; padding: 0'],
                'summary' => '',
                'bordered' => true,
                'striped' => false,
                'condensed' => true,
                'responsive' => false,
                'hover' => true,
                'floatHeader' => false,
                'panel' => [
                    'type' => GridView::TYPE_PRIMARY,
                    'heading' => '<i class="glyphicon glyphicons-spade"></i>&nbsp; Последние измерения',
                    'headingOptions' => ['style' => 'background: #337ab7']
                ],
            ]
        );
        ?>
    </div>
</div>
<!-- /.box -->
