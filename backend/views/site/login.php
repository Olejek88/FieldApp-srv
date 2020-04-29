<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Войти');

$fieldOptions1 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-user form-control-feedback'></span>"
];

$fieldOptions2 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-lock form-control-feedback'></span>"
];
?>

<div class="wrapper-block">
    <div class="panel panel-default wrapper-login-panel">
        <div class="panel-body">
            <div class="login-box">
                <div class="login-logo text-center">
                    <h4>
                        <a href="/" style=" color: #333; text-decoration: none;">
                            <b><?php echo Yii::t('app', 'FieldApp') ?></b>
                        </a>
                    </h4>
                </div>

                <div class="login-box-body">
                    <!-- <p class="login-box-msg">Введите учетные данные</p> -->

                    <?php $form = ActiveForm::begin(['id' => 'login-form', 'enableClientValidation' => false]); ?>

                    <?= $form
                        ->field($model, 'username', $fieldOptions1)
                        ->label(Yii::t('app', 'Имя пользователя'))
                        ->textInput(['placeholder' => $model->getAttributeLabel(Yii::t('app', 'Введите имя'))]) ?>

                    <?= $form
                        ->field($model, 'password', $fieldOptions2)
                        ->label(Yii::t('app', 'Пароль'))
                        ->passwordInput(['placeholder' => $model->getAttributeLabel(Yii::t('app', 'Введите пароль'))]) ?>

                    <div class="row">
                        <div class="col-xs-8">
                            <?= $form->field($model, 'rememberMe')
                                ->checkbox(['label' => Yii::t('app', 'Запомнить'),]) ?>
                        </div>

                        <div class="col-xs-4">
                            <?= Html::submitButton(Yii::t('app', 'Вход'), ['class' => 'btn btn-primary btn-block btn-flat', 'name' => 'login-button']) ?>
                        </div>

                    </div>

                    <?php ActiveForm::end(); ?>

                </div>

            </div>
        </div>
    </div>
</div>
