<?php
namespace api\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;
use common\models\Channel;

/**
 */
class EquipmentController extends ActiveController
{
    public $modelClass = 'app\models\Equipment';

    /**
     * Init
     *
     * @throws UnauthorizedHttpException
     * @return void
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

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'set-tag' => ['POST'],
                ],
            ],
        ];
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
     * @return Channel[]|Channel
     */
    public function actionIndex()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // проверяем параметры запроса
        $req = \Yii::$app->request;
        $query = Channel::find()->where(['deleted' => 0]);

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

        $tagId = $req->getQueryParam('tagId');
        if ($tagId != null) {
            $query->andWhere(['=', 'tagId', $tagId]);
            $result = $query->one();
            return $result;
        }

        // проверяем что хоть какие-то условия были заданы
        if ($query->where == null) {
            return [];
        }

        // выбираем данные из базы
        $result = $query->all();
        return $result;
    }

    /**
     * Установка ид метки для единицы оборудования.
     *
     * @return boolean В случае успеха возвращает true
     * @throws \yii\base\InvalidConfigException
     */
    public function actionSetTag()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // проверяем параметры запроса
        $req = \Yii::$app->request;
        $query = Channel::find()->where(['deleted' => 0]);

        // uuid оборудования
        $uuid = $req->getQueryParam('uuid');
        if ($uuid != null) {
            $query->andWhere(['uuid' => $uuid]);
        } else {
            return false;
        }

        // выбираем данные из базы
        $equipment = $query->one();
        if ($equipment == null) {
            return false;
        }

        // ID метки
        $tagId = $req->getQueryParam('tagId');
        if ($tagId == null) {
            return false;
        }

        // проверяем на наличие такой метки в базе
        $testEquipment = Channel::find()->where(['tagId' => $tagId])->one();
        if ($testEquipment != null) {
            return false;
        }

        $equipment->tagId = $tagId;
        if (!$equipment->save()) {
            return false;
        } else {
            return true;
        }
    }
}
