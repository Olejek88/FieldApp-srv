<?php
namespace common\components;
use Yii;

class MyHelpers
{
    /**
     * Возвращает полный путь до файла в локальной файловой системе.
     *
     * @param string $path Путь справа от корня.
     * @param bool   $root От корня.
     *
     * @return string Полный путь.
     */
    public static function getImgLocalPath($path, $root = false)
    {
        $dbName = \Yii::$app->session->get('user.dbname');

        if ($root) {
            $result = '/';
        } else {
            $result = '';
        }
        return $result . "storage/$dbName$path";
    }

    /**
     * Возвращает полный путь до файла на сайте.
     *
     * @param string $path Путь справа от корня.
     * @param bool   $root От корня.
     *
     * @return string Полный путь.
     */
    public static function getImgUrlPath($path, $root = true)
    {
        $userName = \Yii::$app->user->identity->username;

        if ($root) {
            $result = '/';
        } else {
            $result = '';
        }

        return $result . "storage/$userName$path";
    }

    public static function getImgRemotePath($path)
    {
        $dbName = \Yii::$app->session->get('user.dbname');
        $localPath = 'storage/' . $dbName . $path;
        $url="";
        if (file_exists(Yii::getAlias($localPath))) {
            $identity = \Yii::$app->user->identity;
            $userName = $identity->username;
            $dir = 'storage/' . $userName . $path;
            $url = Yii::$app->request->BaseUrl . '/' . $dir;
        }
        return $url;
    }

/**
     * Возвращает полный путь до файла на сайте,
     * если файла нет, возвращает no-image изображение.
     *
     * @param string $path Путь справа от корня.
     *
     * @return string Полный путь.
     */
    public static function getImgUrl($path)
    {
        if (file_exists(self::getImgLocalPath($path))) {
            $url = MyHelpers::getImgUrlPath($path);
        } else {
            $url = '/storage/order-level/no-image-icon-4.png';
        }

        return $url;
    }

    /**
     * Распознование и форматирование даты
     *
     * @param string $date   Дата в виде строки
     * @param string $format Формат выходной даты
     *
     * @return string
     */
    static function parseFormatDate($date, $format = 'Y-m-d H:i:s')
    {
        $time = strtotime($date);
        if ($time <= 0) {
            // TODO: возвращать null как будет осуществлён переход на nullable поля в базе
            return date($format);
        } else {
            return date($format, $time);
        }
    }

}

