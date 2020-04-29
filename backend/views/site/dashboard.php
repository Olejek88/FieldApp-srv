<?php
/* @var $devices
 * @var $cameras
 * @var $dataProviderRegister
 * @var $measureChart
 * @var $measureTitle
 * @var $tree
 * @var $coordinates
 * @var $categories
 * @var $usersCount
 * @var $currentUser
 * @var $objectsCount
 * @var $objectsTypeCount
 * @var $events
 * @var $reportDataProvider
 * @var $objectsList
 * @var $objectsGroup
 * @var $usersList
 * @var $lightList
 * @var $lightGoodList
 * @var $lightBadList
 * @var $sensorCO2List
 * @var $lightGroup
 * @var $lightGoodGroup
 * @var $lightBadGroup
 * @var $sensorCO2Group
 * @var $camerasGroup
 * @var $camerasList
 * @var $nodesGroup
 * @var $define
 * @var $postCode
 * @var $sensorCO
 * @var $nodesList
 */

use yii\helpers\Html;

$this->title = Yii::t('app', 'Сводная');
if (isset($_GET['type']))
    $type = $_GET['type'];
else $type = 1;
?>

<!-- Main row -->
<div class="row">
    <div class="col-md-12">
        <?= $this->render('widget-map', ['coordinates' => $coordinates,
            'lightList' => $lightList, 'lightGoodList' => $lightGoodList,
            'lightBadList' => $lightBadList, 'sensorCO2List' => $sensorCO2List,
            'lightGroup' => $lightGroup, 'lightGoodGroup' => $lightGoodGroup,
            'lightBadGroup' => $lightBadGroup, 'sensorCO2Group' => $sensorCO2Group,
            'nodesGroup' => $nodesGroup, 'nodesList' => $nodesList,
            'define' => $define, 'postCode' => $postCode,
            'camerasGroup' => $camerasGroup, 'camerasList' => $camerasList]); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?php
        if ($type == 1) {
            echo $this->render('widget-equipment-table', ['devices' => $devices]);
        }
        if ($type == 2) {
            echo $this->render('widget-equipment-camera');
        }
        if ($type == 3) {
            echo $this->render('widget-sensor', ['sensors' => $sensorCO]);
        }
        ?>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <?php

        if ($type == 1) {
            echo $this->render('widget-calendar', ['events' => $events]);
        }
        if ($type == 2) {
            // Заглушка так как у нас нет событий по камерам
            $dataProviderRegister->query->andWhere(['_id' => 0]);
            echo $this->render('widget-register', ['dataProviderRegister' => $dataProviderRegister]);
        }
        if ($type == 3) {
            // Заглушка так как у нас нет событий по датчикам
            $dataProviderRegister->query->andWhere(['_id' => 0]);
            echo $this->render('widget-register', ['dataProviderRegister' => $dataProviderRegister]);
        }
        ?>
    </div>
    <div class="col-md-6">
        <?php
        if ($type == 1) {
            echo $this->render('widget-report', ['reportDataProvider' => $reportDataProvider]);
        }
        if ($type == 2) {
            foreach ($cameras as $camera) {
                echo '<div class="col-md-12">';
                echo $this->render('widget-camera', ['camera' => $camera]);
                echo '</div>';
            }
        }
        if ($type == 3) {
            echo $this->render('widget-measure', ['title' => $measureTitle, 'chart' => $measureChart]);
        }
        ?>
    </div>
</div>

<footer class="main-footer" style="margin-left: 0 !important;">
    <div class="pull-right hidden-xs" style="vertical-align: middle; text-align: center;">
        <b>Version</b> 1.0.2
    </div>
    <?php echo Html::a('<img width="70px" src="images/mtm.png">', 'http://www.mtm-smart.com'); ?>
    <strong>Copyright &copy; 2019 <a href="http://www.mtm-smart.com">MTM Смарт</a>.</strong> Все права на
    программный продукт защищены.
</footer>
