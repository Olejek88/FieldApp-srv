<?php
/* @var $searchModel backend\models\EquipmentSearch */

/* @var $equipmentModel */

use common\models\Documentation;
use common\models\EquipmentModel;
use common\models\EquipmentStatus;
use common\models\Objects;
use common\models\UserEquipment;
use kartik\editable\Editable;
use kartik\grid\GridView;
use kartik\popover\PopoverX;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$this->title = Yii::t('app', 'ТОИРУС::Оборудование');

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
        'class' => 'kartik\grid\ExpandRowColumn',
        'width' => '50px',
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'header' => '',
        'value' => function () {
            return GridView::ROW_COLLAPSED;
        },
        'detail' => function ($model) {
            return Yii::$app->controller->renderPartial('equipment-details', ['model' => $model]);
        },
        'expandIcon' => '<span class="glyphicon glyphicon-expand"></span>',
        'headerOptions' => ['class' => 'kartik-sheet-style'],
        'expandOneOnly' => true
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
    ],
    [
        'class' => 'kartik\grid\EditableColumn',
        'attribute' => 'equipmentModelUuid',
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'filterType' => GridView::FILTER_SELECT2,
        'filter' => ArrayHelper::map(EquipmentModel::find()->orderBy('title')->all(),
            'uuid', 'title'),
        'filterWidgetOptions' => [
            'pluginOptions' => ['allowClear' => true],
        ],
        'header' => Yii::t('app', 'Модель оборудования') . ' ' . Html::a('<span class="glyphicon glyphicon-plus"></span>',
                '/equipment-model/create?from=equipment/index',
                ['title' => Yii::t('app', 'Добавить')]),
        'filterInputOptions' => ['placeholder' => Yii::t('app', 'Любой')],
        'format' => 'raw',
        'contentOptions' => [
            'class' => 'table_class'
        ],
        'value' => 'equipmentModel.title',
        'editableOptions' => function () {
            $models = ArrayHelper::map(EquipmentModel::find()->orderBy('title')->all(), 'uuid', 'title');
            return [
                'header' => Yii::t('app', 'Модель оборудования'),
                'size' => 'lg',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'displayValueConfig' => $models,
                'data' => $models
            ];
        },
    ],
    [
        'class' => 'kartik\grid\EditableColumn',
        'attribute' => 'inventoryNumber',
        'width' => '100px',
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'contentOptions' => [
            'class' => 'table_class'
        ],
        'headerOptions' => ['class' => 'text-center'],
        'content' => function ($data) {
            return $data->inventoryNumber;
        }
    ],
    [
        'class' => 'kartik\grid\EditableColumn',
        'attribute' => 'serialNumber',
        'width' => '100px',
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'contentOptions' => [
            'class' => 'table_class'
        ],
        'headerOptions' => ['class' => 'text-center'],
        'content' => function ($data) {
            return $data->serialNumber;
        }
    ],
    [
        'class' => 'kartik\grid\EditableColumn',
        'attribute' => 'locationUuid',
        'vAlign' => 'middle',
        'width' => '180px',
        'value' => 'location.title',
        'filterType' => GridView::FILTER_SELECT2,
        'header' => Yii::t('app', 'Местоположение') . ' ' . Html::a('<span class="glyphicon glyphicon-plus"></span>',
                '/objects/create?from=equipment/index',
                ['title' => Yii::t('app', 'Добавить')]),
        'filter' => ArrayHelper::map(Objects::find()->where(['deleted' => 0])->orderBy('title')->all(),
            'uuid', 'title'),
        'filterWidgetOptions' => [
            'pluginOptions' => ['allowClear' => true],
        ],
        'filterInputOptions' => ['placeholder' => Yii::t('app', 'Любой')],
        'format' => 'raw',
        'editableOptions' => function () {
            $models = ArrayHelper::map(Objects::find()->where(['deleted' => 0])->orderBy('title')->all(), 'uuid', 'title');
            return [
                'header' => Yii::t('app', 'Локация'),
                'size' => 'lg',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'placement' => PopoverX::ALIGN_LEFT,
                'displayValueConfig' => $models,
                'data' => $models
            ];
        },
    ],
    [
        'width' => '180px',
        'mergeHeader' => true,
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'content' => function ($data) {
            $userEquipments = UserEquipment::find()
                ->where(['equipmentUuid' => $data['uuid']])
                ->all();
            $count = 0;
            $userEquipmentName = "";
            foreach ($userEquipments as $userEquipment) {
                if ($count > 0) $userEquipmentName .= ', ';
                $userEquipmentName .= $userEquipment['user']['name'];
                $count++;
            }
            if ($count == 0)
                $userEquipmentName = '<div class="progress"><div class="critical5">' .
                    Yii::t('app', 'не назначен') . '</div></div>';
            return $userEquipmentName;
        },
        'header' => Yii::t('app', 'Исполнители'),
        'format' => 'raw',
    ],
    [
        'class' => 'kartik\grid\EditableColumn',
        'attribute' => 'equipmentStatusUuid',
        'header' => Yii::t('app', 'Статус') . ' ' . Html::a('<span class="glyphicon glyphicon-plus"></span>',
                '/equipment-status/create?from=equipment/index',
                ['title' => Yii::t('app', 'Добавить')]),
        'contentOptions' => [
            'class' => 'table_class'
        ],
        'headerOptions' => ['class' => 'text-center'],
        'vAlign' => 'middle',
        'width' => '160px',
        'editableOptions' => function () {
            $status = [];
            $list = [];
            $statuses = EquipmentStatus::getStatusListTranslated();
            foreach ($statuses as $stat) {
                $color = 'background-color: gray';
                if ($stat['uuid'] == EquipmentStatus::UNKNOWN ||
                    $stat['uuid'] == EquipmentStatus::NOT_MOUNTED)
                    $color = 'background-color: gray';
                if ($stat['uuid'] == EquipmentStatus::NEED_CHECK ||
                    $stat['uuid'] == EquipmentStatus::NEED_REPAIR)
                    $color = 'background-color: orange';
                if ($stat['uuid'] == EquipmentStatus::WORK)
                    $color = 'background-color: green';
                if ($stat['uuid'] == EquipmentStatus::NOT_WORK)
                    $color = 'background-color: red';
                $list[$stat['uuid']] = $stat['title'];
                $status[$stat['uuid']] = "<span class='badge' style='" . $color . "; height: 12px; margin-top: -3px'> </span>&nbsp;" .
                    $stat['title'];
            }
            return [
                'header' => Yii::t('app', 'Статус'),
                'size' => 'md',
                'placement' => PopoverX::ALIGN_LEFT,
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'displayValueConfig' => $status,
                'data' => $list
            ];
        },
        'filterType' => GridView::FILTER_SELECT2,
        'filter' => ArrayHelper::map(EquipmentStatus::getStatusListTranslated(),
            'uuid', 'title'),
        'filterWidgetOptions' => [
            'pluginOptions' => ['allowClear' => true],
        ],
        'filterInputOptions' => ['placeholder' => Yii::t('app', 'Любой')],
        'format' => 'raw'
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'headerOptions' => ['class' => 'kartik-sheet-style'],
        'width' => '150px',
        'header' => Yii::t('app', 'Действия'),
        'buttons' => [
            'order' => function ($url, $model) {
                $task = Html::a('<span class="fa fa-plus"></span>',
                    ['/equipment/select-task', 'equipmentUuid' => $model['uuid'], 'source' => 'table'],
                    [
                        'title' => Yii::t('app', 'Создать задачу обслуживания'),
                        'data-toggle' => 'modal',
                        'data-pjax' => '0',
                        'data-target' => '#modalAddTask',
                    ]
                );
                return $task;
            },
            'register' => function ($url, $model) {
                $register = Html::a('<span class="glyphicon glyphicon-calendar"></span>',
                    ['/equipment-register/list', 'equipmentUuid' => $model['uuid']],
                    [
                        'title' => Yii::t('app', 'Журнал событий'),
                        'data-toggle' => 'modal',
                        'data-target' => '#modalRegister',
                    ]
                );
                return $register;
            },
            'measures' => function ($url, $model) {
                $measures = Html::a('<span class="fa fa-bar-chart"></span>',
                    ['/equipment/measures-list', 'equipmentUuid' => $model['uuid'], 'orderUuid' => null],
                    [
                        'title' => Yii::t('app', 'Журнал измерений'),
                        'data-toggle' => 'modal',
                        'data-target' => '#modalMeasures',
                    ]
                );
                return $measures;
            },
            'operation' => function ($url, $model) {
                $operation = Html::a('<span class="glyphicon glyphicon-phone"></span>',
                    ['/equipment/operations', 'equipmentUuid' => $model['uuid']],
                    [
                        'title' => Yii::t('app', 'История работ'),
                        'data-toggle' => 'modal',
                        'data-target' => '#modalTasks',
                    ]
                );
                return $operation;
            },
            'documentation' => function ($url, $model) {
                $documentationCount = Documentation::find()
                    ->where(['equipmentUuid' => $model['uuid']])
                    ->orWhere(['equipmentModelUuid' => $model['equipmentModelUuid']])
                    ->count();
                if ($documentationCount) {
                    return Html::a('<span class="glyphicon glyphicon-floppy-disk"></span>',
                        ['/documentation/documentation', 'equipmentUuid' => $model['uuid']],
                        [
                            'title' => Yii::t('app', 'Документация'),
                            'data-toggle' => 'modal',
                            'data-target' => '#modalDocumentation',
                        ]);
                } else {
                    return '';
                }
            },
            /*            'contragents' => function ($url, $model) {
                            return Html::a('<span class="fa fa-users"></span>',
                                ['/equipment/contragents', 'equipmentUuid' => $model['uuid']],
                                [
                                    'title' => Yii::t('app', 'Подрядчики'),
                                    'data-toggle' => 'modal',
                                    'data-target' => '#modalContragents',
                                ]);
                        },*/
            'edit' => function ($url, $model) {
                return Html::a('<span class="fa fa-edit"></span>',
                    ['/equipment/edit-table', 'uuid' => $model['uuid'], 'reference' => 'table'],
                    [
                        'title' => Yii::t('app', 'Редактировать'),
                        'data-toggle' => 'modal',
                        'data-target' => '#modalAddEquipment',
                    ]);
            },
        ],
        'template' => '{edit} {order} {register} {measures} {operation} {documentation} {contragents} {delete}'
    ]
];

echo GridView::widget([
    'id' => 'equipment-table',
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
            Html::a(Yii::t('app', 'Новое'),
                ['/equipment/new-table', 'reference' => 'table'],
                [
                    'class' => 'btn btn-success',
                    'title' => Yii::t('app', 'Новое'),
                    'data-toggle' => 'modal',
                    'data-target' => '#modalAddEquipment'
                ])
        ],
        '{export}',
    ],
    'export' => [
        'fontAwesome' => true,
        'id' => 'ww',
        'target' => GridView::TARGET_BLANK,
        'filename' => 'equipments'
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
        'heading' => '<i class="glyphicon glyphicon-tags"></i>&nbsp;' . Yii::t('app', 'Оборудование')
    ],
    'rowOptions' => function ($model) {
        $uuid = Yii::$app->request->getQueryParam('uuid');
        if ($uuid) {
            if ($uuid == $model['uuid'])
                return ['class' => 'danger'];
        }
    }
]);

$this->registerJs('$("#modalRegister").on("hidden.bs.modal",
function () {
    $(this).removeData();
})');
$this->registerJs('$("#modalContragents").on("hidden.bs.modal",
function () {
    $(this).removeData();
})');
$this->registerJs('$("#modalDocumentation").on("hidden.bs.modal",
function () {
    $(this).removeData();
})');
$this->registerJs('$("#modalTasks").on("hidden.bs.modal",
function () {
    $(this).removeData();
})');
$this->registerJs('$("#modalAddTask").on("hidden.bs.modal",
function () {
    $(this).removeData();
})');
$this->registerJs('$("#modalAddDefect").on("hidden.bs.modal",
function () {
    $(this).removeData();
})');
$this->registerJs('$("#modalAddEquipment").on("show.bs.modal",
function () {
    var w0 = document.getElementById(\'w0\');
    if (w0) {
      w0.id = \'w1\';
    }
})');
$this->registerJs('$("#modalAddEquipment").on("hidden.bs.modal",
function () {
    $(this).removeData();
})');
?>

<div class="modal remote fade" id="modalDocumentation">
    <div class="modal-dialog" style="width: 800px">
        <div class="modal-content loader-lg" id="modalDocumentationContent">
        </div>
    </div>
</div>
<div class="modal remote fade" id="modalAddTask">
    <div class="modal-dialog" style="width: 800px; height: 400px">
        <div class="modal-content loader-lg" style="margin: 10px; padding: 10px">
        </div>
    </div>
</div>
<div class="modal remote fade" id="modalTasks">
    <div class="modal-dialog" style="width: 1200px">
        <div class="modal-content loader-lg">
        </div>
    </div>
</div>
<div class="modal remote fade" id="modalRegister">
    <div class="modal-dialog" style="width: 800px">
        <div class="modal-content loader-lg" id="modalRegisterContent">
        </div>
    </div>
</div>
<div class="modal remote fade" id="modalContragents">
    <div class="modal-dialog" style="width: 800px">
        <div class="modal-content loader-lg" id="modalContragentsContent">
        </div>
    </div>
</div>
<div class="modal remote fade" id="modalMeasures">
    <div class="modal-dialog" style="width: 800px">
        <div class="modal-content loader-lg" id="modalMeasureContent">
        </div>
    </div>
</div>
<div class="modal remote" id="modalAddEquipment">
    <div class="modal-dialog">
        <div class="modal-content loader-lg" id="modalContentEquipment">
        </div>
    </div>
</div>
<div class="modal remote fade" id="modalAddDefect">
    <div class="modal-dialog" style="width: 700px">
        <div class="modal-content loader-lg" id="modalContentDefect">
        </div>
    </div>
</div>

<!--<style>
    .grid-view td {
        white-space: pre-line;
    }
</style>
-->