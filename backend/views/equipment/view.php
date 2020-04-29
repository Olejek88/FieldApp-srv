<?php

use common\models\Equipment;
use Da\QrCode\QrCode;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $model Equipment */

$this->title = $model->title;
?>
<div class="order-status-view box-padding">

    <div class="panel panel-default">
        <div class="panel-heading" style="background: #fff;">
            <h3 class="text-center" style="color: #333;">
                <?php echo Html::encode($this->title) ?>
            </h3>
        </div>
        <div class="panel-body">
            <div class="user-image-photo">
                <img src="<?php echo Html::encode($model->getImageUrl()) ?>" alt="">
            </div>

            <h1 class="text-center"></h1>

            <div id="myTabContent" class="tab-content">
                <div class="tab-pane fade active in" id="list">
                    <p class="text-center">
                        <?php
                        echo $this->render('@backend/views/yii2-app/layouts/buttons.php',
                            ['model' => $model]);
                        ?>
                    </p>
                    <h6>
                        <?php echo DetailView::widget(
                            [
                                'model' => $model,
                                'attributes' => [
                                    'inventoryNumber',
                                    'serialNumber',
                                    'uuid',
                                    'title',
                                    'tagId',
                                    [
                                        'label' => Yii::t('app', 'Модель'),
                                        'value' => $model['equipmentModel']->title
                                    ],
                                    [
                                        'label' => Yii::t('app', 'Статус'),
                                        'value' => $model['equipmentStatus']->title
                                    ],
                                    [
                                        'label' => Yii::t('app', 'Локация'),
                                        'value' => $model['location']->title
                                    ],
                                    'startDate',
                                    'createdAt',
                                    'changedAt',
                                ],
                            ]
                        ) ?>
                    </h6>
                    <div style="align-content: center">
                        <?php
                        $qrCode = (new QrCode($model['tagId']))
                            ->setSize(200)
                            ->setMargin(5)
                            ->useForegroundColor(0, 0, 0);
                        echo '<img src="' . $qrCode->writeDataUri() . '"/>';
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
