<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "channel".
 *
 * @property integer $_id
 * @property string $uuid
 * @property string $measureTypeUuid
 * @property string $title
 * @property string $createdAt
 * @property string $changedAt
 *
 * @property MeasureType $measureType
 */
class Channel extends ActiveRecord
{
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
        return 'channel';
    }

    /**
     * Свойства объекта со связанными данными.
     *
     * @return array
     */
    public function fields()
    {
        return ['_id', 'uuid',
            'measureTypeUuid',
            'measureType' => function ($model) {
                return $model->measureType;
            },
            'title', 'createdAt', 'changedAt'
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
                    'measureTypeUuid',
                    'title',
                ],
                'required'
            ],
            [['createdAt', 'changedAt'], 'safe'],
            [
                [
                    'uuid',
                    'measureTypeUuid'
                ],
                'string', 'max' => 50
            ],
            [['title'], 'string', 'max' => 100]
        ];
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
            'measureTypeUuid' => Yii::t('app', 'Тип измерения'),
            'measureType' => Yii::t('app', 'Тип измерения'),
            'title' => Yii::t('app', 'Название канала'),
            'createdAt' => Yii::t('app', 'Создан'),
            'changedAt' => Yii::t('app', 'Изменен'),
        ];
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

    public function getLastMeasure()
    {
        $measure = MeasuredValue::find()->where(['channelUuid' => $this->uuid])->orderBy('date desc')->limit(1)->one();
        if ($measure)
            return $measure['value'];
        else
            return '-';
    }
}
