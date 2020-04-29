<?php
namespace common\components;

use common\models\DefectLevel;
use common\models\EquipmentStatus;
use common\models\Gpstrack;
use common\models\Journal;
use common\models\Objects;
use common\models\OperationStatus;
use common\models\OrderStatus;
use common\models\StageStatus;
use common\models\TaskStatus;
use common\models\Users;
use Yii;

/**
 * Class MainFunctions
 */
class MainFunctions
{
    /**
     * return generated UUID
     * @return string generated UUID
     * @throws \Exception
     */
    static function GUID()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            random_int(0, 65535),
            random_int(0, 65535),
            random_int(0, 65535),
            random_int(16384, 20479),
            random_int(32768, 49151),
            random_int(0, 65535),
            random_int(0, 65535),
            random_int(0, 65535));
    }
}

