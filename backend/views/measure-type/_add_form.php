<?php
/* @var $type MeasureType
 */

use common\components\MainFunctions;
use common\models\MeasureType;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

?>

<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => true,
    'action' => '../measure-type/save',
    'options' => [
        'id' => 'form2'
    ]]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?php echo Yii::t('app', 'Редактировать тип измерения') ?></h4>
</div>
<div class="modal-body">
    <?php
    if (isset ($type) && $type['uuid']) {
        echo $form->field($type, 'uuid')
            ->hiddenInput(['value' => $type['uuid']])
            ->label(false);
    } else {
        echo $form->field($type, 'uuid')
            ->hiddenInput(['value' => MainFunctions::GUID()])
            ->label(false);
    }
    echo $form->field($type, 'title')->textInput(['maxlength' => true]);
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
            $.ajax({
                url: "save",
                type: "post",
                data: $('#form2').serialize(),
                success: function (code) {
                    let message = JSON.parse(code);
                    if (message.code === 0) {
                        $('#modalAddType').modal('hide');
                    } else {
                        alert(message.message);
                    }
                },
                error: function () {
                }
            });
        }
    });
</script>
<?php ActiveForm::end(); ?>
