<?php
/* @var $provider
 */
use kartik\grid\GridView;
?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Каналы измерения</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
            </button>
        </div>
    </div>
    <div class="box-body">
        <?php
        $gridColumns = [
            [
                'attribute' => 'title',
                'vAlign' => 'middle',
                'format' => 'raw',
            ],
            [
                'attribute' => 'measureType',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'format' => 'raw',
                'value' => function ($model) {
                    $color = 'background-color: gray';
                    $status = "<span class='badge' style='" . $color . "; height: 12px; margin-top: -3px'> </span>&nbsp;" .
                        $model->measureType->title;
                    return $status;
                },
                'contentOptions' => [
                    'class' => 'table_class'
                ],
            ],
            [
                'header' => 'Значение',
                'format' => 'raw',
                'contentOptions' => [
                    'class' => 'table_class'
                ],
                'value' => function ($model) {
                    return $model->getLastMeasure();
                },
                'headerOptions' => ['class' => 'text-center'],
                'hAlign' => 'center',
                'vAlign' => 'middle',
            ],
        ];

        echo GridView::widget([
            'id' => 'equipment-table',
            'dataProvider' => $provider,
            'columns' => $gridColumns,
            'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
            'headerRowOptions' => ['class' => 'kartik-sheet-style'],
            'filterRowOptions' => ['class' => 'kartik-sheet-style'],
            'pjax' => true,
            'toolbar' => [
                []
            ],
            'showPageSummary' => false,
            'pageSummaryRowOptions' => ['style' => 'line-height: 0; padding: 0'],
            'summary' => '',
            'bordered' => true,
            'striped' => false,
            'condensed' => false,
            'responsive' => true,
            'persistResize' => false,
            'hover' => true,
        ]);
        ?>
    </div>
</div>
