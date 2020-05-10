<?php
namespace api\controllers;

use common\components\MyHelpers;
use common\models\MeasuredValue;
use Yii;
use yii\filters\VerbFilter;
use yii\rest\ActiveController;
use yii\web\NotAcceptableHttpException;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;
use common\models\Channel;

class ChannelController extends ActiveController
{
    public $modelClass = 'app\models\Channel';

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
        $req = Yii::$app->request;
        $query = Channel::find();

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

    /**
     * Метод для загрузки новых каналов
     *
     * @return array
     * @throws NotAcceptableHttpException
     */
    public function actionAddChannel()
    {
        if (Yii::$app->request->isPost) {
            $success = true;
            $saved = array();
            $params = Yii::$app->request->bodyParams;
            foreach ($params as $item) {
                $model = Channel::findOne(['_id' => $item['_id'], 'uuid' => $item['uuid']]);
                if ($model == null) {
                    $model = new Channel();
                }

                $model->attributes = $item;
                $model->setAttribute('_id', $item['_id']);
                $model->setAttribute('title', $item['title']);
                $model->setAttribute('measureTypeUuid', $item['measureType']['uuid']);
                $model->setAttribute(
                    'createdAt',
                    MyHelpers::parseFormatDate($item['createdAt'])
                );
                $model->setAttribute(
                    'changedAt',
                    MyHelpers::parseFormatDate($item['changedAt'])
                );

                if ($model->validate()) {
                    if ($model->save(false)) {
                        $saved[] = [
                            '_id' => $item['_id'],
                            'uuid' => $item['uuid']
                        ];
                    } else {
                        $success = false;
                    }
                } else {
                    $success = false;
                }
            }
            return ['success' => $success, 'data' => $saved];
        } else {
            throw new NotAcceptableHttpException();
        }
    }
}
