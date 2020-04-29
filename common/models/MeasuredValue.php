<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "measured_value".
 *
 * @property integer $_id
 * @property string $uuid
 * @property string $equipmentUuid
 * @property string $operationUuid
 * @property string $date
 * @property string $value
 * @property string $createdAt
 * @property string $changedAt
 * @property string $measureTypeUuid
 *
 * @property MeasureType $measureType
 * @property Operation $operation
 * @property Equipment $equipment
 */
class MeasuredValue extends ToirusModel
{
    /**
     * Название таблицы.
     *
     * @return string
     *
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'measured_value';
    }

    /**
     * Rules.
     *
     * @return array
     *
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'uuid',
                    'equipmentUuid',
                    'operationUuid',
                    'measureTypeUuid',
                    'value'
                ],
                'required'
            ],
            [['date', 'createdAt', 'changedAt'], 'safe'],
            [
                [
                    'uuid',
                    'equipmentUuid',
                    'operationUuid',
                    'measureTypeUuid',
                    'value'
                ],
                'string',
                'max' => 45
            ],
            [
                [
                    'uuid',
                    'equipmentUuid',
                    'operationUuid',
                    'measureTypeUuid',
                    'value',
                    'date',
                ],
                'filter', 'filter' => function ($param) {
                return htmlspecialchars($param, ENT_QUOTES | ENT_HTML401);
                }
            ],
        ];
    }

    /**
     * Labels.
     *
     * @return array
     *
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => Yii::t('app', '№'),
            'uuid' => Yii::t('app', 'Uuid'),
            'equipmentUuid' => Yii::t('app', 'Оборудование'),
            'operationUuid' => Yii::t('app', 'Операция'),
            'measureTypeUuid' => Yii::t('app', 'Тип измерения'),
            'date' => Yii::t('app', 'Дата'),
            'value' => Yii::t('app', 'Значение'),
            'createdAt' => Yii::t('app', 'Создан'),
            'changedAt' => Yii::t('app', 'Изменен'),
        ];
    }

    /**
     * Fields.
     *
     * @return array
     */
    public function fields()
    {
        return ['_id', 'uuid',
            'equipment' => function ($model) {
                return $model->equipment;
            },
            'operation' => function ($model) {
                return $model->operation;
            },
            'measureType' => function ($model) {
                return $model->measureType;
            }, 'date', 'value', 'createdAt', 'changedAt'
        ];
    }

    /**
     * Объект связанного поля.
     *
     * @return ActiveQuery
     */
    public function getEquipment()
    {
        return $this->hasOne(Equipment::class, ['uuid' => 'equipmentUuid']);
    }

    /**
     * Объект связанного поля.
     *
     * @return ActiveQuery
     */
    public function getOperation()
    {
        return $this->hasOne(Operation::class, ['uuid' => 'operationUuid']);
    }

    /**
     * Объект связанного поля.
     *
     * @return ActiveQuery
     */
    public function getMeasureType()
    {
        return $this->hasOne(
            MeasureType::class, ['uuid' => 'measureTypeUuid']
        );
    }
}
