<?php

namespace backend\controllers;


use common\models\Users;
use Yii;
use yii\web\NotFoundHttpException;

$accountUser = Yii::$app->user->identity;
if ($accountUser) {
    $currentUser = Users::findOne(['userId' => $accountUser['id']]);
    if ($currentUser == null) {
        /** @noinspection PhpUnhandledExceptionInspection */
        throw new NotFoundHttpException(Yii::t('app', 'Пользователь не найден!'));
    }

    Yii::$app->view->params['currentUser'] = $currentUser;

    $userImage = $currentUser->getImageUrl();
    if (!$userImage)
        $userImage = Yii::$app->request->baseUrl . '/images/unknown2.png';
    $userImage = str_replace("storage", "files", $userImage);
}

