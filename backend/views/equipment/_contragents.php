<?php

use common\models\Contragent;
use common\models\EquipmentContragent;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $equipmentUuid string */
?>

<div class="contragents-form">

    <?php $form = ActiveForm::begin([
        'enableAjaxValidation' => false,
        'action' => '../equipment/set-contragent',
        'options' => [
            'id' => 'form22'
        ],
    ]);
    ?>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><?php echo Yii::t('app', 'Редактировать подрядчиков') ?></h4>
    </div>
    <div class="modal-body">

        <?php
        echo Html::hiddenInput("equipmentUuid", $equipmentUuid);
        $contragents = EquipmentContragent::find()
            ->where(['equipmentUuid' => $equipmentUuid])
            ->all();
        echo '<label class="control-label">' . Yii::t('app', 'Убрать подрядчика') . '</label>';
        echo '<br/>';
        foreach ($contragents as $equipmentContragent) {
            echo Html::checkbox('contragent-' . $equipmentContragent->_id, false,
                ['label' => $equipmentContragent->contragent->name]);
            echo '<br/>';
        }
        echo '<br/>';

        echo '<label class="control-label">' . Yii::t('app', 'Добавить подрядчика') . '</label>';
        echo '<br/>';
        $contragents = Contragent::find()->orderBy('name')->all();
        $items = ArrayHelper::map($contragents, 'uuid', 'name');
        try {
            echo Select2::widget(
                ['id' => 'contragentUuid',
                    'name' => 'contragentUuid',
                    'language' => Yii::t('app', 'ru'),
                    'data' => $items,
                    'options' => ['placeholder' => Yii::t('app', 'Выберите контрагента ...')],
                    'pluginOptions' => [
                        'allowClear' => true
                    ]
                ]);
        } catch (Exception $e) {
        }
        echo '<br/>';
        echo '<label id="error" style="color: red"></label>';
        echo '<br/>';
        ?>

        <div class="form-group text-center">
            <?= Html::submitButton(Yii::t('app', 'Изменить'), ['class' => 'btn btn-success']) ?>
        </div>
        <script>
            var send = false;
            $(document).on("beforeSubmit", "#form22", function (e) {
                e.preventDefault();
            }).on('submit', "#form22", function (e) {
                e.preventDefault();
                if (!send) {
                    $.ajax({
                        url: "../equipment/set-contragent",
                        type: "post",
                        data: $('#form22').serialize(),
                        success: function (code) {
                            var message = JSON.parse(code);
                            if (message.code === 0) {
                                send = true;
                                $('#modalContragents').modal('hide');
                            } else {
                                var div = document.getElementById('error');
                                div.innerHTML = message.message;
                            }
                            //$('#modalContragents').removeData();
                        },
                        error: function () {
                        }
                    });
                }
            });
        </script>

    </div>
    <?php ActiveForm::end(); ?>
</div>
