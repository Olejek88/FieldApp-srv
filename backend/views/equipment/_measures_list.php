<?php

/* @var $measures common\models\MeasuredValue */
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title text-center"><?php echo Yii::t('app', 'Измерения') ?></h4>
</div>
<div class="modal-body">
    <table class="table table-striped table-hover text-left">
        <thead>
        <tr>
            <th>#</th>
            <th><?php echo Yii::t('app', 'Время') ?></th>
            <th><?php echo Yii::t('app', 'Оборудование') ?></th>
            <th><?php echo Yii::t('app', 'Задача') ?></th>
            <th><?php echo Yii::t('app', 'Значение') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($measures as $measure): ?>
            <tr>
                <td><?= $measure['_id'] ?></td>
                <td><?= $measure['date'] ?></td>
                <td><?= $measure['equipment']['title'] ?></td>
                <td><?= $measure['operation']['stage']['stageTemplate']['title'] ?></td>
                <td><?= $measure['value'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
