<?php
/* @var $channel Channel
 * @var $measureTypes
 */

use common\components\MainFunctions;
use common\models\Channel;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

?>

<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => true,
    'action' => '../channel/save',
    'options' => [
        'id' => 'form2',
        'enctype' => 'multipart/form-data'
    ]]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?php echo Yii::t('app', 'Редактировать канал измерения') ?></h4>
</div>
<div class="modal-body">
    <?php
    if (isset ($channel) && $channel['uuid']) {
        echo $form->field($channel, 'uuid')
            ->hiddenInput(['value' => $channel['uuid']])
            ->label(false);
    } else {
        echo $form->field($channel, 'uuid')
            ->hiddenInput(['value' => MainFunctions::GUID()])
            ->label(false);
    }
    echo $form->field($channel, 'title')->textInput(['maxlength' => true]);
    echo $form->field($channel, 'measureTypeUuid')->widget(Select2::class,
            [
                'data' => $measureTypes,
                'language' => Yii::t('app', 'ru'),
                'options' => [
                    'placeholder' => Yii::t('app', 'Выберите тип измерения..'),
                    'style' => ['height' => '42px', 'padding-top' => '10px']
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]);
    ?>
</div>
<div class="modal-footer">
    <?php echo Html::submitButton(Yii::t('app', 'Отправить'), ['class' => 'btn btn-success']) ?>
    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('app', 'Закрыть') ?></button>
</div>
<script>
    var send = false;
    $(document).on("beforeSubmit", "#form2", function (e) {
        e.preventDefault();
    }).on('submit', "#form2", function (e) {
        e.preventDefault();
        if (!send) {
            send = true;
            var form3 = document.getElementById("form2");
            var fd = new FormData(form3);
            $.ajax({
                type: "post",
                data: fd,
                processData: false,
                contentType: false,
                url: "../channel/save",
                success: function () {
                    $('#modalAddEquipment').modal('hide');
                },
                error: function () {
                }
            });
        }
    });
</script>
<?php ActiveForm::end(); ?>
