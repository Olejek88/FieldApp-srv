<?php

namespace api\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotAcceptableHttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\HttpException;
use yii\web\Response;

use common\models\Token;
use common\models\Users;

/**
 * Class TokenController
 *
 * @property string $connection
 */
class TokenController extends Controller
{
    public $enableCsrfValidation = false;

    /**
     * Создаёт и возвращает новый токен.
     *
     * @return string Токен.
     * @throws NotAcceptableHttpException
     * @throws HttpException
     * @throws UnauthorizedHttpException
     */
    public function actionIndex()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;
        if (!$request->isPost) {
            throw new NotAcceptableHttpException();
        }

        $tokenType = $request->post('grant_type');
        // TODO: пароль и ид метки в открытом виде гонять не целесообразно,
        // нужно что-то придумать!
        $password = $request->post($tokenType);

        // находим пользователя с таким паролем
        $user = self::getUser($tokenType, $password);
        if ($user == null) {
            throw new UnauthorizedHttpException();
        }

        $token = Token::findOne(['tagId' => $user->tagId]);
        if ($token != null) {
            $start = time();
            $end = $start + 86400;
            $token->accessToken = Token::initToken();
            $token->tokenType = $tokenType;
            $token->expiresIn = $end;
            $token->userName = $user->login;
            $token->issued = date('Y-m-d\TH:i:s', $start);
            $token->expires = date('Y-m-d\TH:i:s', $end);
            $token->save();
        } else {
            // создаём токен
            $token = self::createToken(
                $user->login, $user->tagId, $tokenType
            );

            if ($token == null) {
                throw new HttpException(500, Yii::t('app', 'Ошибка получения токена!'));
            }
        }

        $this->setConnection($user->tagId);

        return $token;
    }

    /**
     * Создаёт новый токен.
     *
     * @param string $login Login пользователя.
     * @param string $tagId Id метки.
     * @param string $tokenType Тип токена (label | password)
     *
     * @return Token | null
     * @throws \Exception
     */
    public static function createToken($login, $tagId, $tokenType)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($login == null || $tagId == null || $tokenType == null) {
            return null;
        }

        $start = time();
        $end = $start + 86400;

        $token = new Token();
        $token->tagId = $tagId;
        $token->accessToken = Token::initToken();
        $token->tokenType = $tokenType;
        $token->expiresIn = $end;
        $token->userName = $login;
        $token->issued = date('Y-m-d\TH:i:s', $start);
        $token->expires = date('Y-m-d\TH:i:s', $end);

        if (!$token->save()) {
            return null;
        }

        return $token;
    }

    /**
     * Возвращает токен по Id метки.
     *
     * @param string $tagId Id метки.
     *
     * @return Token Объект токена.
     */
    public static function getTokenByTagId($tagId)
    {
        $tokens = Token::find()->where(['tagId' => $tagId])->all();
        if (count($tokens) > 1) {
            // TODO: Реализовать уведомление администратора о том что
            // в системе два токена с одной меткой!

            // throw new HttpException(500, 'Tokens with same tagId more than 1!');
            return null;
        } else if (count($tokens) == 1) {
            return $tokens[0];
        } else {
            return null;
        }
    }


    /**
     * Возвращает пользователя по паролю либо метке.
     *
     * @param string $tokenType Тип токена (label, password)
     * @param string $password Ид метки или пароль.
     *
     * @return Users
     * @throws NotAcceptableHttpException
     * @throws HttpException
     */
    public static function getUser($tokenType, $password)
    {
        $condition = null;
        switch ($tokenType) {
            case 'label':
                $condition = ['tagId' => $password];
                break;
            case 'password':
                $condition = ['pass' => $password];
                break;
            default:
                throw new NotAcceptableHttpException();
        }

        // пользователь обязательно должен быть 'активным'
        $condition['active'] = 1;
        $users = Users::find()->where($condition)->all();
        if (count($users) > 1) {
            // TODO: Здесь у нас косяк в случае проверки по паролю,
            // в действительности может быть два и более пользователя
            // с одинаковым паролем. Необходимо решить этот вопрос!
            // TODO: Реализовать уведомление администратора о том что
            // в системе два пользователя с одной меткой!

            //  throw new HttpException(500, 'Duplicate tagId!');
            return null;
        } else if (count($users) == 1) {
            return $users[0];
        } else {
            return null;
        }
    }

    /**
     * Проверяет действителен ли указанный токен.
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

        return Token::isTokenValid($token);
    }

    /**
     * Возвращает токен из переданного запроса.
     *
     * @param \yii\web\Request $request Запрос.
     *
     * @return string Токен.
     */
    public static function getTokenString($request)
    {
        if ($request == null) {
            return null;
        }

        $value = $request->getHeaders()->get('Authorization');
        if ($value == null) {
            return null;
        } else {
            $result = explode('bearer ', $value);
            if (count($result) == 2) {
                return $result[1];
            } else {
                return null;
            }
        }
    }

    /**
     * Получаем пользователя по токену.
     *
     * @param string $token Токен.
     *
     * @return Users Пользователь.
     */
    public static function getUserByToken($token)
    {
        $tokens = Token::find()->where(['accessToken' => $token])->all();
        if (count($tokens) > 1) {
            // TODO: Реализовать уведомление администратора о том что
            // в системе два токена с одной меткой!

            // throw new HttpException(500, 'Tokens with same tagId more than 1!');
            return null;
        } else if (count($tokens) == 1) {
            $user = Users::find()->where(['tagId' => $tokens[0]->tagId])->all();
            return $user[0];
        } else {
            return null;
        }
    }

    /**
     * Устанавливаем текущую дату очердного запроса от пользователя.
     *
     * @param string $tagId Ид метки.
     *
     * @return void
     */
    public function setConnection($tagId)
    {
        $user = Users::findOne(['tagId' => $tagId]);

        if ($user != null) {
            $connect = date('Y-m-d h:i:s');
            $user->connectionDate = $connect;
            // это ошибочное решение,
            // подобная информация должна хранится в отдельной таблице
            //$user->update();
        }
    }
}
