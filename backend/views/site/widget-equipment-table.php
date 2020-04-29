<?php

use common\models\Device;
use common\models\DeviceStatus;
use common\models\DeviceType;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Таблица работоспособности</h3>
        <div class="box-tools pull-right">
            <div class="btn-group">
                <?php echo Html::a("<button type='button' class='btn btn-box-tool'>
                    <i class='fa fa-link'></i></button>", ['/device']); ?>
            </div>
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
                'class' => 'kartik\grid\DataColumn',
                'attribute' => 'nodeUuid',
                'vAlign' => 'middle',
                'value' => function ($data) {
                    return $data['node']['object']->getAddress() . ' [' . $data['node']['address'] . ']';
                },
                'filterType' => GridView::FILTER_SELECT2,
                'header' => 'Адрес',
                'filterInputOptions' => ['placeholder' => 'Любой'],
                'format' => 'raw',
            ],
            [
                'attribute' => 'deviceTypeUuid',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'value' => 'deviceType.title',
                'filterType' => GridView::FILTER_SELECT2,
                'header' => 'Тип',
                'filter' => ArrayHelper::map(DeviceType::find()->orderBy('title')->all(),
                    'uuid', 'title'),
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filterInputOptions' => ['placeholder' => 'Любой'],
                'format' => 'raw',
                'contentOptions' => [
                    'class' => 'table_class'
                ],
            ],
            [
                'attribute' => 'deviceStatusUuid',
                'header' => 'Статус',
                'format' => 'raw',
                'contentOptions' => [
                    'class' => 'table_class'
                ],
                'headerOptions' => ['class' => 'text-center'],
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'value' => function ($model) {
                    $color = 'background-color: white';
                    if ($model['deviceStatusUuid'] == DeviceStatus::UNKNOWN ||
                        $model['deviceStatusUuid'] == DeviceStatus::NOT_MOUNTED)
                        $color = 'background-color: gray';
                    if ($model['deviceStatusUuid'] == DeviceStatus::NOT_WORK)
                        $color = 'background-color: red';
                    if ($model['deviceStatusUuid'] == DeviceStatus::NOT_LINK)
                        $color = 'background-color: red';
                    if ($model['deviceStatusUuid'] == DeviceStatus::WORK)
                        $color = 'background-color: green';
                    $status = "<span class='badge' style='" . $color . "; height: 12px; margin-top: -3px'> </span>&nbsp;" .
                        $model->deviceStatus->title;
                    return $status;
                },
            ],
        ];

        $devices = Device::find()->where(['OR',
            ['deviceTypeUuid' => DeviceType::DEVICE_LIGHT_WITHOUT_ZB],
            ['deviceTypeUuid' => DeviceType::DEVICE_LIGHT],
            ['deviceTypeUuid' => DeviceType::DEVICE_ELECTRO],
            ['deviceTypeUuid' => DeviceType::DEVICE_COUNTER]]);
        $provider = new ActiveDataProvider(
            [
                'query' => $devices,
                'sort' => false,
            ]
        );

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
            'rowOptions' => function ($model) {
                if ($model['deviceStatusUuid'] != DeviceStatus::WORK)
                    return ['class' => 'danger'];
            }
        ]);
        ?>
    </div>
</div>
