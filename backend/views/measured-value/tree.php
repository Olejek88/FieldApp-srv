<?php

use wbraganca\fancytree\FancytreeWidget;
use yii\web\JsExpression;

$this->title = Yii::t('app', 'Дерево моделей оборудования - измеренные значения');
?>
    <table id="tree">
        <colgroup>
            <col width="*">
            <col width="150px">
            <col width="150px">
            <col width="150px">
            <col width="100px">
            <col width="*">
        </colgroup>
        <thead class="thead_tree">
        <tr>
            <th align="center" colspan="10"><?php echo Yii::t('app', 'Измеренные значения') ?></th>
        </tr>
        <tr>
            <th align="center"><?php echo Yii::t('app', 'Оборудование') ?></th>
            <th>
                <?php echo Yii::t('app', 'Расположение') ?></th>
            <th>
                <?php echo Yii::t('app', 'Параметр') ?></th>
            <th>
                <?php echo Yii::t('app', 'Дата измерения') ?></th>
            <th>
                <?php echo Yii::t('app', 'Значение') ?></th>
            <th>
                <?php echo Yii::t('app', 'Операция') ?></th>
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
        </tr>
        </tbody>
    </table>
<?php echo FancytreeWidget::widget([
    'options' => [
        'id' => 'tree',
        'source' => $equipment,
        'extensions' => ['dnd', "glyph", "table"],
        'glyph' => 'glyph_opts',
        'dnd' => [
            'preventVoidMoves' => true,
            'preventRecursiveMoves' => true,
            'autoExpandMS' => 400,
            'dragStart' => new JsExpression('function(node, data) {
				return true;
			}'),
            'dragEnter' => new JsExpression('function(node, data) {
				return true;
			}'),
            'dragDrop' => new JsExpression('function(node, data) {
				data.otherNode.moveTo(node, data.hitMode);
			}'),
        ],
        'table' => [
            'indentation' => 20,
            "titleColumnIdx" => "1",
            "locationColumnIdx" => "2",
            "parameterColumnIdx" => "3",
            "dateColumnIdx" => "4",
            "valueColumnIdx" => "5",
            "operationColumnIdx" => "6"
        ],
        'renderColumns' => new JsExpression('function(event, data) {
            var node = data.node;
            $tdList = $(node.tr).find(">td");
            $tdList.eq(1).text(node.data.location);
            $tdList.eq(2).html(node.data.parameter);           
            $tdList.eq(3).text(node.data.date);
            $tdList.eq(4).text(node.data.value);
            $tdList.eq(5).html(node.data.operation);
        }')
    ]
]);
?>