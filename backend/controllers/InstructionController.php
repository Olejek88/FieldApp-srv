<?php

namespace backend\controllers;

use backend\models\InstructionSearch;
use common\components\MainFunctions;
use common\models\Instruction;
use common\models\InstructionStageTemplate;
use common\models\Users;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * InstructionController implements the CRUD actions for Instruction model.
 */
class InstructionController extends ToirusController
{
    protected $modelClass = Instruction::class;

    /**
     * Lists all Instruction models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new InstructionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->setSort(['defaultOrder' => ['_id' => SORT_DESC]]);
        $dataProvider->query->where(['deleted' => 0]);
        $dataProvider->pagination->pageSize = 25;
        if (isset($_POST['editableAttribute'])) {
            $model = Instruction::find()
                ->where(['_id' => $_POST['editableKey']])
                ->one();
            if ($_POST['editableAttribute'] == 'title') {
                $model['title'] = $_POST['Instruction'][$_POST['editableIndex']]['title'];
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
        $instruction = $this->findModel($id);
        $instruction->deleted = 1;
        $instruction->save();
        return $this->redirect(['index']);
    }

    /**
     * Finds the Instruction model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id Id
     *
     * @return Instruction the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Instruction::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'Запрашиваемая страница не существует.'));
        }
    }

    /**
     */
    public function actionList()
    {
        if (isset($_GET["entityUuid"]))
            $entityUuid = $_GET["entityUuid"];
        else $entityUuid = '';

        $instructions = Instruction::find()->where(['entityUuid' => $entityUuid])
            ->where(['deleted' => 0])
            ->all();
        return $this->renderAjax('_instruction_list', [
            'instruction' => $instructions,
        ]);
    }

    /**
     * @return int|string
     */
    public function actionNew()
    {
        $instruction = new Instruction();
        return $this->renderAjax('../instruction/_add_form', [
            'instruction' => $instruction
        ]);
    }

    /**
     * @return bool|string|Response
     */
    public function actionSave()
    {
        $model = new Instruction();
        if ($model->load(Yii::$app->request->post())) {
            $accountUser = Yii::$app->user->identity;
            $currentUser = Users::findOne(['userId' => $accountUser['id']]);
            $model['userUuid'] = $currentUser['uuid'];

            // получаем файл для последующего сохранения
            $file = UploadedFile::getInstance($model, 'path');
            if ($file && $file->tempName) {
                $fileName = self::_saveFile($model, $file);
                if ($fileName) {
                    $model->path = $fileName;
                    $dbName = Yii::$app->session->get('user.dbname');
                    $localPath = 'storage/' . $dbName . '/instruction/' . $model->path;
                    $model->size = filesize($localPath);
                }
            }

            if ($model->save(false)) {
                return $this->redirect(['/instruction']);
            } else
                return json_encode($model->errors);
        }
        return $this->render('_add_form', ['model' => $model]);
    }

    /**
     * Сохраняем файл согласно нашим правилам.
     *
     * @param Instruction $model Документация
     * @param UploadedFile $file Файл
     *
     * @return string | null
     */
    public static function _saveFile($model, $file)
    {
        $dir = $model->getDocDir();
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                return null;
            }
        }

        $targetDir = Yii::getAlias($dir);
        $fileName = $model->uuid . '.' . $file->extension;
        if ($file->saveAs($targetDir . $fileName)) {
            return $fileName;
        } else {
            return null;
        }
    }

}
