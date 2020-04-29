<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use Yii;
use yii\db\Expression;
use yii\redis\Connection;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\Exception;
use Throwable;

/**
 * Class Users
 *
 * @property integer $_id
 * @property string $uuid
 * @property string $name
 * @property string $login
 * @property string $pass
 * @property integer $type
 * @property string $tagId
 * @property integer $active
 * @property string $whoIs
 * @property string $image
 * @property string $contact
 * @property integer $userId
 * @property integer $connectionDate
 * @property integer $createdAt
 * @property integer $changedAt
 *
 * @property User $user
 * @property int $id
 * @property string $imageUrl
 * @property null|string $typeName
 * @property string $imageDir
 */
class Users extends ToirusModel
{
    private static $_IMAGE_ROOT = 'users';

    const USER_SYSTEM = "1111111-1111-1111-1111-111111111111";

    const UPDATE_SCENARIO = 'update';

    const TYPE_OPERATOR = 1; // пользователь может быть аутентифицирован только как оператор АРМ
    const TYPE_WORKER = 2; // пользователь может быть аутентифицирован только как исполнитель
    const TYPE_BOTH = 3; // пользователь может быть и оператором и исполнителем

    /**
     * Behaviors.
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'createdAt',
                'updatedAtAttribute' => 'changedAt',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * Table name.
     *
     * @return string
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * Rules.
     *
     * @return array
     */
    public function rules()
    {
        return [
            [
                [
                    'uuid',
                    'name',
                    'login',
//                    'type',
                    'tagId',
                    'active',
                    'whoIs',
                    'contact',
                    'userId'
                ],
                'required', 'on' => 'default'
            ],
            [
                [
                    'uuid',
                    'name',
//                    'type',
                    'tagId',
                    'active',
                    'whoIs',
                    'contact',
                    'userId'
                ],
                'required', 'on' => self::UPDATE_SCENARIO
            ],
            ['tagId', 'unique', 'targetClass' => '\common\models\Users', 'message' => 'Эта метка уже используется.'],
            ['tagId', 'checkUniqueGlobal'],
            [['type', 'active', 'userId'], 'integer'],
            [['image'], 'file'],
            [['connectionDate', 'createdAt', 'changedAt'], 'safe'],
            [['uuid'], 'string', 'max' => 50, 'on' => self::UPDATE_SCENARIO],
            [['uuid', 'login'], 'string', 'max' => 50, 'on' => 'default'],
            [['name', 'tagId', 'contact'], 'string', 'max' => 100],
            [['whoIs'], 'string', 'max' => 45],
            [
                [
                    'uuid',
                    'name',
                    'login',
                    'tagId',
                    'whoIs',
                    'contact',
                ],
                'filter', 'filter' => function ($param) {
                return htmlspecialchars($param, ENT_QUOTES | ENT_HTML401);
                }
            ],
        ];
    }

    /**
     * Проверяем на уникальность метку среди всех клиентов.
     *
     * @param $attr
     * @param $param
     * @throws InvalidConfigException
     */
    public function checkUniqueGlobal($attr, $param)
    {
        $dirtyValue = $this->getDirtyAttributes([$attr]);
        if (count($dirtyValue) == 0) {
            return;
        }

        $oldValue = $this->getOldAttribute($attr);
        if ($oldValue == $dirtyValue[$attr]) {
            return;
        }

        if (!$this->hasErrors()) {
            /** @var Connection $redis */
            $redis = Yii::$app->get('redis');
            $result = $redis->get($this->$attr);
            if ($result != null) {
                $this->addError($attr, 'Эта метка уже используется.');
            }
        }
    }

    /**
     * Метки для свойств.
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            '_id' => Yii::t('app', '№'),
            'uuid' => Yii::t('app', 'Uuid'),
            'name' => Yii::t('app', 'Имя'),
            'login' => Yii::t('app', 'Логин'),
            'pass' => Yii::t('app', 'Пароль'),
            'type' => Yii::t('app', 'Тип'),
            'tagId' => Yii::t('app', 'Tag ID'),
            'active' => Yii::t('app', 'Статус'),
            'whoIs' => Yii::t('app', 'Должность'),
            'image' => Yii::t('app', 'Фотография'),
            'contact' => Yii::t('app', 'Контакт руководителя'),
            'userId' => Yii::t('app', 'User id'),
            'connectionDate' => Yii::t('app', 'Дата подключения'),
            'createdAt' => Yii::t('app', 'Создан'),
            'changedAt' => Yii::t('app', 'Изменен'),
        ];
    }

    /**
     * {@inheritdoc}
     *
     * The default implementation returns the names of the relations that have been populated into this record.
     */
    public function extraFields()
    {
        $ef = parent::extraFields();
        $ef[] = 'user';
        return $ef;
    }

    /**
     * Связываем пользователей из yii с пользователями из toir.
     *
     * @return ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'userId']);
    }

    /**
     * Проверка целостности модели?
     *
     * @return bool
     */
    public function upload()
    {
        if ($this->validate()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Возвращает id.
     *
     * @return int
     */
    public function getId()
    {
        return $this['_id'];
    }

    /**
     * Какие-то действия.
     *
     * @return void
     */
    public function afterFind()
    {
//        $this->active = $this->active == 0 ? false : true;
        parent::afterFind();
    }

    /**
     * URL изображения.
     *
     * @return string | null
     */
    public function getImageUrl()
    {
        $dbName = Yii::$app->session->get('user.dbname');
        $noFileUrl = Yii::$app->request->baseUrl . '/images/unknown2.png';
        if ($this->image) {
            $localPath = 'storage/' . $dbName . '/' . self::$_IMAGE_ROOT . '/' . $this->image;
            if (file_exists($localPath)) {
                /** @var User $identity */
                $identity = Yii::$app->user->identity;
                $userName = $identity->username;
                $dir = 'storage/' . $userName . '/' . self::$_IMAGE_ROOT . '/'
                    . $this->image;
                return Yii::$app->request->getBaseUrl() . '/' . $dir;
            } else {
                return $noFileUrl;
                // такого в штатном режиме быть не должно!
            }
        }
        return $noFileUrl;
    }

    /**
     * Возвращает каталог в котором должен находится файл изображения,
     * относительно папки web.
     *
     * @return string
     */
    public function getImageDir()
    {
        $dbName = Yii::$app->session->get('user.dbname');
        $dir = 'storage/' . $dbName . '/' . self::$_IMAGE_ROOT . '/';
        return $dir;
    }

    /**
     * @param bool $runValidation
     * @param null $attributeNames
     * @return bool
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        $dirtyTagId = $this->getDirtyAttributes(['tagId']);
        $oldTagId = $this->getOldAttribute('tagId');
        $isNewRecord = $this->isNewRecord;

        if (count($dirtyTagId) == 1) {
            $token = Token::findOne(['tagId' => $oldTagId]);
            if ($token != null) {
                $token->delete();
            }
        }

        $isSaved = parent::save($runValidation, $attributeNames);


        if ($isSaved) {
            // создаём/изменяем связку метки и базы к которой она относится
            /** @var Connection $redis */
            $redis = Yii::$app->get('redis');
            $result = Yii::$app->db->createCommand('select database() as dbname')->query()->read();

            if ($isNewRecord) {
                $redis->set($this->login, $result['dbname']);
                $redis->set($this->tagId, $result['dbname']);
            } else {
                if (count($dirtyTagId) == 1) {
                    $redis->del($oldTagId);
                    $redis->set($this->tagId, $result['dbname']);
                }
            }
        }

        return $isSaved;
    }

    /**
     * @return string|null
     */
    public function getTypeName()
    {
        switch ($this->type) {
            case 0 :
                return 'Не указана';
            case 1 :
                return 'Оператор';
            case 2 :
                return 'Исполнитель';
            case 3 :
                return 'Обе роли';
            default:
                return null;
        }
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        $class = explode('\\', get_class($this));
        $class = $class[count($class) - 1];

        $perm = parent::getPermissions();
        $perm['dashboard'] = 'dashboard' . $class;
        $perm['table'] = 'table' . $class;
        $perm['edit'] = 'edit' . $class;
        $perm['save'] = 'save' . $class;
        $perm['timeline'] = 'timeline' . $class;
        return $perm;
    }
}
