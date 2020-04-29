<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "measured_value".
 *
 * @property integer $_id
 * @property string $uuid
 * @property string $channelUuid
 * @property string $date
 * @property string $value
 * @property string $createdAt
 * @property string $changedAt
 *
 * @property Channel $channel
 */

class MeasuredValue extends ActiveRecord
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
                    'channelUuid',
                    'value'
                ],
                'required'
            ],
            [['date', 'createdAt', 'changedAt'], 'safe'],
            [
                [
                    'uuid',
                    'channelUuid',
                    'value'
                ],
                'string',
                'max' => 45
            ]
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
            'channelUuid' => Yii::t('app', 'Канал'),
            'channel' => Yii::t('app', 'Канал'),
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
            'channel' => function ($model) {
                return $model->channel;
            }, 'date', 'value', 'createdAt', 'changedAt'
        ];
    }

    /**
     * Объект связанного поля.
     *
     * @return ActiveQuery
     */
    public function getChannel()
    {
        return $this->hasOne(Channel::class, ['uuid' => 'channelUuid']);
    }
}
