<?php
namespace api\controllers;

use common\models\MeasureType;
use Yii;
use yii\base\Controller;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

class MeasureTypeController extends Controller
{
    public $modelClass = 'common\models\MeasureType';

    /**
     * Init
     *
     * @return void
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
     * Displays homepage.
     *
     * @return array
     */
    public function actionIndex()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // проверяем параметры запроса
        $req = Yii::$app->request;
        $query = MeasureType::find();

        $id = $req->getQueryParam('id');
        if ($id != null) {
            $query->andWhere(['_id' => $id]);
        }

        $uuid = $req->getQueryParam('uuid');
        if ($uuid != null) {
            $query->andWhere(['uuid' => $uuid]);
        }

        $changedAfter = $req->getQueryParam('changedAfter');
        if ($changedAfter != null) {
            $query->andWhere(['>=', 'changedAt', $changedAfter]);
        }

        // проверяем что хоть какие-то условия были заданы
        if ($query->where == null) {
            return [];
        }

        // выбираем данные из базы
        $result = $query->all();
        return $result;
    }
}
