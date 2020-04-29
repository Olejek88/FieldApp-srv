<?php

namespace common\models;

use backend\controllers\EquipmentRegisterController;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\redis\Connection;
use yii\db\ActiveQuery;
use Exception;
use yii\base\InvalidConfigException;


/**
 * This is the model class for table "equipment".
 *
 * @property integer $_id
 * @property string $uuid
 * @property string $equipmentModelUuid
 * @property string $title
 * @property string $criticalTypeUuid
 * @property string $startDate
 * @property double $latitude
 * @property double $longitude
 * @property string $tagId
 * @property string $image
 * @property string $upload
 * @property string $equipmentStatusUuid
 * @property string $inventoryNumber
 * @property string $locationUuid
 * @property string $createdAt
 * @property string $changedAt
 * @property string $parentEquipmentUuid
 * @property string $imageUrl
 * @property string $imageDir
 * @property boolean $deleted
 * @property string $serialNumber
 *
 * @property EquipmentStatus $equipmentStatus
 * @property EquipmentModel $equipmentModel
 * @property Objects $location
 * @property CriticalType $criticalType
 * @property Defect[] $defects
 * @property UserEquipment[] $equipmentUsers
 * @property Stage $lastStage
 * @property Documentation[] $documentations
 */
class Equipment extends ToirusModel
{
    private static $_IMAGE_ROOT = 'equipment';
    public $upload;

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
        return 'equipment';
    }

    /**
     * Свойства объекта со связанными данными.
     *
     * @return array
     */
    public function fields()
    {
        return ['_id', 'uuid',
            'equipmentModelUuid',
            'equipmentModel' => function ($model) {
                return $model->equipmentModel;
            },
            'equipmentStatusUuid',
            'equipmentStatus' => function ($model) {
                return $model->equipmentStatus;
            },
            'title', 'inventoryNumber', 'serialNumber',
            'locationUuid',
            'location' => function ($model) {
                return $model->location;
            },
            'criticalTypeUuid',
            'criticalType' => function ($model) {
                return $model->criticalType;
            }, 'startDate', 'latitude', 'longitude',
            'tagId', 'image', 'createdAt', 'changedAt'
        ];
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
                    'equipmentModelUuid',
                    'title',
                    'criticalTypeUuid',
                    'tagId',
                    'equipmentStatusUuid',
                    'locationUuid'
                ],
                'required'
            ],
            [['tagId'], 'unique'],
            [['startDate', 'createdAt', 'changedAt'], 'safe'],
            [['latitude', 'longitude'], 'number'],
            [['upload'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg'],
            [
                [
                    'uuid',
                    'equipmentModelUuid',
                    'criticalTypeUuid',
                    'tagId',
                    'equipmentStatusUuid',
                    'serialNumber',
                    'inventoryNumber'
                ],
                'string', 'max' => 50
            ],
            [['title', 'locationUuid'], 'string', 'max' => 100],
            ['tagId', 'unique', 'targetClass' => '\common\models\Equipment', 'message' => 'Эта метка уже используется.'],
            ['tagId', 'checkUniqueGlobal',], //Срабатывет на кеш в Редисе
            [
                ['locationUuid'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Objects::class,
                'targetAttribute' => ['locationUuid' => 'uuid']
            ],
            [
                [
                    'uuid',
                    'equipmentModelUuid',
                    'title',
                    'criticalTypeUuid',
                    'tagId',
                    'image',
                    'equipmentStatusUuid',
                    'inventoryNumber',
                    'locationUuid',
                    'serialNumber',
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
        $serialNumber = 'Серийный номер';
        if (isset(Yii::$app->params['style'])) {
            $style = Yii::$app->params['style'];
            if ($style == 'quarzwerke') {
                $serialNumber = 'AKS-код';
            }
        }

        return [
            '_id' => Yii::t('app', '№'),
            'uuid' => Yii::t('app', 'Uuid'),
            'equipmentModelUuid' => Yii::t('app', 'Модель оборудования'),
            'equipmentModel' => Yii::t('app', 'Модель'),
            'title' => Yii::t('app', 'Название'),
            'criticalTypeUuid' => Yii::t('app', 'Критичность'),
            'startDate' => Yii::t('app', 'Дата установки'),
            'latitude' => Yii::t('app', 'Широта'),
            'longitude' => Yii::t('app', 'Долгота'),
            'tagId' => Yii::t('app', 'Тег'),
            'image' => Yii::t('app', 'Фотография'),
            'upload' => Yii::t('app', 'Файл'),
            'equipmentStatusUuid' => Yii::t('app', 'Статус'),
            'inventoryNumber' => Yii::t('app', 'Инвентарный'),
            'locationUuid' => Yii::t('app', 'Локация'),
            'parentEquipmentUuid' => Yii::t(
                'app', 'Uuid родительского оборудования'
            ),
            'serialNumber' => Yii::t('app', $serialNumber),
            'createdAt' => Yii::t('app', 'Создан'),
            'changedAt' => Yii::t('app', 'Изменен'),
        ];
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
     * Объект связанного поля.
     *
     * @return ActiveQuery
     */
    public function getCriticalType()
    {
        return $this->hasOne(
            CriticalType::class, ['uuid' => 'criticalTypeUuid']
        );
    }

    /**
     * Объект связанного поля.
     *
     * @return ActiveQuery
     */
    public function getEquipmentStatus()
    {
        return $this->hasOne(
            EquipmentStatus::class, ['uuid' => 'equipmentStatusUuid']
        );
    }

    /**
     * Объект связанного поля.
     *
     * @return ActiveQuery
     */
    public function getEquipmentModel()
    {
        return $this->hasOne(
            EquipmentModel::class, ['uuid' => 'equipmentModelUuid']
        );
    }

    /**
     * Объект связанного поля.
     *
     * @return ActiveQuery
     */
    public function getLocation()
    {
        return $this->hasOne(Objects::class, ['uuid' => 'locationUuid']);
    }

    /**
     * Объект связанного поля.
     *
     * @return ActiveQuery
     */
    /*
    public function getObjects()
    {
        return $this->hasOne(Objects::class, ['uuid' => 'locationUuid']);
    }*/

    /**
     * URL изображения.
     *
     * @return string
     */
    public function getImageUrl()
    {
        $noImage = '/storage/order-level/no-image-icon-4.png';
        if ($this->image == '') {
            /** @var EquipmentModel $em */
            $em = $this->equipmentModel;
            if (is_null($em))
                return $noImage;
            return $em->getImageUrl();
        }

        $dbName = Yii::$app->session->get('user.dbname');
        $typeUuid = $this->equipmentModelUuid;
        $localPath = 'storage/' . $dbName . '/' . self::$_IMAGE_ROOT . '/'
            . $typeUuid . '/' . $this->image;
        if (file_exists(Yii::getAlias($localPath))) {
            /** @var User $identity */
            $identity = Yii::$app->user->identity;
            $userName = $identity->username;
            $dir = 'storage/' . $userName . '/' . self::$_IMAGE_ROOT . '/'
                . $typeUuid . '/' . $this->image;
            $url = Yii::$app->request->getBaseUrl() . '/' . $dir;
        } else {
            /** @var EquipmentModel $em */
            $em = $this->equipmentModel;
            if (is_null($em))
                return $noImage;
            $url = $em->getImageUrl();
        }
        return $url;
    }

    /**
     * Возвращает каталог в котором должен находится файл изображения,
     * относительно папки web.
     *
     * @return string
     */
    public function getImageDir()
    {
        $typeUuid = $this->equipmentModelUuid;
        $dbName = Yii::$app->session->get('user.dbname');
        $dir = 'storage/' . $dbName . '/' . self::$_IMAGE_ROOT . '/'
            . $typeUuid . '/';
        return $dir;
    }

    /**
     * Возвращает каталог в котором должен находится файл изображения,
     * относительно папки web.
     *
     * @param string $typeUuid Uuid типа операции
     *
     * @return string
     */
    public function getImageDirType($typeUuid)
    {
        $dbName = Yii::$app->session->get('user.dbname');
        $dir = 'storage/' . $dbName . '/' . self::$_IMAGE_ROOT . '/'
            . $typeUuid . '/';
        return $dir;
    }

    /**
     * @param bool $runValidation
     * @param null $attributeNames
     * @return bool
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        $dirtyTagId = $this->getDirtyAttributes(['tagId']);
        $oldTagId = $this->getOldAttribute('tagId');
        $isNewRecord = $this->isNewRecord;

        $isSaved = parent::save($runValidation, $attributeNames);

        if ($isSaved) {
            // создаём/изменяем связку метки и базы к которой она относится
            /** @var Connection $redis */
            $redis = Yii::$app->get('redis');
            $result = Yii::$app->db->createCommand('select database() as dbname')->query()->read();
            if ($isNewRecord) {
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
     * @return array
     */
    public function getPermissions()
    {
        $class = explode('\\', get_class($this));
        $class = $class[count($class) - 1];

        $perm = parent::getPermissions();
        $perm['move'] = 'move' . $class;
        $perm['remove-user'] = 'remove-user' . $class;
        $perm['delete-image'] = 'delete-image' . $class;
        $perm['rename'] = 'rename' . $class;
        $perm['remove'] = 'remove' . $class;
        $perm['delete'] = 'delete' . $class;
        $perm['restore'] = 'restore' . $class;
        $perm['operations'] = 'operations' . $class;
        $perm['status'] = 'status' . $class;
        $perm['serial'] = 'serial' . $class;
        $perm['select-task'] = 'select-task' . $class;
        $perm['new'] = 'new' . $class;
        $perm['edit'] = 'edit' . $class;
        $perm['save'] = 'save' . $class;
        $perm['validation'] = 'validation' . $class;
        $perm['measures-list'] = 'measures-list' . $class;
        $perm['search-form'] = 'search-form' . $class;
        $perm['contragents'] = 'contragents' . $class;
        $perm['set-contragent'] = 'set-contragent' . $class;
        $perm['edit-table'] = 'edit-table' . $class;
        $perm['new-table'] = 'new-table' . $class;
        $perm['get-model'] = 'get-model' . $class;
        $perm['get-equipment'] = 'get-equipment' . $class;
        $perm['get-repair-parts'] = 'get-repair-parts' . $class;
        $perm['equipment-copy'] = 'equipment-copy' . $class;
        $perm['repair-parts'] = 'repair-parts' . $class;
        return $perm;
    }

    /**
     * @return ActiveQuery
     */
    public function getDefects()
    {
        return $this->hasMany(Defect::class, ['equipmentUuid' => 'uuid']);
    }

    /**
     * @return ActiveQuery
     */
    public function getEquipmentUsers()
    {
        return $this->hasMany(UserEquipment::class, ['equipmentUuid' => 'uuid'])->with('user');
    }

    /**
     * @return ActiveQuery
     */
    public function getDocumentations()
    {
        return $this->hasMany(Documentation::class, ['equipmentUuid' => 'uuid'])
            ->orWhere(['equipmentModelUuid' => $this->equipmentModelUuid]);
    }

    /**
     * @param $insert
     * @param $changedAttributes
     * @return ActiveQuery
     */
    public function afterSave($insert, $changedAttributes)
    {
        //$attributes = ['equipmentModelUuid','title','startDate','tagId','inventoryNumber','locationUuid','serialNumber'];
        if (isset($changedAttributes['serialNumber'])) {
            EquipmentRegisterController::addEquipmentRegister($this->uuid,
                EquipmentRegisterType::CHANGE_PROPERTIES, $changedAttributes['serialNumber'], $this->serialNumber);
        }
        if (isset($changedAttributes['equipmentModelUuid'])) {
            EquipmentRegisterController::addEquipmentRegister($this->uuid,
                EquipmentRegisterType::CHANGE_PROPERTIES, $changedAttributes['equipmentModelUuid'], $this->equipmentModel->title);
        }
        if (isset($changedAttributes['startDate'])) {
            EquipmentRegisterController::addEquipmentRegister($this->uuid,
                EquipmentRegisterType::CHANGE_PROPERTIES, $changedAttributes['startDate'], $this->startDate);
        }
        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return ActiveQuery
     */
    public function getLastStage()
    {
        return $this->hasOne(Stage::class, ['equipmentUuid' => 'uuid'])
            ->orderBy('changedAt DESC')
            ->with(['stageTemplate']);
    }

    /**
     * URL изображения.
     *
     * @param $equipment
     * @return string
     */
    public static function getImageUrlS($equipment)
    {
        if ($equipment['image'] == '') {
            return EquipmentModel::getImageUrlS($equipment['equipmentModel']);
        }

        $dbName = Yii::$app->session->get('user.dbname');
        $typeUuid = $equipment['equipmentModelUuid'];
        $localPath = 'storage/' . $dbName . '/' . self::$_IMAGE_ROOT . '/'
            . $typeUuid . '/' . $equipment['image'];
        if (file_exists(Yii::getAlias($localPath))) {
            /** @var User $identity */
            $identity = Yii::$app->user->identity;
            $userName = $identity->username;
            $dir = 'storage/' . $userName . '/' . self::$_IMAGE_ROOT . '/'
                . $typeUuid . '/' . $equipment['image'];
            $url = Yii::$app->request->getBaseUrl() . '/' . $dir;
        } else {
            $url = EquipmentModel::getImageUrlS($equipment['equipmentModel']);
        }
        return $url;
    }

    /**
     * Process deletion of image
     *
     * @return boolean the status of deletion
     * @throws InvalidConfigException
     */
    public function deleteImage()
    {
        //$file = $this->getImageUrl();
        $this->image = null;
        $this->save();
        // сначала убираем, потом пытаемся удалить
        // TODO проверить с Lua
        // check if file exists on server
        /*        if (empty($file) || !file_exists($file)) {
                    return false;
                }

                // check if uploaded file can be deleted on server
                if (!unlink($file)) {
                    return false;
                }*/
        return true;
    }
}
