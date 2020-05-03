<?php

namespace backend\controllers;

use backend\models\MeasuredSearchValue;
use common\models\Defect;
use common\models\Channel;
use common\models\EquipmentModel;
use common\models\EquipmentType;
use common\models\MeasuredValue;
use Yii;
use yii\db\StaleObjectException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * MeasuredValueController implements the CRUD actions for MeasuredValue model.
 */
class MeasuredValueController extends Controller
{
    protected $modelClass = MeasuredValue::class;

    /**
     * Lists all MeasuredValue models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MeasuredSearchValue();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 15;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single MeasuredValue model.
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
     * Creates a new MeasuredValue model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new MeasuredValue();
        $searchModel = new MeasuredSearchValue();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 10;
        $dataProvider->setSort(['defaultOrder' => ['_id' => SORT_DESC]]);

        if ($model->load(Yii::$app->request->post())) {
            $measure = MeasuredValue::find()
                ->select('_id')
                ->orderBy('_id DESC')
                ->one();
            if ($measure)
                $measure_id = $measure["_id"] + 1;
            else
                $measure_id = 1;
            $model->_id = $measure_id;
            $model->changedAt = date("Y-m-d H:i:s");
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->_id]);
            }
        }
        return $this->render('create', [
            'model' => $model,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * Displays a trend of value
     * @return mixed
     */
    public function actionTrend()
    {
        $request = Yii::$app->request;
        $equipmentUuid = $request->getQueryParam('equipment');
        $measureTypeUuid = $request->getQueryParam('measure');
        $measuredValues = array(0);
        $name = '';
        if (!empty($measureTypeUuid) && !empty($equipmentUuid)) {
            $measuredValues = MeasuredValue::find()
                ->where(['measureTypeUuid' => $measureTypeUuid])
                ->where(['equipmentUuid' => $equipmentUuid])
                ->orderBy('date')
                ->all();
            if ($measuredValues[0]!=null)
                $name = $measuredValues[0]["measureType"]->title;
        }
        return $this->render('trend', [
            'values' => $measuredValues,
            'name' => $name
        ]);
    }

    /**
     * Updates an existing MeasuredValue model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing MeasuredValue model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the MeasuredValue model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MeasuredValue the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MeasuredValue::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'Запрашиваемая страница не существует.'));
        }
    }

    /**
     * Build equipment tree and measured values of each equipment.
     * If the model is not found, a 404 HTTP exception will be thrown.
     */
    public function actionTree()
    {
        $fullTree= array();
        $equipmentTypes = EquipmentType::find()
            ->select('*')
            ->all();
        $equipmentTypeCount = 0;
        foreach ($equipmentTypes as $equipmentType) {
            $fullTree[$equipmentTypeCount]["title"] = $equipmentType['title'];

            $equipmentModels = EquipmentModel::find()
                ->select('*')
                ->where(['equipmentTypeUuid' => $equipmentType['uuid']])
                ->all();
            $equipmentModelCount = 0;

            foreach ($equipmentModels as $equipmentModel) {
                $fullTree[$equipmentTypeCount]["children"][$equipmentModelCount]["title"] = $equipmentModel['title'];
                $equipments = Channel::find()
                    ->select('*')
                    ->where(['equipmentModelUuid' => $equipmentModel['uuid']])
                    ->all();

                $equipmentCount = 0;
                foreach ($equipments as $equipment) {
                    $fullTree[$equipmentTypeCount]["children"][$equipmentModelCount]["children"][$equipmentCount]["title"] = $equipment['title'];
                    $fullTree[$equipmentTypeCount]["children"][$equipmentModelCount]["children"][$equipmentCount]["location"] = $equipment['location']->title;
                    $measuredValues = MeasuredValue::find()
                        ->select('*')
                        ->where(['equipmentUuid' => $equipment['uuid']])
                        ->groupBy('measureTypeUuid')
                        ->orderBy('createdAt')
                        ->all();
                    $measureCount = 0;
                    foreach ($measuredValues as $measuredValue) {
                        $fullTree[$equipmentTypeCount]["children"][$equipmentModelCount]["children"][$equipmentCount]["children"][$measureCount]["title"] =
                            '<a href="/measured-value/trend.php?equipment='.$measuredValue["equipment"]->uuid.'&measure='.$measuredValue["measureType"]->uuid.'">'.$measuredValue['measureType']->title.'</a>';
                        $fullTree[$equipmentTypeCount]["children"][$equipmentModelCount]["children"][$equipmentCount]["children"][$measureCount]["location"] = "---//--//---";
                        $fullTree[$equipmentTypeCount]["children"][$equipmentModelCount]["children"][$equipmentCount]["children"][$measureCount]["parameter"] = $measuredValue['measureType']->title;
                        $fullTree[$equipmentTypeCount]["children"][$equipmentModelCount]["children"][$equipmentCount]["children"][$measureCount]["value"] = $measuredValue['value'];
                        $fullTree[$equipmentTypeCount]["children"][$equipmentModelCount]["children"][$equipmentCount]["children"][$measureCount]["date"] = $measuredValue['date'];
                        $fullTree[$equipmentTypeCount]["children"][$equipmentModelCount]["children"][$equipmentCount]["children"][$measureCount]["operation"] = $measuredValue['operation']->operationTemplate->title;
                        $measureCount++;
                    }
                    if ($measureCount>0)
                        $equipmentCount++;
                }
                $equipmentModelCount++;
            }
            $equipmentTypeCount++;
        }
        return $this->render('tree', [
            'equipment' => $fullTree
        ]);
    }

}
