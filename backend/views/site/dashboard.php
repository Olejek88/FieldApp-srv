<?php
/* @var $measures
 * @var $channels
 * @var $title
 * @var $chart
*/

$this->title = Yii::t('app', 'Сводная');
?>
<div class="row">
    <div class="col-md-6">
        <?php
        echo $this->render('widget-archive', ['provider' => $measures]);
        ?>
    </div>
    <div class="col-md-6">
        <?php
        echo $this->render('widget-channel-table', ['provider' => $channels]);
        ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?php
        echo $this->render('widget-measure', ['title' => $title, 'chart' => $chart]);
        ?>
    </div>
</div>
<footer class="main-footer" style="margin-left: 0 !important;">
    <div class="pull-right hidden-xs" style="vertical-align: middle; text-align: center;">
        <b>Version</b> 1.0.0
    </div>
    <strong>Copyright &copy; 2020 Olejek</strong> Все права на программный продукт защищены.
</footer>
