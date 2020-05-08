<?php

use wbraganca\fancytree\FancytreeWidget;
use yii\web\JsExpression;

$this->title = Yii::t('app', 'Дерево измеренные значения');
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
            <th align="center"><?php echo Yii::t('app', 'Тип/канал') ?></th>
            <th>
                <?php echo Yii::t('app', 'Дата измерения') ?></th>
            <th>
                <?php echo Yii::t('app', 'Значение') ?></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td></td>
            <td class="alt"></td>
            <td class="center"></td>
        </tr>
        </tbody>
    </table>
<?php echo FancytreeWidget::widget([
    'options' => [
        'id' => 'tree',
        'source' => $equipment,
        'extensions' => ["glyph", "table"],
        'glyph' => 'glyph_opts',
        'table' => [
            'indentation' => 20,
            "titleColumnIdx" => "1",
            "dateColumnIdx" => "2",
            "valueColumnIdx" => "3",
        ],
        'renderColumns' => new JsExpression('function(event, data) {
            var node = data.node;
            $tdList = $(node.tr).find(">td");
            $tdList.eq(1).text(node.data.date);
            $tdList.eq(2).text(node.data.value);
        }')
    ]
]);
?>