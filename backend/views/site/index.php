<?php

/* @var $accountUser */
/* @var $orders */
/* @var $equipment */
/* @var $coordinates */
/* @var $js */
/* @var $js2 */
/* @var $gps */
/* @var $objectsList */
/* @var $objectsGroup */
/* @var $defectList */
/* @var $defectGroup */
/* @var $measureList */
/* @var $measureGroup */
/* @var $usersList */
/* @var $equipmentsList */
/* @var $equipmentsGroup */
/* @var $equipmentsErrorList */
/* @var $equipmentsErrorGroup */
/* @var $ways */
/* @var $users */
/* @var $usersGroup */
/* @var $wayUsers */

$this->title = Yii::t('app', 'ТОИРУС::Карта');
//LeafLetAsset::register($this);
\dosamigos\leaflet\LeafLetAsset::register($this);
$this->registerJs('$(window).on("resize", function () { $("#mapid").height($(window).height()-40); }).trigger("resize");');
 ?>

<div id="page-preloader">
    <div class="cssload-preloader cssload-loading">
    	<span class="cssload-slice"></span>
    	<span class="cssload-slice"></span>
    	<span class="cssload-slice"></span>
    	<span class="cssload-slice"></span>
    	<span class="cssload-slice"></span>
    	<span class="cssload-slice"></span>
    </div>
</div>

<div class="box-relative">
    <div class="control-pnel-user">
        <?php
            //if (\Yii::$app->user->can(User::PERMISSION_ADMIN) || \Yii::$app->user->can(User::PERMISSION_OPERATOR))
                //echo $this->render('navbar');
        ?>
    </div>
    <?php
        //echo $this->render('navsearch');
    ?>
    <div id="mapid" style="width: 100%; height: 800px"></div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        /*
                var userIcon = L.icon({
                    iconUrl: '/images/position_worker.png',
                    iconSize: [32, 52],
                    iconAnchor: [14, 52],
                    popupAnchor: [-3, -76]
                });
                var equipmentIcon = L.icon({
                    iconUrl: '/images/marker_equipment.png',
                    iconSize: [32, 52],
                    iconAnchor: [14, 52],
                    popupAnchor: [-3, -76]
                });
                var equipmentErrorIcon = L.icon({
                    iconUrl: '/images/marker_equipment_error.png',
                    iconSize: [32, 52],
                    iconAnchor: [14, 52],
                    popupAnchor: [-3, -76]
                });
                var measureIcon = L.icon({
                    iconUrl: '/images/marker_measure.png',
                    iconSize: [32, 52],
                    iconAnchor: [14, 52],
                    popupAnchor: [-3, -76]
                });
                var objectIcon = L.icon({
                    iconUrl: '/images/marker_object.png',
                    iconSize: [32, 52],
                    iconAnchor: [14, 52],
                    popupAnchor: [-3, -76]
                });
                var defectIcon = L.icon({
                    iconUrl: '/images/marker_defect.png',
                    iconSize: [32, 52],
                    iconAnchor: [14, 52],
                    popupAnchor: [-3, -76]
                });
        */

        var userIcon = L.icon({
            iconUrl: '/images/position_worker_m.png',
            iconSize: [28, 43],
            iconAnchor: [14, 43],
            popupAnchor: [-3, -76]
        });
        var equipmentIcon = L.icon({
            iconUrl: '/images/marker_equipment_m.png',
            iconSize: [28, 43],
            iconAnchor: [14, 43],
            popupAnchor: [-3, -76]
        });
        var equipmentErrorIcon = L.icon({
            iconUrl: '/images/marker_equipment_error_m.png',
            iconSize: [28, 43],
            iconAnchor: [14, 43],
            popupAnchor: [-3, -76]
        });
        var measureIcon = L.icon({
            iconUrl: '/images/marker_measure_m.png',
            iconSize: [28, 43],
            iconAnchor: [14, 43],
            popupAnchor: [-3, -76]
        });
        var objectIcon = L.icon({
            iconUrl: '/images/marker_object_m.png',
            iconSize: [28, 43],
            iconAnchor: [14, 43],
            popupAnchor: [-3, -76]
        });
        var defectIcon = L.icon({
            iconUrl: '/images/marker_defect_m.png',
            iconSize: [28, 43],
            iconAnchor: [14, 43],
            popupAnchor: [-3, -76]
        });
        var photoIcon = L.icon({
            iconUrl: '/images/marker_photo_m.png',
            iconSize: [28, 43],
            iconAnchor: [14, 43],
            popupAnchor: [-3, -76]
        });
        var videoIcon = L.icon({
            iconUrl: '/images/marker_video_m.png',
            iconSize: [28, 43],
            iconAnchor: [14, 43],
            popupAnchor: [-3, -76]
        });

        <?php
        echo $objectsList;
        echo $objectsGroup;
        echo $usersList;
        echo $usersGroup;
        echo $equipmentsList;
        echo $equipmentsGroup;
        echo $equipmentsErrorList;
        echo $equipmentsErrorGroup;
        echo $defectList;
        echo $defectGroup;
        echo $measureList;
        echo $measureGroup;
        echo $ways;
        $cnt = 0;
        foreach ($users as $user) {
            echo $wayUsers[$cnt];
            $cnt++;
        }

        ?>

        var overlayMapsA = {};
        var overlayMapsB = {
            "<?php echo Yii::t('app', 'Пользователи')?>": users,
            "<?php echo Yii::t('app', 'Объекты')?>": objects,
            "<?php echo Yii::t('app', 'Оборудование в порядке')?>": equipments,
            "<?php echo Yii::t('app', 'Оборудование аварийное')?>": equipments2,
            "<?php echo Yii::t('app', 'Дефекты')?>": defects,
            "<?php echo Yii::t('app', 'Измерения')?>": measures,
            "<?php echo Yii::t('app', 'Маршруты:')?>": ways
            <?php
            $cnt = 0;
            foreach ($users as $user) {
                echo ',' . PHP_EOL . '"' . $user['name'] . '": wayUser' . $user["_id"];
                $cnt++;
            }
            ?>
        };

        var map = L.map('mapid', {
            zoomControl: false,
            layers: [users, objects, equipments, equipments2, defects, measures]
        }).setView(<?= $coordinates ?>, 13);

        L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
            maxZoom: 18,
            id: 'mapbox.streets'
        }).addTo(map);

        L.control.layers(overlayMapsA, overlayMapsB, {
            position: 'bottomleft'
        }).addTo(map);

        L.control.zoom({
            position: 'bottomleft'
        }).addTo(map);
    });

</script>
