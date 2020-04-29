<?php

namespace backend\controllers;

use backend\models\MeasureSearchType;
use common\models\MeasureType;
use Throwable;
use Yii;
use yii\db\StaleObjectException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * MeasureTypeController implements the CRUD actions for MeasureType model.
 */
class MeasureTypeController extends ToirusController
{
    protected $modelClass = MeasureType::class;

    /**
     * Lists all MeasureType models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel  = new MeasureSearchType();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 20;

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
        $searchModel  = new MeasureSearchType();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 10;
        $dataProvider->setSort(['defaultOrder' => ['_id' => SORT_DESC]]);

        if ($model->load(Yii::$app->request->post())) {
            $file = UploadedFile::getInstance($model, 'icon');
            if ($file && $file->tempName) {
                $model->icon = $file;

                if ($model->upload()) {
                    $uuidFile = $model->uuid;
                    $dbName = Yii::$app->session->get('user.dbname');
                    $imageFile = 'storage/' . $dbName . '/' . $uuidFile . '/';
                    $fileName = $model->uuid . '.' . $model->icon->extension;

                    if (!is_dir($imageFile)) {
                        mkdir($imageFile, 0755, true);
                    }

                    $dir = Yii::getAlias($imageFile);
                    $model->icon->saveAs($dir . $fileName);
                    $model->icon = $fileName;

                    if ($model->save(false)) {
                        return $this->redirect('index');
                    }
                } else {
                    return $model->icon;
                }
            }
        }


        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
                'dataProvider' => $dataProvider
            ]);
        }
    }

    /**
     * Updates an existing MeasureType model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $baseImage = $model->icon;

        if ($model->load(Yii::$app->request->post())) {
            $file = UploadedFile::getInstance($model, 'icon');
            if ($file && $file->tempName) {
                $model->icon = $file;
                if ($model->upload()) {
                    $uuidFile = $model->uuid;
                    $dbName = Yii::$app->session->get('user.dbname');
                    $imageFile = 'storage/' . $dbName . '/' . $uuidFile . '/';
                    $fileName = $model->uuid . '.' . $model->icon->extension;

                    if (!is_dir($imageFile)) {
                        mkdir($imageFile, 0755, true);
                    }

                    $dir = Yii::getAlias($imageFile);
                    $model->icon->saveAs($dir . $fileName);
                    $model->icon = $fileName;

                    if ($model->save(false)) {
                        return $this->redirect('index');
                    }
                } else {
                    return $model->icon;
                }
            } else {
                $model->icon = $baseImage;
                if ($model->save(false)) {
                    return $this->redirect('index');
                }
            }
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
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
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model && parent::checkDelete($model->uuid))
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
    protected function findModel($id)
    {
        if (($model = MeasureType::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'Запрашиваемая страница не существует.'));
        }
    }
}
