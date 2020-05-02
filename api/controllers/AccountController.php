<?php
namespace api\controllers;

use common\models\Users;
use Yii;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;
use yii\web\NotFoundHttpException;
use backend\controllers\UsersController;

class AccountController extends ActiveController
{

    /**
     * Init
     *
     * @return void
     *
     * @throws UnauthorizedHttpException
     */
    public function init()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $token = TokenController::getTokenString(Yii::$app->request);
        // проверяем авторизацию пользователя
        if (!TokenController::isTokenValid($token)) {
            throw new UnauthorizedHttpException();
        }
    }

    /**
     * Actions
     *
     * @return array
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        return $actions;
    }

    /**
     * Index
     *
     * @return void
     */
    public function actionIndex()
    {
        $this->redirect('account/me');
    }

    /**
     * Me
     *
     * @return Users
     * @throws NotFoundHttpException
     */
    public function actionMe()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $token = TokenController::getTokenString(Yii::$app->request);
        $user = UsersController::getUserByToken($token);
        if ($user != null) {
            return $user;
        } else {
            throw new NotFoundHttpException();
        }
    }
}
