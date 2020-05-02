<?php

namespace backend\controllers;

use backend\models\MeasureSearchType;
use common\components\MainFunctions;
use common\models\MeasureType;
use Throwable;
use Yii;
use yii\db\StaleObjectException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * MeasureTypeController implements the CRUD actions for MeasureType model.
 */
class MeasureTypeController extends Controller
{
    protected $modelClass = MeasureType::class;

    /**
     * Lists all MeasureType models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MeasureSearchType();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 20;
        if (isset($_POST['editableAttribute'])) {
            $model = MeasureType::find()
                ->where(['_id' => $_POST['editableKey']])
                ->one();
            if ($_POST['editableAttribute'] == 'title') {
                $model['title'] = $_POST['MeasureType'][$_POST['editableIndex']]['title'];
            }
            $model->save();
            return json_encode('');
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single MeasureType model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new ModelType model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new MeasureType();
        $searchModel = new MeasureSearchType();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 10;
        $dataProvider->setSort(['defaultOrder' => ['_id' => SORT_DESC]]);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save(false)) {
                return $this->render('index', [
                    'model' => $model,
                    'dataProvider' => $dataProvider
                ]);
            }
        }
        return $this->render('index', [
            'model' => $model,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * Updates an existing MeasureType model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public
    function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save(false)) {
                return $this->redirect('index');
            }
        }
        return $this->redirect('index');
    }

    /**
     * Deletes an existing MeasureType model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws Throwable
     * @throws StaleObjectException
     */
    public
    function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model)
            $model->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds the MeasureType model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MeasureType the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected
    function findModel($id)
    {
        if (($model = MeasureType::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'Запрашиваемая страница не существует.'));
        }
    }

    /**
     * @return int|string
     * @throws \Exception
     */
    public
    function actionNew()
    {
        $measureType = new MeasureType();
        $measureType->uuid = MainFunctions::GUID();
        return $this->renderAjax('../measure-type/_add_form', [
            'type' => $measureType
        ]);
    }

    /**
     * @return bool|string|Response
     */
    public
    function actionSave()
    {
        $model = new MeasureType();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save(false)) {
                return $this->redirect(['/measure-type']);
            } else
                return json_encode($model->errors);
        }
        return $this->render('_add_form', ['model' => $model]);
    }

}
