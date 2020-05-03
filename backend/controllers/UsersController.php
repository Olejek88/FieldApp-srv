<?php

namespace backend\controllers;

use api\controllers\TokenController;
use backend\models\UsersSearch;
use common\models\Token;
use common\models\User;
use common\models\Users;
use Exception;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\rbac\Role;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * UsersController implements the CRUD actions for Users model.
 */
class UsersController extends Controller
{
    protected $modelClass = Users::class;

    /**
     * Lists all Users models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UsersSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 15;

        return $this->render(
            'index',
            [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]
        );
    }

    /**
     * Table all Users models.
     *
     * @return mixed
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function actionTable()
    {
        if (isset($_POST['editableAttribute'])) {
            $model = Users::find()
                ->where(['_id' => $_POST['editableKey']])
                ->one();
            if ($_POST['editableAttribute'] == 'type') {
                $model['type'] = intval($_POST['Users'][$_POST['editableIndex']]['type']);
                $model->save();
                return json_encode($model->errors);
            }
            if ($_POST['editableAttribute'] == 'active') {
                $model['active'] = $_POST['Users'][$_POST['editableIndex']]['active'];
                $model->save();
                return json_encode($model->errors);
            }
        }
        if (isset($_POST['editableKey'])) {
            /** @var Users $model */
            $model = Users::find()
                ->where(['_id' => $_POST['editableKey']])
                ->one();
            if (isset ($_POST['email'])) {
                /** @var User $user */
                $user = User::find()->where(['id' => $model->userId])->one();
                if ($user) {
                    $user->email = $_POST['email'];
                    $user->save();
                    return json_encode('');
                }
            }
            $model->save();
            return json_encode('');
        }

        $searchModel = new UsersSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 15;
        return $this->render(
            'table',
            [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]
        );
    }

    /**
     * Creates a new Users model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     * @throws InvalidConfigException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionCreate()
    {
        $model = new Users();
        $model->type = 1;
        if ($model->load(Yii::$app->request->post())) {
            // получаем изображение для последующего сохранения
            $file = UploadedFile::getInstance($model, 'image');
            if ($file && $file->tempName) {
                $fileName = self::_saveFile($model, $file);
                if ($fileName) {
                    $model->image = $fileName;
                } else {
                    // уведомить пользователя, админа о невозможности сохранить файл
                    Yii::error(Yii::t('app', 'Не возможно сохранить файл изображения пользователя!'));
                }
            }

            $usersParams = Yii::$app->request->getBodyParam('Users', null);
            $pass = $usersParams['pass'];
            if (!empty($pass)) {
                $model->pass = Yii::$app->security->generatePasswordHash($pass);
            }

            if ($model->save()) {
                if (!empty($pass)) {
                    // обновляем пароль для связанной записи из таблицы user
                    $user = User::findOne($model->userId);
                    if ($user != null) {
                        $user->setPassword($pass);
                        $user->save();
                    }
                }
                return $this->redirect(['/users']);
            }
        }

        return $this->render('create', [
            'model' => $model
        ]);
    }

    /**
     * Updates an existing Users model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id Id.
     *
     * @return mixed
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = Users::UPDATE_SCENARIO;
        // сохраняем старое значение image
        $oldImage = $model->image;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // получаем изображение для последующего сохранения
            $file = UploadedFile::getInstance($model, 'image');
            if ($file && $file->tempName) {
                $fileName = self::_saveFile($model, $file);
                if ($fileName) {
                    $model->image = $fileName;
                } else {
                    $model->image = $oldImage;
                    // уведомить пользователя, админа о невозможности сохранить файл
                }
            } else {
                $model->image = $oldImage;
            }

            $usersParams = Yii::$app->request->getBodyParam('Users', null);
            $pass = $usersParams['pass'];
            if (!empty($pass)) {
                $model->pass = Yii::$app->security->generatePasswordHash($pass);
            }

            if ($model->save()) {
                if (!empty($pass)) {
                    // обновляем пароль для связанной записи из таблицы user
                    $user = User::findOne($model->userId);
                    if ($user != null) {
                        $user->setPassword($pass);
                        $user->save();
                    }
                }

                // обновляем разрешения пользователя
                $newRoleModel = new Role();
                if ($newRoleModel->load(Yii::$app->request->post())) {
                    $newRole = $am->getRole($newRoleModel->role);
                    try {
                        // удаляем все назначения прав связанных с ролями
                        $userId = $model->userId;
                        $userRoles = $am->getRolesByUser($userId);
                        foreach ($userRoles as $userRole) {
                            $am->revoke($userRole, $userId);
                        }

                        $am->assign($newRole, $userId);
                    } catch (Exception $e) {
                        // видимо такое разрешение есть
                    }
                }
                return $this->redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $userRoles = $am->getRolesByUser($model->userId);
            if (!empty($userRoles)) {
                foreach ($userRoles as $userRole) {
                    $defaultRole = $userRole->name;
                    break;
                }
            }
        }
        return $this->render(
            'update',
            [
                'model' => $model
            ]
        );
    }

    /**
     * Finds the Users model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id Id.
     *
     * @return Users the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Users::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'Запрашиваемая страница не существует.'));
        }
    }

    /**
     * Возвращает объект Users по токену.
     *
     * @param string $token Токен.
     *
     * @return Users|null Оъект пользователя.
     */
    public static function getUserByToken($token)
    {
        if (TokenController::isTokenValid($token)) {
            $tokens = Token::find()->where(['accessToken' => $token])->all();
            if (count($tokens) == 1) {
                $users = Users::find()->where(['login' => $tokens[0]->userName])->all();
                $user = count($users) == 1 ? $users[0] : null;
                return $user;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * Сохраняем файл согласно нашим правилам.
     *
     * @param Users $model Пользователь
     * @param UploadedFile $file Файл
     *
     * @return string | null
     */
    private static function _saveFile($model, $file)
    {
        $dir = $model->getImageDir();
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

    /**
     * Формируем код записи о событии
     * @param $date
     * @param $type
     * @param $id
     * @param $title
     * @param $text
     *
     * @return string
     */
    public static function formEvent($date, $type, $id, $title, $text)
    {
        $event = '<li>';
        if ($type == 'defect')
            $event .= '<i class="fa fa-wrench bg-red"></i>';
        if ($type == 'journal')
            $event .= '<i class="fa fa-calendar bg-aqua"></i>';
        if ($type == 'equipmentRegister')
            $event .= '<i class="fa fa-cogs bg-green"></i>';
        if ($type == 'usersAttribute')
            $event .= '<i class="fa fa-user bg-gray"></i>';
        if ($type == 'order')
            $event .= '<i class="fa fa-sitemap bg-yellow"></i>';
        if ($type == 'message')
            $event .= '<i class="fa fa-envelope bg-blue"></i>';

        $event .= '<div class="timeline-item">';
        $event .= '<span class="time"><i class="fa fa-clock-o"></i> ' . date("M j, Y h:i", strtotime($date)) . '</span>';
        if ($type == 'defect')
            $event .= '<h3 class="timeline-header">' . Html::a(Yii::t('app', 'Пользователь зарегистрировал дефект &nbsp;'),
                    ['/defect/view', 'id' => Html::encode($id)]) . $title . '</h3>';
        if ($type == 'journal')
            $event .= '<h3 class="timeline-header"><a href="#">' . Yii::t('app', 'Добавлено событие журнала') . '</a></h3>';
        if ($type == 'equipmentRegister')
            $event .= '<h3 class="timeline-header">' . Html::a(Yii::t('app', 'Параметр оборудования изменен &nbsp;'),
                    ['/equipment-register/view', 'id' => Html::encode($id)]) . $title . '</h3>';
        if ($type == 'usersAttribute')
            $event .= '<h3 class="timeline-header">' . Html::a(Yii::t('app', 'Изменен аттрибут пользователя &nbsp;'),
                    ['/equipment-register/view', 'id' => Html::encode($id)]) . $title . '</h3>';
        if ($type == 'order')
            $event .= '<h3 class="timeline-header">' . Html::a(Yii::t('app', 'Сформирован наряд &nbsp;'),
                    ['/orders/view', 'id' => Html::encode($id)]) . '[' . $title . ']</h3>';
        if ($type == 'message')
            $event .= '<h3 class="timeline-header">' . Html::a(Yii::t('app', 'Получено сообщение &nbsp;'),
                    ['/messages/view', 'id' => Html::encode($id)]) . $title . '</h3>';

        $event .= '<div class="timeline-body" style="min-height: 100px;">' . $text . '</div>';
        $event .= '</div></li>';
        return $event;
    }

    /**
     * @param $action
     * @return bool
     * @throws BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        } else {
            return true;
        }
    }
}
