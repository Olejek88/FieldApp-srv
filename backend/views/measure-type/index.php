<?php
/* @var $searchModel backend\models\MeasureSearchType */

use kartik\grid\GridView;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Типы измерений');

$gridColumns = [
    [
        'attribute' => '_id',
        'hAlign' => 'center',
        'vAlign' => 'middle',
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
        'class' => 'kartik\grid\EditableColumn',
        'attribute' => 'title',
        'vAlign' => 'middle',
        'contentOptions' => [
            'class' => 'table_class'
        ],
        'headerOptions' => ['class' => 'text-center'],
        'editableOptions' => [
            'size' => 'lg',
        ],
        'content' => function ($data) {
            return $data->title;
        }
    ]
];

echo GridView::widget([
    'id' => 'table',
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $gridColumns,
    'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
    'headerRowOptions' => ['class' => 'kartik-sheet-style'],
    'filterRowOptions' => ['class' => 'kartik-sheet-style'],
    'beforeHeader' => [
        '{toggleData}'
    ],
    'toolbar' => [
        ['content' =>
            Html::a(Yii::t('app', 'Новый'),
                ['/measure-type/new'],
                [
                    'class' => 'btn btn-success',
                    'title' => Yii::t('app', 'Новый'),
                    'data-toggle' => 'modal',
                    'data-target' => '#modalAddType'
                ])
        ]
    ],
    'pjax' => true,
    'showPageSummary' => false,
    'pageSummaryRowOptions' => ['style' => 'line-height: 0; padding: 0'],
    'summary' => '',
    'bordered' => true,
    'striped' => false,
    'condensed' => false,
    'responsive' => true,
    'persistResize' => false,
    'hover' => true,
    'panel' => [
        'type' => GridView::TYPE_PRIMARY,
        'heading' => '<i class="glyphicon glyphicon-tags"></i>&nbsp;' . Yii::t('app', 'Типы измерений')
    ],
]);

$this->registerJs('$("#modalAddType").on("hidden.bs.modal",
function () {
    $(this).removeData();
})');
?>

<div class="modal remote" id="modalAddType">
    <div class="modal-dialog">
        <div class="modal-content loader-lg" id="modalContentType">
        </div>
    </div>
</div>
