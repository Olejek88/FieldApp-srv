<?php
/* @var $equipment Equipment */
/* @var string $model_uuid */
/* @var string $reference */
/* @var $equipmentModels array */
/* @var Objects[] $objects */
/* @var string $object_uuid */

use common\components\MainFunctions;
use common\models\CriticalType;
use common\models\Equipment;
use common\models\EquipmentModel;
use common\models\EquipmentStatus;
use common\models\Objects;
use common\models\Settings;
use dosamigos\leaflet\layers\Marker;
use dosamigos\leaflet\layers\TileLayer;
use dosamigos\leaflet\LeafLet;
use dosamigos\leaflet\plugins\geocoder\GeoCoder;
use dosamigos\leaflet\plugins\geocoder\ServiceNominatim;
use dosamigos\leaflet\types\Icon;
use dosamigos\leaflet\types\LatLng;
use dosamigos\leaflet\widgets\Map;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use kartik\widgets\FileInput;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

?>

<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => true,
    'validationUrl' => Url::toRoute('/equipment/validation'),
    'action' => '../equipment/save',
    'options' => [
        'id' => 'form2',
        'enctype' => 'multipart/form-data'
    ]]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?php echo Yii::t('app', 'Редактировать оборудование') ?></h4>
</div>
<div class="modal-body">
    <?php
    $latDefault = 55.160374;
    $lngDefault = 61.402738;

    $ulyanovsk = Settings::getSettings(Settings::SETTING_ULYANOVSK);
    if ($ulyanovsk) {
        $latDefault = 53.9656921;
        $lngDefault = 48.3384054;
    }
    if (isset ($equipment) && $equipment['uuid']) {
        echo Html::hiddenInput("equipmentUuid", $equipment['uuid']);
        echo Html::hiddenInput("reference", $reference);

        echo $form->field($equipment, 'uuid')
            ->hiddenInput(['value' => $equipment['uuid']])
            ->label(false);
    } else {
        echo Html::hiddenInput("reference", $reference);
        echo $form->field($equipment, 'uuid')
            ->hiddenInput(['value' => MainFunctions::GUID()])
            ->label(false);
    }
    echo $form->field($equipment, 'title')->textInput(['maxlength' => true]);

    if ($model_uuid != null) {
        $model = EquipmentModel::find()->where(['uuid' => $model_uuid])->one();
        if ($model) {
            echo $form->field($equipment, 'equipmentModelUuid')->hiddenInput(['value' => $model['uuid']])->label(false);
        }
    } else {
        echo $form->field($equipment, 'equipmentModelUuid')->widget(Select2::class,
            [
                'data' => $equipmentModels,
                'language' => Yii::t('app', 'ru'),
                'options' => [
                    'placeholder' => Yii::t('app', 'Выберите модель..'),
                    'style' => ['height' => '42px', 'padding-top' => '10px']
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]);
    }
    if (!empty($equipment->equipmentStatusUuid)) {
        echo $form->field($equipment, 'equipmentStatusUuid')->hiddenInput(['value' => $equipment['equipmentStatusUuid']])->label(false);
    } else {
        echo $form->field($equipment, 'equipmentStatusUuid')->hiddenInput(['value' => EquipmentStatus::WORK])->label(false);
    }
    //echo $form->field($equipment, 'startDate')->hiddenInput(['value' => date("Ymd")])->label(false);
    echo $form->field($equipment, 'criticalTypeUuid')->hiddenInput(['value' => CriticalType::CRITICAL])->label(false);
    echo $form->field($equipment, 'tagId')->textInput(['maxlength' => true]);

    echo $form->field($equipment, 'upload')->widget(
        FileInput::class,
        ['options' => ['accept' => '*'],
            'pluginOptions' => [
                'removeClass' => 'btn btn-danger',
                'deleteUrl' => Url::toRoute(['equipment/delete-image', 'id' => $equipment->_id]),
                'initialPreview' => [
                    Html::img($equipment->getImageUrl(), ['class' => 'file-preview-image', 'height' => 200])
                ],
            ]
        ]
    );
    echo $form->field($equipment, 'inventoryNumber')->textInput(['maxlength' => true]);
    echo $form->field($equipment, 'serialNumber')->textInput(['maxlength' => true]);

    if ($object_uuid != null) {
        echo $form->field($equipment, 'locationUuid')->hiddenInput(['value' => $object_uuid])->label(false);
        echo Html::hiddenInput("locationUuid", $object_uuid);
    } else {

        //$countItems = count($items);
        //$isItems = $countItems != 0;

        echo $form->field($equipment, 'locationUuid')->widget(Select2::class,
            [
                'data' => $objects,
                'language' => Yii::t('app', 'ru'),
                'options' => [
                    'placeholder' => Yii::t('app', 'Выберите объект..'),
                    'style' => ['height' => '42px', 'padding-top' => '10px']
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ],
                'pluginEvents' => [
                    "select2:select" => "function(data) {
                        $.ajax({
                                url: '../objects/coordinates',
                                type: 'post',
                                data: {
                                    id: data.params.data.id
                                },
                                success: function (data) {
                                    var coordinates = JSON.parse(data);
                                    $('#equipment-latitude').val(coordinates.latitude);
                                    $('#equipment-longitude').val(coordinates.longitude);
                                }
                            });
                  }"]

            ]);
        /* else {
            echo $form->field($equipment, 'locationUuid')->dropDownList([
                '00000000-0000-0000-0000-000000000004' => 'Данных нет']);
        }*/
    }

    $latitude = $equipment->latitude;
    $longitude = $equipment->longitude;

    if ($latitude && $longitude) {
        echo $form->field($equipment, 'latitude')->textInput(['maxlength' => true, 'value' => $latitude])->label(true);
        echo $form->field($equipment, 'longitude')->textInput(['maxlength' => true, 'value' => $longitude])->label(true);
    } else {
        echo $form->field($equipment, 'latitude')->textInput(['maxlength' => true, 'value' => $latDefault])->label(true);
        echo $form->field($equipment, 'longitude')->textInput(['maxlength' => true, 'value' => $lngDefault])->label(true);
    }

    // lets use nominating service
    $nominatim = new ServiceNominatim();

    // create geocoder plugin and attach the service
    $geoCoderPlugin = new GeoCoder([
        'service' => $nominatim,
        'clientOptions' => [
            // we could leave it to allocate a marker automatically
            // but I want to have some fun
            'showMarker' => false,
        ]
    ]);

    // first lets setup the center of our map
    if ($latitude && $longitude)
        $center = new LatLng(['lat' => $latitude, 'lng' => $longitude]);
    else
        $center = new LatLng(['lat' => $latDefault, 'lng' => $lngDefault]);

    // now lets create a marker that we are going to place on our map
    $icon = new Icon(['iconUrl' => '/images/marker-icon.png', 'shadowUrl' => '/images/marker-shadow.png']);

    $marker = new Marker([
        'latLng' => $center,
        'icon' => $icon,
        'name' => 'geoMarker',
        'clientOptions' => [
            'draggable' => true,
            'icon' => $icon,
        ],
        'clientEvents' => [
            'dragend' => 'function(e){
                $("#equipment-latitude").val(e.target._latlng.lat);
                $("#equipment-longitude").val(e.target._latlng.lng);
            }'
        ],
    ]);
    // The Tile Layer (very important)
    $tileLayer = new TileLayer([
//        'urlTemplate' => 'https://a.tile.openstreetmap.org/{z}/{x}/{y}.png',
        'urlTemplate' => 'https://{s}.tiles.mapbox.com/v4/mapquest.streets-mb/{z}/{x}/{y}.{ext}?access_token=pk.eyJ1IjoibWFwcXVlc3QiLCJhIjoiY2Q2N2RlMmNhY2NiZTRkMzlmZjJmZDk0NWU0ZGJlNTMifQ.mPRiEubbajc6a5y9ISgydg',
        'clientOptions' => [
            'attribution' => 'Tiles &copy; <a href="https://www.osm.org/copyright" target="_blank">OpenStreetMap contributors</a> />',
            'subdomains' => '1234',
            'type' => 'osm',
            's' => 'a',
            'ext' => 'png',

        ]
    ]);

    // now our component and we are going to configure it
    $leafLet = new LeafLet([
        'name' => 'geoMap',
        'center' => $center,
        'tileLayer' => $tileLayer,
        'clientEvents' => [
            'geocoder_showresult' => 'function(e){
                // set markers position
                geoMarker.setLatLng(e.Result.center);
                $("#equipment-latitude").val(e.Result.center.lat);
                $("#equipment-longitude").val(e.Result.center.lng);
            }'
        ],
    ]);
    // Different layers can be added to our map using the `addLayer` function.
    $leafLet->addLayer($marker);      // add the marker
    //    $leafLet->addLayer($tileLayer);  // add the tile layer

    // install the plugin
    $leafLet->installPlugin($geoCoderPlugin);

    // finally render the widget
    try {
        echo Map::widget(['leafLet' => $leafLet]);
    } catch (Exception $exception) {
        echo '<div id="map"></div>';
    }

    ?>
    <div class="pole-mg">
        <p style="width: 300px; margin-bottom: 0;"><?php echo Yii::t('app', 'Дата ввода в эксплуатацию') ?></p>
        <?php echo DatePicker::widget(
            [
                'model' => $equipment,
                'attribute' => 'startDate',
                'language' => Yii::t('app', 'ru'),
                'size' => 'ms',
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                ]
            ]
        );
        ?>
    </div>
</div>
<div class="modal-footer">
    <?php echo Html::submitButton(Yii::t('app', 'Отправить'), ['class' => 'btn btn-success']) ?>
    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('app', 'Закрыть') ?></button>
</div>
<script>
    var send = false;
    $(document).on("beforeSubmit", "#form2", function (e) {
        e.preventDefault();
    }).on('submit', "#form2", function (e) {
        e.preventDefault();
        if (!send) {
            send = true;
            var form3 = document.getElementById("form2");
            var fd = new FormData(form3);
            $.ajax({
                type: "post",
                data: fd,
                processData: false,
                contentType: false,
                url: "../equipment/save",
                success: function () {
                    $('#modalAddEquipment').modal('hide');
                },
                error: function () {
                }
            });
        }
    });
</script>
<?php ActiveForm::end(); ?>
