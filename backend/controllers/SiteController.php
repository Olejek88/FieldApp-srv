<?php
namespace backend\controllers;

use backend\models\ChannelSearch;
use backend\models\MeasuredSearchValue;
use common\models\MeasuredValue;
use common\models\User;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use yii\web\HttpException;

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
                        'actions' => ['login', 'error', 'dashboard'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'dashboard'],
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
    public function actionDashboard()
    {
        //@var $measures
        $searchModel = new ChannelSearch();
        $dataProviderChannels = $searchModel->search(Yii::$app->request->queryParams);
        $dataProviderChannels->pagination->pageSize = 15;

        $searchModel = new MeasuredSearchValue();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 15;

        $measureChart['values'] = '';
        $measureChart['dates'] = '';
        $title = '';
        /** @var MeasuredValue $lastMeasure */
        $lastMeasure = MeasuredValue::find()->orderBy('date desc')->limit(1)->one();
        if ($lastMeasure) {
            $count = 0;
            $title = $lastMeasure->channel->title;
            $measures = MeasuredValue::find()
                ->where(['channelUuid' => $lastMeasure->channelUuid])
                ->orderBy('date desc')
                ->limit(30)
                ->all();
            foreach ($measures as $measure) {
                if ($count > 0) {
                    $measureChart['values'] .= ',';
                    $measureChart['dates'] .= ',';
                }
                $measureChart['values'] .= $measure['value'];
                $measureChart['dates'] .= '\'' . $measure['date'] . '\'';
                $count++;
            }
        }

        return $this->render(
            'dashboard',
            [
                'channels' => $dataProviderChannels,
                'measures' => $dataProvider,
                'chart' => $measureChart,
                'title' => $title
            ]
        );
    }

    /**
     * Login action.
     *
     * @return string
     * @throws HttpException
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
            /** @var User $user */
            $user = Yii::$app->user->identity;
            $dbName = $redis->get($user->username);
            if ($dbName == null) {
                throw new HttpException(500, Yii::t('app', 'Не найдена база для пользователя!'));
            }

            $session = Yii::$app->session;
            $session->set('user.dbname', $dbName);
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
