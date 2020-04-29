<?php

namespace backend\controllers;

use backend\models\ChannelSearch;
use common\models\Channel;
use common\models\Users;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * InstructionController implements the CRUD actions for Channel model.
 */
class ChannelController extends Controller
{
    protected $modelClass = Channel::class;

    /**
     * Lists all Instruction models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ChannelSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->setSort(['defaultOrder' => ['_id' => SORT_DESC]]);
        $dataProvider->pagination->pageSize = 25;
        if (isset($_POST['editableAttribute'])) {
            $model = Channel::find()
                ->where(['_id' => $_POST['editableKey']])
                ->one();
            if ($_POST['editableAttribute'] == 'title') {
                $model['title'] = $_POST['Channel'][$_POST['editableIndex']]['title'];
            }
            $model->save();
            return json_encode('');
        }
        return $this->render(
            'index',
            [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]
        );
    }

    /**
     * Deletes an existing Instruction model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id Id
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $channel = $this->findModel($id);
        $channel->save();
        return $this->redirect(['index']);
    }

    /**
     * Finds the Channel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id Id
     *
     * @return Channel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Channel::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'Запрашиваемая страница не существует.'));
        }
    }

    /**
     * @return int|string
     */
    public function actionNew()
    {
        $channel = new Channel();
        return $this->renderAjax('../instruction/_add_form', [
            'channel' => $channel
        ]);
    }

    /**
     * @return bool|string|Response
     */
    public function actionSave()
    {
        $model = new Channel();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save(false)) {
                return $this->redirect(['/channel']);
            } else
                return json_encode($model->errors);
        }
        return $this->render('_add_form', ['model' => $model]);
    }

}
