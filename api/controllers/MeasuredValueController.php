<?php
/**
 * PHP Version 7.0
 *
 * @category Category
 * @package  Api\controllers
 * @author   Дмитрий Логачев <demonwork@yandex.ru>
 * @license  http://www.yiiframework.com/license/ License name
 * @link     http://www.toirus.ru
 */

namespace api\controllers;

use common\components\MyHelpers;
use common\models\MeasuredValue;
use Yii;
use yii\base\Controller;
use yii\web\NotAcceptableHttpException;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

/**
 * Class DocumentationController
 *
 * @category Category
 * @package  Api\controllers
 * @author   Дмитрий Логачев <demonwork@yandex.ru>
 * @license  http://www.yiiframework.com/license/ License name
 * @link     http://www.toirus.ru
 */
class MeasuredValueController extends Controller
{
    public $modelClass = 'common\models\MeasuredValue';

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
        $query = MeasuredValue::find();

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
     * Метод для загрузки/сохранения измеренных значений при выполнении операций.
     *
     * @return string
     * @throws NotAcceptableHttpException
     */
    public function actionUploadMeasuredValue()
    {
        if (Yii::$app->request->isPost) {
            $success = true;
            $saved = array();
            $params = Yii::$app->request->bodyParams;
            foreach ($params as $item) {
                $model = MeasuredValue::findOne(['_id' => $item['_id'], 'uuid' => $item['uuid']]);
                if ($model == null) {
                    $model = new MeasuredValue();
                }

                $model->attributes = $item;
                $model->setAttribute('_id', $item['_id']);
                $model->setAttribute('equipmentUuid', $item['equipment']['uuid']);
                $model->setAttribute('operationUuid', $item['operation']['uuid']);
                $model->setAttribute(
                    'measureTypeUuid', $item['measureType']['uuid']
                );
                $model->setAttribute(
                    'date',
                    MyHelpers::parseFormatDate($item['date'])
                );
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
