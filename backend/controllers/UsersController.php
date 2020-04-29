<?php

namespace backend\controllers;

use api\controllers\TokenController;
use backend\models\Role;
use backend\models\UsersSearch;
use common\components\MainFunctions;
use common\models\Defect;
use common\models\EquipmentRegister;
use common\models\Gpstrack;
use common\models\Journal;
use common\models\Message;
use common\models\Orders;
use common\models\OrderStatus;
use common\models\Task;
use common\models\TaskStatus;
use common\models\Token;
use common\models\User;
use common\models\Users;
use common\models\UsersAttribute;
use Exception;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * UsersController implements the CRUD actions for Users model.
 */
class UsersController extends ToirusController
{
    protected $modelClass = Users::class;

    /**
     * Lists all Users models.
     *
     * @return mixed
     * @throws InvalidConfigException
     * @throws Throwable
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
     * Displays a single Users model.
     *
     * @param integer $id Id.
     *
     * @return mixed
     * @throws \yii\base\Exception
     * @throws Throwable
     */
    public function actionView($id)
    {
        ini_set('memory_limit', '-1');
        $am = Yii::$app->getAuthManager();
        /** @var User $identity */
        $identity = Yii::$app->user->getIdentity();
        if (Yii::$app->user->can(User::ROLE_ADMIN) || $identity->users->_id == $id) {
        } else {
            Yii::$app->session->setFlash('warning', '<h3>'
                . Yii::t('app', 'Не достаточно прав доступа.') . '</h3>');
            $this->redirect('/');
        }

        $user = $this->findModel($id);
        if ($user) {
            $user_orders = Orders::find()->where(['userUuid' => $user['uuid']])->count();
            $user_property['orders'] = $user_orders;
            $user_defects = Defect::find()->where(['userUuid' => $user['uuid']])->count();
            $user_property['defects'] = $user_defects;
            $user_messages = Message::find()->where(['toUserUuid' => $user['uuid']])->count();
            $user_property['messages'] = $user_messages;
            $user_attributes = UsersAttribute::find()->where(['userUuid' => $user['uuid']])->count();
            $user_property['attributes'] = $user_attributes;
            $user_attributes = Gpstrack::find()->where(['userUuid' => $user['uuid']])->count();
            $user_property['tracks'] = $user_attributes;
            $user_property['location'] = MainFunctions::getLocationByUser($user, true);

            $events = [];
            $defects = Defect::find()
                ->where(['=', 'userUuid', $user['uuid']])
                ->limit(3)
                ->all();
            foreach ($defects as $defect) {
                if ($defect['process'] == 1) {
                    $status = '<a class="btn btn-success btn-xs">' . Yii::t('app', 'Исправлен') . '</a>';
                } else {
                    $status = '<a class="btn btn-danger btn-xs">' . Yii::t('app', 'Активен') . '</a>';
                }

                $taskText = $defect->taskUuid != null ? $defect->task->taskTemplate->title : Yii::t('app', 'Нет задачи');
                if ($defect->equipment) {
                    $equipment_title = $defect->equipment->title;
                } else {
                    $equipment_title = Yii::t('app', 'не указано');
                }
                $text = '<a class="btn btn-default btn-xs">' . $equipment_title . '</a>' . $defect['comment'] . '<br/>
                <i class="fa fa-cogs"></i>&nbsp;' . Yii::t('app', 'Задача') . ': ' . $taskText . '<br/>
                <i class="fa fa-check-square"></i>&nbsp;' . Yii::t('app', 'Статус') . ': ' . $status . '';
                $events[] = ['date' => $defect['date'], 'event' => self::formEvent($defect['date'], 'defect', $defect['_id'],
                    $defect['defectType']->title, $text)];
            }

            $journals = Journal::find()
                ->where(['=', 'userUuid', $user['uuid']])
                ->limit(3)
                ->all();
            foreach ($journals as $journal) {
                $text = $journal['description'];
                $events[] = ['date' => $journal['date'], 'event' => self::formEvent($journal['date'], 'journal', 0,
                    $journal['description'], $text)];
            }

            $equipmentRegisters = EquipmentRegister::find()
                ->where(['=', 'userUuid', $user['uuid']])
                ->limit(3)
                ->all();
            foreach ($equipmentRegisters as $equipmentRegister) {
                $path = $equipmentRegister['equipment']->getImageUrl();
                if ($path == null)
                    $path = '/storage/order-level/no-image-icon-4.png';
                $text = '<img src="' . Html::encode($path) . '" style="margin:5px; max-height:70px; margin: 2; float:left" alt="">';
                $text .= '<i class="fa fa-cogs"></i>&nbsp;<a class="btn btn-default btn-xs">' . $equipmentRegister['equipment']->title . '</a><br/>
                <i class="fa fa-clipboard"></i>&nbsp;' . Yii::t('app', 'Изменил параметр') . ': <a class="btn btn-default btn-xs">'
                    . $equipmentRegister['fromParameterUuid'] . '</a>&nbsp;&gt;&nbsp;
                    <a class="btn btn-default btn-xs">' . $equipmentRegister['toParameterUuid'] . '</a>';
                $events[] = ['date' => $equipmentRegister['date'], 'event' => self::formEvent($equipmentRegister['date'],
                    'equipmentRegister', 0, '', $text)];
            }

            $usersAttributes = UsersAttribute::find()
                ->where(['=', 'userUuid', $user['uuid']])
                ->limit(5)
                ->all();
            foreach ($usersAttributes as $usersAttribute) {
                $text = '<a class="btn btn-default btn-xs">' . Yii::t('app', 'Для пользователя зарегистрировано событие') . '</a><br/>
                &nbsp;' . $usersAttribute['attributeType']->name . ' <a class="btn btn-default btn-xs">'
                    . $usersAttribute['value'] . '</a>';
                $events[] = ['date' => $usersAttribute['date'], 'event' => self::formEvent($usersAttribute['date'],
                    'usersAttribute', 0, '', $text)];
            }

            $orders = Orders::find()
                ->where(['=', 'userUuid', $user['uuid']])
                ->all();
            foreach ($orders as $order) {
                if ($order['openDate'] > 0) $openDate = date("j-d-Y h:i", strtotime($order['openDate']));
                else $openDate = Yii::t('app', 'не начинался');
                if ($order['closeDate'] > 0) $closeDate = date("j-d-Y h:i", strtotime($order['closeDate']));
                else $closeDate = Yii::t('app', 'не закончился');
                $text = '' . Yii::t('app', 'Автор') . ': <a class="btn btn-primary btn-xs">' . $order['author']->name . '</a><br/>
                <i class="fa fa-calendar"></i>&nbsp;' . Yii::t('app', 'Открыт') . ': ' . $openDate . '
                <i class="fa fa-calendar"></i>&nbsp;' . Yii::t('app', 'Закрыт') . ': ' . $closeDate . '<br/>';
                $text .= '' . Yii::t('app', 'Основание') . ': <a class="btn btn-default btn-xs">' . $order['reason'] . '</a><br/>';
                if ($order['comment']) $text .= $order['comment'] . '<br/>';
                switch ($order['orderStatus']) {
                    case OrderStatus::COMPLETE:
                        $text .= '<a class="btn btn-success btn-xs">' . Yii::t('app', 'Закончен') . '</a>&nbsp;';
                        break;
                    case OrderStatus::CANCELED:
                        $text .= '<a class="btn btn-danger btn-xs">' . Yii::t('app', 'Отменен') . '</a>&nbsp;';
                        break;
                    case OrderStatus::UN_COMPLETE:
                        $text .= '<a class="btn btn-warning btn-xs">' . Yii::t('app', 'Не закончен') . '</a>&nbsp;';
                        break;
                    default:
                        $text .= '<a class="btn btn-warning btn-xs">' . Yii::t('app', 'Не определен') . '</a>&nbsp;';
                }
                $events[] = ['date' => $order['startDate'], 'event' => self::formEvent($order['startDate'],
                    'order', 0, $order['title'], $text)];
            }

            $messages = Message::find()
                ->where(['=', 'toUserUuid', $user['uuid']])
                ->limit(5)
                ->all();
            foreach ($messages as $message) {
                $text = 'От: <a class="btn btn-default btn-xs">' . $message['fromUser']->name . '</a><br/>' . $message['text'];
                $events[] = ['date' => $message['date'], 'event' => self::formEvent($message['date'],
                    'message', 0, '', $text)];
            }

            $orders = Orders::find()
                ->where(['userUuid' => $user['uuid']])
                ->limit(5)
                ->orderBy('startDate desc')
                ->all();
            $orderCount = 0;
            $tree = [];
            foreach ($orders as $order) {
                $tasks = Task::find()
                    ->where(['orderUuid' => $order['uuid']])
                    ->all();
                $tree[$orderCount] = '';
                foreach ($tasks as $task) {
                    if ($task['startDate'] > 0) $startDate = date("M j, Y", strtotime($task['startDate']));
                    else $startDate = Yii::t('app', 'не начиналась');
                    if ($task['endDate'] > 0) $endDate = date("M j, Y", strtotime($task['endDate']));
                    else $endDate = Yii::t('app', 'не закончилась');
                    switch ($task['taskStatus']) {
                        case TaskStatus::COMPLETE:
                            $tree[$orderCount] .= '<span class="label label-success">' . Yii::t('app', 'Закончен') . '</span>&nbsp;';
                            break;
                        case TaskStatus::CANCELED:
                            $tree[$orderCount] .= '<span class="label label-danger">' . Yii::t('app', 'Отменен') . '</span>&nbsp;';
                            break;
                        case TaskStatus::UN_COMPLETE:
                            $tree[$orderCount] .= '<span class="label label-warning">' . Yii::t('app', 'Не закончен') . '</span>&nbsp;';
                            break;
                        case TaskStatus::NEW_TASK:
                            $tree[$orderCount] .= '<span class="label label-info">' . Yii::t('app', 'Не закончен') . '</span>&nbsp;';
                            break;
                        default:
                            $tree[$orderCount] .= '<span class="label label-info">' . Yii::t('app', 'Не определен') . '</span>&nbsp;';
                    }

                    $tree[$orderCount] .= $task['taskTemplate']->title . '&nbsp;<i class="fa fa-calendar"></i>['
                        . $startDate . '&nbsp;-&nbsp;' . $endDate . ']<br/>';
                }

                $orderCount++;
            }

            $sort_events = MainFunctions::array_msort($events, ['date' => SORT_DESC]);


            $defaultRole = User::ROLE_OPERATOR;
            $userRoles = $am->getRolesByUser($user->userId);
            if (!empty($userRoles)) {
                foreach ($userRoles as $userRole) {
                    $defaultRole = $userRole->name;
                    break;
                }
            }

            $role = new Role();
            // значение по умолчанию
            $role->role = $defaultRole;
            $roles = $am->getRoles();
            $assignments = $am->getAssignments($user->userId);
            foreach ($assignments as $value) {
                if (key_exists($value->roleName, $roles)) {
                    $role->role = $value->roleName;
                    break;
                }
            }

            $roleList = ArrayHelper::map($roles, 'name', 'description');
            return $this->render(
                'view',
                [
                    'model' => $user,
                    'user_property' => $user_property,
                    'orders' => $orders,
                    'events' => $sort_events,
                    'tree' => $tree,
                    'role' => $role,
                    'roleList' => $roleList,
                ]
            );
        } else {
            return $this->redirect(['users/dashboard']);
        }
    }

    /**
     * Creates a new Users model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionCreate()
    {
        $model = new Users();
        $model->type = 1;
        $am = Yii::$app->getAuthManager();
        $roles = $am->getRoles();
        $roleList = ArrayHelper::map($roles, 'name', 'description');
        $role = new Role();
        // значение по умолчанию
        $role->role = User::ROLE_OPERATOR;

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
                MainFunctions::register(Yii::t('app', 'Добавлен пользователь ') . $model->name);

                if (!empty($pass)) {
                    // обновляем пароль для связанной записи из таблицы user
                    $user = User::findOne($model->userId);
                    if ($user != null) {
                        $user->setPassword($pass);
                        $user->save();
                    }
                }

                if ($role->load(Yii::$app->request->post())) {
                    $newRole = $am->getRole($role->role);
                    $am->assign($newRole, $model->userId);
                }

                //return $this->redirect(['view', 'id' => $model->_id]);
                return $this->redirect(['/users']);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'role' => $role,
            'roleList' => $roleList,
        ]);
    }

    /**
     * @return mixed
     */
    public function actionDashboard()
    {
        $users = Users::find()->orderBy('createdAt DESC')->all();
        $count = 0;
        $user_property[][] = '';
        foreach ($users as $user) {
            $user_orders = Orders::find()->where(['userUuid' => $user['uuid']])->count();
            $user_property[$count]['orders'] = $user_orders;
            $user_defects = Defect::find()->where(['userUuid' => $user['uuid']])->count();
            $user_property[$count]['defects'] = $user_defects;
            $user_messages = Message::find()->where(['toUserUuid' => $user['uuid']])->count();
            $user_property[$count]['messages'] = $user_messages;
            $count++;
        }
        return $this->render('dashboard', [
            'users' => $users,
            'user_property' => $user_property
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
        $am = Yii::$app->getAuthManager();
        $defaultRole = User::ROLE_OPERATOR;

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
                MainFunctions::register(Yii::t('app', 'Обновлен профиль пользователя ') . $model->name);

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

        $role = new Role();
        // значение по умолчанию
        $role->role = $defaultRole;
        $roles = $am->getRoles();
        $assignments = $am->getAssignments($id);
        foreach ($assignments as $value) {
            if (key_exists($value->roleName, $roles)) {
                $role->role = $value->roleName;
                break;
            }
        }

        $roleList = ArrayHelper::map($roles, 'name', 'description');

        return $this->render(
            'update',
            [
                'model' => $model,
                'role' => $role,
                'roleList' => $roleList,
            ]
        );
    }

    /**
     * Deletes an existing Users model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id Id.
     *
     * @return mixed
     * @throws Exception
     * @throws Throwable
     */
    public function actionDelete($id)
    {
        /** @var Users $user */
        $user = Users::findOne($id);
        if ($user != null) {
            $user->active = 0;
            $user->save();
            return $this->redirect(['/users/view', 'id' => $user->_id]);
        } else {
            return $this->redirect(['/users/index']);
        }
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
                $users = Users::find()->where(['tagId' => $tokens[0]->tagId])->all();
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

    /**
     * @return int|string
     * @throws NotFoundHttpException
     */
    public function actionEdit()
    {
        if (isset ($_GET["id"]))
            $id = $_GET["id"];
        else
            return null;

        $model = $this->findModel($id);

        $model->scenario = Users::UPDATE_SCENARIO;
        $am = Yii::$app->getAuthManager();
        $defaultRole = User::ROLE_OPERATOR;
        $userRoles = $am->getRolesByUser($model->userId);
        if (!empty($userRoles)) {
            foreach ($userRoles as $userRole) {
                $defaultRole = $userRole->name;
                break;
            }
        }

        $role = new Role();
        // значение по умолчанию
        $role->role = $defaultRole;
        $roles = $am->getRoles();
        $assignments = $am->getAssignments($id);
        foreach ($assignments as $value) {
            if (key_exists($value->roleName, $roles)) {
                $role->role = $value->roleName;
                break;
            }
        }

        $roleList = ArrayHelper::map($roles, 'name', 'description');

        return $this->renderAjax('../users/_edit_users', [
            'model' => $model,
            'role' => $role,
            'roleList' => $roleList,
        ]);
    }

    /**
     * Displays a single Users timeline.
     *
     * @param integer $id Id.
     *
     * @return mixed
     * @throws Throwable
     */
    public function actionTimeline($id)
    {
        ini_set('memory_limit', '-1');

        if (!empty($_GET['type']) && is_numeric($_GET['type'])) {
            $type = intval($_GET['type']);
        } else {
            $type = null;
        }

        try {
            $user = $this->findModel($id);
        } catch (Exception $exception) {
            return $this->redirect(['/users/table']);
        }

        $events = [];

        $defects = Defect::find()
            ->where(['=', 'userUuid', $user['uuid']])
            ->orderBy('date DESC')
            ->limit(3)
            ->all();
        if ($type == 2) {
            foreach ($defects as $defect) {
                if ($defect['process'] == 1) {
                    $status = '<a class="btn btn-success btn-xs">' . Yii::t('app', 'Исправлен') . '</a>';
                } else {
                    $status = '<a class="btn btn-danger btn-xs">' . Yii::t('app', 'Активен') . '</a>';
                }

                $taskText = $defect->taskUuid != null ? $defect->task->taskTemplate->title : 'Нет задачи';
                if ($defect->equipment) {
                    $equipment_title = $defect->equipment->title;
                } else {
                    $equipment_title = Yii::t('app', 'не указано');
                }
                $text = '<a class="btn btn-default btn-xs">' . $equipment_title . '</a>' . $defect['comment'] . '<br/>
                <i class="fa fa-cogs"></i>&nbsp;' . Yii::t('app', 'Задача') . ': ' . $taskText . '<br/>
                <i class="fa fa-check-square"></i>&nbsp;' . Yii::t('app', 'Статус') . ': ' . $status . '';
                $events[] = ['date' => $defect['date'], 'event' => self::formEvent($defect['date'], 'defect', $defect['_id'],
                    $defect['defectType']->title, $text)];
            }
        }

        if ($type == null) {
            $journals = Journal::find()
                ->where(['=', 'userUuid', $user['uuid']])
                ->orderBy('date DESC')
                ->limit(3)
                ->all();
            foreach ($journals as $journal) {
                $text = $journal['description'];
                $events[] = ['date' => $journal['date'], 'event' => self::formEvent($journal['date'], 'journal', 0,
                    $journal['description'], $text)];
            }
        }

        $equipmentRegisters = EquipmentRegister::find()
            ->where(['=', 'userUuid', $user['uuid']])
            ->orderBy('date DESC')
            ->limit(3)
            ->all();
        if ($type == null) {
            foreach ($equipmentRegisters as $equipmentRegister) {
                $path = $equipmentRegister['equipment']->getImageUrl();
                if ($path == null)
                    $path = '/storage/order-level/no-image-icon-4.png';
                $text = '<img src="' . Html::encode($path) . '" style="margin:5px; width:50px; margin: 2; float:left" alt="">';
                $text .= '<i class="fa fa-cogs"></i>&nbsp;<a class="btn btn-default btn-xs">' . $equipmentRegister['equipment']->title . '</a><br/>
                <i class="fa fa-clipboard"></i>&nbsp;' . Yii::t('app', 'Изменил параметр') . ': <a class="btn btn-default btn-xs">'
                    . $equipmentRegister['fromParameterUuid'] . '</a>&nbsp;&gt;&nbsp;
                    <a class="btn btn-default btn-xs">' . $equipmentRegister['toParameterUuid'] . '</a>';
                $events[] = ['date' => $equipmentRegister['date'], 'event' => self::formEvent($equipmentRegister['date'],
                    'equipmentRegister', 0, '', $text)];
            }
        }

        $usersAttributes = UsersAttribute::find()
            ->where(['=', 'userUuid', $user['uuid']])
            ->limit(5)
            ->all();
        if ($type == 3) {
            foreach ($usersAttributes as $usersAttribute) {
                $text = '<a class="btn btn-default btn-xs">' .
                    Yii::t('app', 'Для пользователя зарегистрировано событие') . '</a><br/>
                &nbsp;' . $usersAttribute['attributeType']->name . ' <a class="btn btn-default btn-xs">'
                    . $usersAttribute['value'] . '</a>';
                $events[] = ['date' => $usersAttribute['date'], 'event' => self::formEvent($usersAttribute['date'],
                    'usersAttribute', 0, '', $text)];
            }
        }

        $orders = Orders::find()
            ->where(['=', 'userUuid', $user['uuid']])
            ->orderBy('startDate DESC')
            ->all();
        if ($type == 1) {
            foreach ($orders as $order) {
                if ($order['openDate'] > 0) $openDate = date("j-d-Y h:i", strtotime($order['openDate']));
                else $openDate = Yii::t('app', 'не начинался');
                if ($order['closeDate'] > 0) $closeDate = date("j-d-Y h:i", strtotime($order['closeDate']));
                else $closeDate = Yii::t('app', 'не закончился');
                $text = '' . Yii::t('app', 'Автор') . ': <a class="btn btn-primary btn-xs">' . $order['author']->name . '</a><br/>
                <i class="fa fa-calendar"></i>&nbsp;' . Yii::t('app', 'Открыт') . ': ' . $openDate . '
                <i class="fa fa-calendar"></i>&nbsp;' . Yii::t('app', 'Закрыт') . ': ' . $closeDate . '<br/>';
                $text .= '' . Yii::t('app', 'Основание') . ': <a class="btn btn-default btn-xs">' . $order['reason'] . '</a><br/>';
                if ($order['comment']) $text .= $order['comment'] . '<br/>';
                switch ($order['orderStatus']) {
                    case OrderStatus::COMPLETE:
                        $text .= '<a class="btn btn-success btn-xs">' .
                            Yii::t('app', 'Закончен') . '</a>&nbsp;';
                        break;
                    case OrderStatus::CANCELED:
                        $text .= '<a class="btn btn-danger btn-xs">' .
                            Yii::t('app', 'Отменен') . '</a>&nbsp;';
                        break;
                    case OrderStatus::UN_COMPLETE:
                        $text .= '<a class="btn btn-warning btn-xs">' .
                            Yii::t('app', 'Не закончен') . '</a>&nbsp;';
                        break;
                    default:
                        $text .= '<a class="btn btn-warning btn-xs">' .
                            Yii::t('app', 'Не определен') . '</a>&nbsp;';
                }
                $events[] = ['date' => $order['startDate'], 'event' => self::formEvent($order['startDate'],
                    'order', 0, $order['title'], $text)];
            }
        }

        $messages = Message::find()
            ->where(['=', 'toUserUuid', $user['uuid']])
            ->limit(5)
            ->orderBy('date DESC')
            ->all();
        if ($type == null) {
            foreach ($messages as $message) {
                $text = 'От: <a class="btn btn-default btn-xs">' . $message['fromUser']->name . '</a><br/>' . $message['text'];
                $events[] = ['date' => $message['date'], 'event' => self::formEvent($message['date'],
                    'message', 0, '', $text)];
            }
        }

        $orders = Orders::find()
            ->where(['userUuid' => $user['uuid']])
            ->limit(5)
            ->orderBy('startDate desc')
            ->all();
        $orderCount = 0;
        $tree = [];
        if ($type == 1) {
            foreach ($orders as $order) {
                $tasks = Task::find()
                    ->where(['orderUuid' => $order['uuid']])
                    ->all();
                $tree[$orderCount] = '';
                foreach ($tasks as $task) {
                    if ($task['startDate'] > 0) $startDate = date("M j, Y", strtotime($task['startDate']));
                    else $startDate = Yii::t('app', 'не начиналась');
                    if ($task['endDate'] > 0) $endDate = date("M j, Y", strtotime($task['endDate']));
                    else $endDate = Yii::t('app', 'не закончилась');
                    switch ($task['taskStatus']) {
                        case TaskStatus::COMPLETE:
                            $tree[$orderCount] .= '<span class="label label-success">' .
                                Yii::t('app', 'Закончен') . '</span>&nbsp;';
                            break;
                        case TaskStatus::CANCELED:
                            $tree[$orderCount] .= '<span class="label label-danger">' .
                                Yii::t('app', 'Отменен') . '</span>&nbsp;';
                            break;
                        case TaskStatus::UN_COMPLETE:
                            $tree[$orderCount] .= '<span class="label label-warning">' .
                                Yii::t('app', 'Не закончен') . '</span>&nbsp;';
                            break;
                        case TaskStatus::NEW_TASK:
                            $tree[$orderCount] .= '<span class="label label-info">' .
                                Yii::t('app', 'Не закончен') . '</span>&nbsp;';
                            break;
                        default:
                            $tree[$orderCount] .= '<span class="label label-info">' .
                                Yii::t('app', 'Не определен') . '</span>&nbsp;';
                    }

                    $tree[$orderCount] .= $task['taskTemplate']->title . '&nbsp;<i class="fa fa-calendar"></i>['
                        . $startDate . '&nbsp;-&nbsp;' . $endDate . ']<br/>';
                }

                $orderCount++;
            }
        }

        $sort_events = MainFunctions::array_msort($events, ['date' => SORT_DESC]);
        $today = date("j-m-Y h:i");
        return $this->render(
            'timeline',
            [
                'events' => $sort_events,
                'today_date' => $today,
                'type' => $type,
                'id' => $id,
            ]
        );
    }
}
