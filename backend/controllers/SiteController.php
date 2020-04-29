<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new UsersSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 15;

        $current = date("Y0101");
        $orders = Orders::find()
            ->where('startDate>=' . $current)
            ->all();

        $ordersGroup = [];
        $ordersStatusCount = [0, 0, 0, 0];
        $ordersStatusPercent = [0, 0, 0, 0];
        $sumOrderStatusCount = 0;
        $sumOrderStatusCompleteCount = 0;
        for ($c = 0; $c <= 3; $c++) {
            for ($m = 1; $m <= 12; $m++) {
                $ordersGroup[$c][$m] = 0;
            }
        }

        $status[0]['title'] = Yii::t('app', 'Новых');
        $status[1]['title'] = Yii::t('app', 'Не выполнено и отменено');
        $status[2]['title'] = Yii::t('app', 'Всего выполнено');
        $status[3]['title'] = Yii::t('app', 'В работе');

        /** @var Orders $order */
        foreach ($orders as $order) {
            $month = intval(date("m", strtotime($order->startDate)));
            if ($order->orderStatusUuid == OrderStatus::NEW_ORDER
                || $order->orderStatusUuid == OrderStatus::FORMING) {
                $ordersGroup[0][$month]++;
                $ordersStatusCount[0]++;
            }

            if ($order->orderStatusUuid == OrderStatus::CANCELED ||
                $order->orderStatusUuid == OrderStatus::UN_COMPLETE) {
                $ordersGroup[1][$month]++;
                $ordersStatusCount[1]++;
            }

            if ($order->orderStatusUuid == OrderStatus::COMPLETE) {
                $ordersGroup[2][$month]++;
                $ordersStatusCount[2]++;
                $sumOrderStatusCompleteCount++;
            }

            if ($order->orderStatusUuid == OrderStatus::IN_WORK) {
                $ordersGroup[3][$month]++;
                $ordersStatusCount[3]++;
            }
            $sumOrderStatusCount++;
        }

        for ($cnt = 0; $cnt <= 3; $cnt++) {
            if ($sumOrderStatusCount > 0)
                $ordersStatusPercent[$cnt] = $ordersStatusCount[$cnt] * 100 / $sumOrderStatusCount;
            else
                $ordersStatusPercent[$cnt] = 0;
        }

        $first = 0;
        $categories = "'" . Yii::t('app', 'Январь') . "','" .
            Yii::t('app', 'Февраль') . "','" .
            Yii::t('app', 'Март') . "','" .
            Yii::t('app', 'Апрель') . "','" .
            Yii::t('app', 'Май') . "','" .
            Yii::t('app', 'Июнь') . "','" .
            Yii::t('app', 'Июль') . "','" .
            Yii::t('app', 'Август') . "','" .
            Yii::t('app', 'Сентябрь') . "','" .
            Yii::t('app', 'Октябрь') . "','" .
            Yii::t('app', 'Ноябрь') . "','" .
            Yii::t('app', 'Декабрь') . "'";
        $bar = '';
        for ($c = 0; $c <= 3; $c++) {
            if ($first > 0) {
                $bar .= "," . PHP_EOL;
            }

            $bar .= "{ name: '" . $status[$c]['title'] . "',";
            $bar .= "data: [";
            $zero = 0;
            for ($m = 1; $m <= 12; $m++) {
                if (isset($ordersGroup[$c][$m])) {
                    if ($zero > 0) {
                        $bar .= ",";
                    }
                    $bar .= $ordersGroup[$c][$m];
                    //echo $c.' '.$m.' = '.$ordersGroup[$c][$m].PHP_EOL;
                    $zero++;
                }
            }

            $bar .= "]}";
            $first++;
        }

        $sumTaskStatusCount = 0;
        $sumTaskStatusCompleteCount = 0;
        $tasksCount = Task::find()
            ->select('count(_id) AS count, taskStatusUuid')
            ->groupBy('taskStatusUuid')
            ->asArray()
            ->all();
        foreach ($tasksCount as $taskCount) {
            if ($taskCount['taskStatusUuid'] == TaskStatus::COMPLETE) {
                $sumTaskStatusCompleteCount += $taskCount['count'];
            }
            $sumTaskStatusCount += $taskCount['count'];
        }
        $sumStageStatusCount = 0;
        $sumStageStatusCompleteCount = 0;
        $stagesCount = Stage::find()
            ->select('count(_id) AS count, stageStatusUuid')
            ->groupBy('stageStatusUuid')
            ->asArray()
            ->all();
        foreach ($stagesCount as $stageCount) {
            if ($stageCount['stageStatusUuid'] == StageStatus::COMPLETE) {
                $sumStageStatusCompleteCount += $stageCount['count'];
            }

            $sumStageStatusCount += $stageCount['count'];
        }

        $sumOperationStatusCount = 0;
        $sumOperationStatusCompleteCount = 0;
        $operationsCount = Operation::find()
            ->select('count(_id) AS count, operationStatusUuid')
            ->groupBy('operationStatusUuid')
            ->asArray()
            ->all();
        foreach ($operationsCount as $operationCount) {
            if ($operationCount['operationStatusUuid'] == OperationStatus::COMPLETE) {
                $sumOperationStatusCompleteCount += $operationCount['count'];
            }

            $sumOperationStatusCount += $operationCount['count'];
        }

        $equipments = Equipment::find()->where(['deleted' => 0])
            ->select('*')
            ->orderBy('createdAt DESC')
            ->limit(5)
            ->all();

        $equipmentsCount = Equipment::find()->where(['deleted' => 0])
            ->count();

        $usersCount = Users::find()->count();
        $objectsCount = Objects::find()->where(['deleted' => 0])->count();

        $defectsByType = Defect::find()
            ->select('COUNT(*) AS cnt, equipment_type.title AS title')
            ->leftJoin('equipment', 'equipment.uuid=defect.equipmentUuid')
            ->leftJoin('equipment_model', 'equipment_model.uuid=equipment.equipmentModelUuid')
            ->leftJoin('equipment_type', 'equipment_type.uuid=equipment_model.equipmentTypeUuid')
            ->asArray()
            ->groupBy('equipment_type.title')
            ->all();
        $sum = 0;
        foreach ($defectsByType as $defect) {
            $sum += $defect['cnt'];
        }

        $cnt = 0;
        foreach ($defectsByType as $defect) {
            $defectsByType[$cnt]['cnt'] = $defect['cnt'] * 100 / $sum;
            $cnt++;
        }

        $equipmentTypesCount = EquipmentType::find()->count();
        $modelsCount = EquipmentModel::find()->count();
        $documentationCount = Documentation::find()->where(['deleted' => false])->count();
        $trackCount = Gpstrack::find()->count();
        $objectsTypeCount = ObjectType::find()->count();

        $messages = Message::find()
            ->orderBy('date DESC')
            ->all();
        $cnt = 0;
        $messagesChat = [];
        $newMessagesCount = 0;
        foreach ($messages as $message) {
            $messagesChat[$cnt]['text'] = $message['text'];
            if ($message['status'] == Message::MESSAGE_NEW)
                $newMessagesCount++;
            $messagesChat[$cnt]['date'] = date("Y-m-d H:i", strtotime($message['date']));
            $messagesChat[$cnt]['fromUser'] = $message['fromUserUuid'];
            if ($message['fromUser']) {
                $messagesChat[$cnt]['from'] = $message['fromUser']->name;

                $path = $message['fromUser']->getImageUrl();
                if (!$path || !$message['fromUser']['image']) {
                    $path = '/images/unknown.png';
                }
                $path = Html::encode($path);
                $messagesChat[$cnt]['fromImage'] = $path;
            } else {
                $messagesChat[$cnt]['from'] = Yii::t('app', 'неизвестен');
                $messagesChat[$cnt]['fromImage'] = '/images/unknown.png';
            }
            $cnt++;
        }

        Service::updateAll(['status' => 0],
            'last_start_date is null or unix_timestamp() > (unix_timestamp(last_start_date) + delay)');
        $services = Service::find()->all();
        $accountUser = Yii::$app->user->identity;
        $currentUser = Users::find()
            ->where(['userId' => $accountUser['id']])
            ->asArray()
            ->one();

        $layer = self::getLayers();
        $users = Users::find()->all();

        $registers = ServiceRegister::find()->orderBy('createdAt desc')->all();
        return $this->render(
            'dashboard',
            [
                'users' => $users,
                'layer' => $layer,
                'registers' => $registers,
                'ordersStatusCount' => $ordersStatusCount,
                'ordersStatusPercent' => $ordersStatusPercent,
                'sumOrderStatusCount' => $sumOrderStatusCount,
                'sumOrderStatusCompleteCount' => $sumOrderStatusCompleteCount,
                'sumTaskStatusCount' => $sumTaskStatusCount,
                'sumTaskStatusCompleteCount' => $sumTaskStatusCompleteCount,
                'sumStageStatusCount' => $sumStageStatusCount,
                'sumStageStatusCompleteCount' => $sumStageStatusCompleteCount,
                'sumOperationStatusCount' => $sumOperationStatusCount,
                'sumOperationStatusCompleteCount' => $sumOperationStatusCompleteCount,
                'defectsByType' => $defectsByType,
                'categories' => $categories,
                'newMessagesCount' => $newMessagesCount,
                'values' => $bar,
                'orders' => $orders,
                'services' => $services,
                'currentUser' => $currentUser,
                'objectsTypeCount' => $objectsTypeCount,
                'messagesChat' => $messagesChat,
                'equipmentTypesCount' => $equipmentTypesCount,
                'modelsCount' => $modelsCount,
                'documentationCount' => $documentationCount,
                'trackCount' => $trackCount,
                'equipmentsCount' => $equipmentsCount,
                'usersCount' => $usersCount,
                'objectsCount' => $objectsCount,
                'equipments' => $equipments,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider
            ]
        );

        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            $redis = Yii::$app->redis;
            /** @var User $user */
            $user = Yii::$app->user->identity;
            $dbName = $redis->get($user->username);
            if ($dbName == null) {
                throw new HttpException(500, Yii::t('app', 'Не найдена база для пользователя!'));
            }

            $session = Yii::$app->session;
            $session->set('user.dbname', $dbName);

            $log = new JournalUser(); //TODO Исправиь на Journal
            $log->userId = $user->id;
            $log->date = date("Y-m-d H:i:s");
            $userIP = empty(Yii::$app->request->userIP) ? 'unknown' : Yii::$app->request->userIP;
            $log->address = $userIP;
            if (!$log->save()) {
                // TODO: Установить обработчик ошибок
                // и решить что делать с полученным результатом
            }

            return $this->goHome();
        } else {
            return $this->render(
                'login',
                [
                    'model' => $model,
                ]
            );
        }

    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
