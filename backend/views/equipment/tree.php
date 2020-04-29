<?php

use wbraganca\fancytree\FancytreeWidget;
use yii\helpers\Html;
use yii\web\JsExpression;

$this->title = Yii::t('app', 'Дерево моделей оборудования');

/* @var array $equipment
 * @var array $users
 * @var string $sq
 */

?>
<table id="tree" style="background-color: white; width: 100%; font-weight: normal">
    <colgroup>
        <col width="*">
        <col width="*">
        <col width="140px">
        <col width="100px">
        <col width="90px">
        <col width="130px">
        <col width="140px">
        <col width="85px">
        <col width="120px">
    </colgroup>
    <thead style="color: white" class="thead_tree">
    <tr>
        <!--        <th colspan="1">
            <?php
        /*            try {
                        echo Select2::widget([
                            'id' => 'user_select',
                            'name' => 'user_select',
                            'language' => Yii::t('app', 'ru'),
                            'data' => $users,
                            'options' => ['placeholder' => 'Выберите исполнителя...'],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ]);
                    } catch (Exception $e) {

                    }
                    */ ?>
        </th>
        <th align="center" colspan="1" style="background-color: #3c8dbc; color: whitesmoke">
            <button class="btn btn-success" type="button" id="addButton" style="padding: 5px 10px">
                <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
            </button>
            &nbsp
            <button class="btn btn-danger" type="button" id="removeButton" style="padding: 5px 10px">
                <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
            </button>
        </th>
-->
        <th align="center">
            <?=
            Html::a('<button class="btn btn-success" type="button" id="addButton" style="padding: 1px 7px"
                    title="Добавить тип оборудования"><span class="fa fa-plus" aria-hidden="true"></span></button>',
                ['/equipment-type/new-type'],
                ['data-toggle' => 'modal', 'data-target' => '#modalAddEquipmentType']);
            ?>
            <button class="btn btn-info" type="button" id="expandButton" style="padding: 1px 5px"
                    title="<?php echo Yii::t('app', 'Развернуть по уровням') ?>">
                <span class="glyphicon glyphicon-expand" aria-hidden="true"></span>
            </button>
            <button class="btn btn-info" type="button" id="expandButton2" style="padding: 1px 5px"
                    title="<?php echo Yii::t('app', 'Развернуть по уровням глубже') ?>">
                <span class="glyphicon glyphicon-expand" aria-hidden="true"></span>
            </button>
            <button class="btn btn-info" type="button" id="collapseButton" style="padding: 1px 5px" title="
            <?php echo Yii::t('app', 'Свернуть') ?>">
                <span class="glyphicon glyphicon-collapse-down" aria-hidden="true"></span>
            </button>
        </th>
        <td style="vertical-align: center">
            <form action="" class="form-inline">
                <table style="vertical-align: center">
                    <tr>
                        <td>
                            <?= Html::textInput('sq', $sq, [
                                'class' => 'form-control',
                                'id' => 'search',
                                'style' => 'background-color: white',
                            ]); ?>
                        </td>
                        <td>
                            <?php
                            if (!empty($sq)) {
                                echo '<a class="btn btn-info" href="/equipment/tree">x</a>';
                            }
                            ?>
                            <button class="btn btn-info" type="submit">
                                <?php echo Yii::t('app', 'Искать') ?></button>
                        </td>
                    </tr>
                </table>
            </form>
        </td>
        <th align="center" colspan="7" class="thead_tree"
            style="color: whitesmoke"><?php echo Yii::t('app', 'Оборудование системы') ?>
        </th>
    </tr>
    <tr style="color: whitesmoke" class="thead_tree">
        <th align="center"><?php echo Yii::t('app', 'Оборудование') ?></th>
        <th><?php echo Yii::t('app', 'Расположение') ?></th>
        <th><?php echo Yii::t('app', 'Задачи') ?></th>
        <th><?php echo Yii::t('app', 'Исполнители') ?></th>
        <th><?php echo Yii::t('app', 'Инвентарный') ?></th>
        <th><?php echo Yii::t('app', 'Серийный номер') ?></th>
        <th><?php echo Yii::t('app', 'Статус') ?></th>
        <th><?php echo Yii::t('app', 'Дата ввода') ?></th>
        <th><?php echo Yii::t('app', 'Действия') ?></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td></td>
        <td class="alt"></td>
        <td class="center"></td>
        <td class="alt"></td>
        <td class="center"></td>
        <td class="alt"></td>
        <td class="center"></td>
        <td class="alt"></td>
        <td class="center"></td>
    </tr>
    </tbody>
</table>
<div class="modal remote fade" id="modalChange">
    <div class="modal-dialog">
        <div class="modal-content loader-lg"></div>
    </div>
</div>
<?php
$this->registerJsFile('/js/custom/modules/list/jquery.fancytree.contextMenu.js', ['depends' => ['wbraganca\fancytree\FancytreeAsset']]);
$this->registerJsFile('/js/custom/modules/list/jquery.contextMenu.min.js',
    ['depends' => ['yii\jui\JuiAsset']]);
$this->registerCssFile('/css/custom/modules/list/ui.fancytree.css');
$this->registerCssFile('/css/custom/modules/list/jquery.contextMenu.min.css');
try {
    echo FancytreeWidget::widget([
        'options' => [
            'id' => 'tree',
            'source' => $equipment,
            'checkbox' => true,
            'keyboard' => false,
            'selectMode' => 2,
            'extensions' => ['dnd', 'table', 'contextMenu'],
            'dnd' => [
                'preventVoidMoves' => true,
                'preventRecursiveMoves' => false,
                'preventSameParent' => false,
                'preventRecursion' => false,
                'autoExpandMS' => 400,
                'dragStart' => new JsExpression('function(node, data) {
				return true;
			}'),
                'dragEnter' => new JsExpression('function(node, data) {
				return true;
			}'),
                'dragDrop' => new JsExpression('function(node, data) {
				if (data.otherNode.selected) {
				    $.ajax({
                        url: "../equipment/equipment-copy",
                        type: "post",
                        data: {
				            from: data.otherNode.data.uuid,
				            model: node.data.model_uuid				        
                        },
                        success: function (code) {
                            var message = JSON.parse(code);
				            if (message.code == 0) {
    				            newNode = data.otherNode.copyTo(node, data.hitMode);
    				            console.log(newNode);
    				            newNode.data.uuid = message.message.uuid;
    				            newNode.data.key = message.message._id;
    				            newNode.key = message.message._id;
    				            newNode.setTitle(message.message.title);
    				            newNode.data = message.message.data;
    				            newNode.render(true);
				            }
				            else {				                     
				                alert (message.message);
				            }
                        }
                    });
                }
			}'),
            ],
            'edit' => [
                'triggerStart' => ["clickActive", "dblclick", "f2", "mac+enter", "shift+click"],
                'save' => new JsExpression('function(event, data) {
                            setTimeout(function(){
                                $(data.node.span).removeClass("pending");
                                data.node.setTitle(data.node.title);
                            }, 2000);
                            return true;
                        }'),
                'close' => new JsExpression('function(event, data) {
                            if(data.save) {
                                 $(data.node.span).addClass("pending");
                                 $.ajax({
                                    url: "rename",
                                    type: "post",
                                    data: {
                                      uuid: data.node.key,
                                      folder: data.node.folder,
                                      param: data.node.title                                            
                                    },
                                    success: function (data) {
                                       }
                                 });
                            }
                        }')
            ],
            'contextMenu' => [
                'menu' => [
                    'new' => [
                        'name' => Yii::t('app', 'Добавить новое'),
                        'icon' => 'add',
                        'callback' => new JsExpression('function(key, opt) {
                        var node = $.ui.fancytree.getNode(opt.$trigger);
                        if (node.folder==true) {
                            $.ajax({
                                url: "new",
                                type: "post",
                                data: {
                                    selected_node: node.key,
                                    folder: node.folder,
                                    uuid: node.data.uuid,
                                    type: node.type,
                                    model_uuid: node.data.model_uuid,
                                    type_uuid: node.data.type_uuid,
                                    reference: "equipment"                                                                        
                                },
                                success: function (data) { 
                                    $(\'#modalAddEquipment\').modal(\'show\');
                                    $(\'#modalContentEquipment\').html(data);
                                }
                           }); 
                        }                        
                    }')
                    ],
                    'edit' => [
                        'name' => Yii::t('app', 'Редактировать'),
                        'icon' => 'edit',
                        'callback' => new JsExpression('function(key, opt) {
                        var node = $.ui.fancytree.getNode(opt.$trigger);
                            $.ajax({
                                url: "edit",
                                type: "get",
                                data: {
                                    selected_node: node.key,
                                    folder: node.folder,
                                    uuid: node.data.uuid,
                                    type: node.type,
                                    model_uuid: node.data.model_uuid,
                                    type_uuid: node.data.type_uuid,
                                    reference: "equipment"                                                                        
                                },
                                success: function (data) {
                                     if (data.length>400) {
                                       $(\'#modalAddEquipment\').modal(\'show\');
                                       $(\'#modalContentEquipment\').html(data);
                                     } else {
                                         var message = JSON.parse(data);
	    			                     if (message.code == -1) {
                                           alert(message.message);
                                         }
                                    }
                                }
                           }); 
                    }')
                    ],
                    'doc' => [
                        'name' => Yii::t('app', 'Добавить документацию'),
                        'icon' => 'add',
                        'callback' => new JsExpression('function(key, opt) {
                            var node = $.ui.fancytree.getNode(opt.$trigger);
                            $.ajax({
                                url: "../documentation/add",
                                type: "post",
                                data: {
                                    selected_node: node.key,
                                    folder: node.folder,
                                    uuid: node.data.uuid,
                                    model_uuid: node.data.model_uuid                                    
                                },
                                success: function (data) { 
                                    $(\'#modalAddDocumentation\').modal(\'show\');
                                    $(\'#modalContent\').html(data);
                                }
                            });
                    }')
                    ],
                    'defect' => [
                        'name' => Yii::t('app', 'Добавить дефект'),
                        'icon' => 'add',
                        'callback' => new JsExpression('function(key, opt) {
                            var node = $.ui.fancytree.getNode(opt.$trigger);
                            $.ajax({
                                url: "../defect/add",
                                type: "post",
                                data: {
                                    selected_node: node.key,
                                    folder: node.folder,
                                    uuid: node.data.uuid,
                                    model_uuid: node.data.model_uuid                                    
                                },
                                success: function (data) { 
                                    $(\'#modalAddDefect\').modal(\'show\');
                                    $(\'#modalContentDefect\').html(data);
                                }
                            });
                    }')
                    ],
                    'delete' => [
                        'name' => Yii::t('app', 'Удалить'),
                        'icon' => "delete",
                        'callback' => new JsExpression('function(key, opt) {
                            var sel = $.ui.fancytree.getTree().getSelectedNodes();
                            $.each(sel, function (event, data) {
                                var node = $.ui.fancytree.getNode(opt.$trigger);
                                $.ajax({
                                      url: "remove",
                                      type: "post",
                                      data: {
                                          selected_node: data.key,
                                          folder: node.folder,
                                          type: node.type,
                                          uuid: node.data.uuid,
                                          model_uuid: node.data.model_uuid
                                      },
                                      success: function (code) {
                                        var message = JSON.parse(code);
				                        if (message.code == 0) {
                                            data.remove();
                                        } else {
                                            alert (message.message);
                                        }            
                                      }                                    
                                   });
                            });
                         }')
                    ]
                ]
            ],
            'table' => [
                'indentation' => 20,
                "titleColumnIdx" => "1",
                "locationColumnIdx" => "2",
                "tasksColumnIdx" => "3",
                "userColumnIdx" => "4",
                "inventoryColumnIdx" => "5",
                "serialColumnIdx" => "6",
                "statusColumnIdx" => "7",
                "startColumnIdx" => "8",
                "linksColumnIdx" => "9",
            ],
            'renderColumns' => new JsExpression('function(event, data) {
            var node = data.node;
            $tdList = $(node.tr).find(">td");
            $tdList.eq(1).text(node.data.location);
            $tdList.eq(2).html(node.data.tasks);           
            $tdList.eq(3).html(node.data.user);
            $tdList.eq(4).html(node.data.inventory);
            $tdList.eq(5).html(node.data.serial);
            $tdList.eq(6).html(node.data.status);
            $tdList.eq(7).html(node.data.start);
            $tdList.eq(8).html(node.data.links);
        }')
        ]
    ]);
} catch (Exception $e) {

}
?>

<div class="modal remote fade" id="modalDefects">
    <div class="modal-dialog" style="width: 900px">
        <div class="modal-content loader-lg"></div>
    </div>
</div>
<div class="modal remote fade" id="modalTasks">
    <div class="modal-dialog" style="width: 1200px">
        <div class="modal-content loader-lg">
        </div>
    </div>
</div>
<div class="modal remote fade" id="modalRegister">
    <div class="modal-dialog" style="width: 800px">
        <div class="modal-content loader-lg" id="modalRegisterContent">
        </div>
    </div>
</div>
<div class="modal remote fade" id="modalDocumentation">
    <div class="modal-dialog" style="width: 800px">
        <div class="modal-content loader-lg" id="modalDocumentationContent">
        </div>
    </div>
</div>
<div class="modal remote fade" id="modalStatus">
    <div class="modal-dialog" style="width: 250px">
        <div class="modal-content loader-lg" style="margin: 10px; padding: 10px">
        </div>
    </div>
</div>
<div class="modal remote fade" id="modalSN">
    <div class="modal-dialog" style="width: 250px">
        <div class="modal-content loader-lg" style="margin: 10px; padding: 10px">
        </div>
    </div>
</div>
<div class="modal remote fade" id="modalRepairPart">
    <div class="modal-dialog" style="width: 1000px; height: 600px">
        <div class="modal-content loader-lg" style="margin: 10px; padding: 10px">
        </div>
    </div>
</div>
<div class="modal remote fade" id="modalAddTask">
    <div class="modal-dialog" style="width: 800px; height: 400px">
        <div class="modal-content loader-lg" style="margin: 10px; padding: 10px">
        </div>
    </div>
</div>
<div class="modal remote fade" id="modalAttributes">
    <div class="modal-dialog" style="width: 800px">
        <div class="modal-content loader-lg" style="margin: 10px; padding: 10px">
        </div>
    </div>
</div>
<div class="modal remote fade" id="modalAddDocumentation">
    <div class="modal-dialog">
        <div class="modal-content loader-lg" id="modalContent">
        </div>
    </div>
</div>
<div class="modal remote fade" id="modalAddDefect">
    <div class="modal-dialog" style="width: 700px">
        <div class="modal-content loader-lg" id="modalContentDefect">
        </div>
    </div>
</div>
<div class="modal remote" id="modalAddEquipment">
    <div class="modal-dialog">
        <div class="modal-content loader-lg" id="modalContentEquipment">
        </div>
    </div>
</div>
<div class="modal remote fade" id="modalContragents">
    <div class="modal-dialog" style="width: 800px">
        <div class="modal-content loader-lg"></div>
    </div>
</div>
<div class="modal remote fade" id="modal_request">
    <div class="modal-dialog">
        <div class="modal-content loader-lg"></div>
    </div>
</div>

<?php
$this->registerJs('$("#addButton").on("click",function() {
        var sel = $.ui.fancytree.getTree().getSelectedNodes();
        var count = $(sel).length;
        var i = 0;        
        $.each(sel, function (event, data) {
            if (data.folder==false) {
                $.ajax({
                    url: "move",
                    type: "post",
                    data: {
                        selected_node: data.key,
                        user: $("#user_select").val()
                    },
                    success: function (data) {
                        i = i + 1;
                        if (i === count) {
                            window.location.replace("tree");
                        }                    
                    }
                });
            }
        });
    })');

$this->registerJs('$("#removeButton").on("click",function() {
        var sel = $.ui.fancytree.getTree().getSelectedNodes();
        var count = $(sel).length;
        var i = 0;        
        $.each(sel, function (event, data) {
            if (data.folder==false) {
                $.ajax({
                    url: "remove-user",
                    type: "post",
                    data: {
                        selected_node: data.key,
                    },
                    success: function (data) {
                        i = i + 1;
                        if (i === count) {
                            window.location.replace("tree");
                        }                    
                    }                  
                });
            }
        });
    })');

?>

<div class="modal remote fade" id="modalUser">
    <div class="modal-dialog" style="width: 400px; height: 300px">
        <div class="modal-content loader-lg" style="margin: 10px; padding: 10px">
        </div>
    </div>
</div>
<div class="modal remote fade" id="modalAddEquipmentType">
    <div class="modal-dialog" style="width: 500px; height: 400px">
        <div class="modal-content loader-lg"></div>
    </div>
</div>

<?php

$this->registerJs('$("#modalUser").on("hidden.bs.modal",
function () {
     window.location.replace("tree");
})');

$this->registerJs('$("#expandButton").on("click",function() {
    $("#tree").fancytree("getRootNode").visit(function(node){
        if(node.getLevel() < 2) {
            node.setExpanded(true);
        } else node.setExpanded(false);
    });
})');

$this->registerJs('$("#expandButton2").on("click",function() {
    $("#tree").fancytree("getRootNode").visit(function(node){
        if(node.getLevel() < 4) {
            node.setExpanded(true);
        } else node.setExpanded(false);
    });
})');

$this->registerJs('$("#collapseButton").on("click",function() {
    $("#tree").fancytree("getRootNode").visit(function(node){
        if(node.getLevel() < 2) {
            node.setExpanded(false);
        }
    });
})');

$this->registerJs('$("#modalPlanTask").on("hidden.bs.modal",
function () {
    $(this).removeData();
})');

$this->registerJs('$("#modalAddEquipment").on("hidden.bs.modal",
function () {
    $(this).removeData();
})');

$this->registerJs('$("#modalRepairPart").on("hidden.bs.modal",
function () {
    $(this).removeData();
})');

$this->registerJs('$("#modalAddEquipmentType").on("hidden.bs.modal",
 function () {
     window.location.replace("tree");
})');
$this->registerJs('$("#modalFactTask").on("hidden.bs.modal",
function () {
    $(this).removeData();
})');
$this->registerJs('$("#modalContragents").on("hidden.bs.modal",
function () {
    $(this).removeData();
})');
$this->registerJs('$("#modal_request").on("hidden.bs.modal",
function () {
    //window.location.replace("tree");
})');
$this->registerJs('$("#modalRegister").on("hidden.bs.modal",
function () {
    $(this).removeData();
})');
$this->registerJs('$("#modalTasks").on("hidden.bs.modal",
function () {
    $(this).removeData();
})');
$this->registerJs('$("#modalDocumentation").on("hidden.bs.modal",
function () {
    $(this).removeData();
})');
$this->registerJs('$("#modalDefects").on("hidden.bs.modal",
function () {
    $(this).removeData();
})');
$this->registerJs('$("#modalSN").on("hidden.bs.modal",
function () {
     window.location.replace("tree");
})');
$this->registerJs('$("#modal_register").on("hidden.bs.modal",
function () {
     window.location.replace("tree");
})');
$this->registerJs('$("#modalAddDefect").on("hidden.bs.modal",
function () {
    $(this).removeData();
})');

$this->registerJs('$("#modalAddTask").on("hidden.bs.modal",
function () {
    $(this).removeData();
})');

$this->registerJs('$("#modalStatus").on("hidden.bs.modal",
function () {
     window.location.replace("tree");
})');

?>
