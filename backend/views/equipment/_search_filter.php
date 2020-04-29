<?php

use backend\models\EquipmentSearch;

use kartik\widgets\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

/** @var $this View */
/** @var $searchModel EquipmentSearch */
/** @var $items array */

?>

<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => false,
    'options' => [
        'id' => 'form18'
    ]]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?php echo Yii::t('app', 'Поиск по оборудованию') ?></h4>
</div>
<div class="modal-body">
    <table style="width: 100%">
        <tr>
            <td style="width: 48%; vertical-align: top">
                <?php
                echo Html::hiddenInput("source", $searchModel->source, ['id' => 'source']);
                echo '<label>' . Yii::t('app', 'Оборудование') . '</label></br>';
                echo Select2::widget(
                    ['id' => 'equipment',
                        'name' => 'equipment',
                        'data' => $items,
                        'value' => $searchModel->equipment,
                        'language' => Yii::t('app', 'ru'),
                        'options' => [
                            'placeholder' => Yii::t('app', 'Выберите оборудование..'),
                        ],
                        'pluginOptions' => [
                            'allowClear' => true
                        ]
                    ]);
                echo '<label>' . Yii::t('app', 'или по названию или идентификатору') . '</label></br>';
                echo Html::textInput('title', $searchModel->title, ['id' => 'title', 'name' => 'title']);
                ?>
            </td>
        </tr>
    </table>
</div>
<div class="modal-footer">
    <?php echo Html::submitButton(Yii::t('app', 'Выбрать'), ['class' => 'btn btn-success']) ?>
</div>

<script>
    $(document).on("beforeSubmit", "#form18", function (e) {
        e.preventDefault();
    }).on('submit', "#form18", function (e) {
        e.preventDefault();
        $.ajax({
            type: "post",
            url: "../orders/filter",
            data: {
                equipment: $('#equipment').val(),
                title: $('#title').val(),
                source: $('#source').val()
            },
            success: function () {
                $('#modalFilter').modal('hide');
            }
        })
    });
</script>
<?php ActiveForm::end(); ?>
