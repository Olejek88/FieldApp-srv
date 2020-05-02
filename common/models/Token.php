<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "token".
 *
 * @property string $tagId
 * @property string $accessToken
 * @property string $tokenType
 * @property integer $expiresIn
 * @property string $userName
 * @property string $issued
 * @property string $expires
 */
class Token extends ActiveRecord
{
    /**
     * Возвращает имя таблицы для модели.
     *
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName()
    {
        return 'token';
    }

    /**
     * Правила.
     *
     * @inheritdoc
     *
     * @return array
     */
    public function rules()
    {
        return [
            [
                [
                    'tagId',
                    'accessToken',
                    'tokenType',
                    'expiresIn',
                    'userName',
                    'issued',
                    'expires'
                ],
                'required'
            ],
            [['expiresIn'], 'integer'],
            [
                [
                    'tagId',
                    'accessToken',
                    'tokenType',
                    'userName',
                    'issued',
                    'expires'
                ],
                'string', 'max' => 128
            ],
            [['accessToken'], 'unique'],
            [
                [
                    'tagId',
                    'accessToken',
                    'tokenType',
                    'userName',
                    'issued',
                    'expires',
                ],
            ],
        ];
    }

    /**
     * Метки к атрибутам.
     *
     * @inheritdoc
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'tagId' => Yii::t('app', 'Tag ID'),
            'accessToken' => Yii::t('app', 'Токен доступа'),
            'tokenType' => Yii::t('app', 'Тип токена'),
            'expiresIn' => Yii::t('app', 'Истекает(unix)'),
            'userName' => Yii::t('app', 'Имя пользователя'),
            'issued' => Yii::t('app', 'Выпущен'),
            'expires' => Yii::t('app', 'Истекает'),
        ];
    }

    /**
     * Генерируем токен
     *
     * @return string
     * @throws \Exception
     */
    public static function initToken()
    {
        return password_hash(uniqid(random_int(0, mt_getrandmax()), true), PASSWORD_BCRYPT);
    }

    /**
     * Инициализируем время.
     *
     * @return integer
     */
    public function initTime()
    {
        return time();
    }

    /**
     * Инициализируем время.
     *
     * @param integer $val Unix timestamp.
     *
     * @return string
     */
    public function initUnixTime($val)
    {
        return date('Y-m-d\TH:i:s', $val);
    }

    /**
     * Инициализируем время.
     *
     * @param integer $val Unix timestamp.
     *
     * @return string
     */
    public function initUnixTimeOne($val)
    {
        return date('Y-m-d\TH:i:s', $val + 86400);
    }

    /**
     * Проверяет есть ли действующий токен.
     *
     * @param string $token Токен.
     *
     * @return boolean
     */
    public static function isTokenValid($token)
    {
        if ($token == null) {
            return false;
        }

        $result = Token::find()->where(['accessToken' => $token])->all();
        if (count($result) > 1) {
            // TODO: Реализовать уведомление администратора о том что
            // в системе два одинаковых токена!
            return false;
        } else if (count($result) == 1) {
            $valid = $result[0]->expiresIn > time() ? true : false;
            if ($valid) {
                $result[0]->expiresIn = time() + 86400;
                $result[0]->save();
            }

            return $valid;
        } else {
            return false;
        }
    }
}
