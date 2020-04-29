<?php

use yii\helpers\Html;
use yii\web\View;
/* @var $this View */

$currentUser = Yii::$app->view->params['currentUser'];
$userImage = $currentUser->getImageUrl();
?>

<header class="main-header">

    <?= Html::a('<span class="logo-mini">F</span><span class="logo-lg">' . Yii::$app->name = '' . Yii::t('app', 'FieldApp') . '</span>',
        Yii::$app->homeUrl, ['class' => 'logo']) ?>
    <nav class="navbar navbar-static-top">
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button" style="padding: 10px 15px">
        </a>
        <div class="navbar-custom-menu" style="padding-top: 0; padding-bottom: 0">
            <ul class="nav navbar-nav">
                <?php
                        //echo $this->render('header_timeline');
                ?>
                <?php
                    // echo $this->render('header_events',['events_warning' => $events_warning, 'events_near' => $events_near, 'events_all' => $events_all]);
                ?>
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <?php
                        echo '<img src="' . $userImage . '" class="user-image" alt="U">';
                        ?>
                        <span class="hidden-xs">
                            <?php
                                if ($currentUser) echo $currentUser['name'];
                            ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            <?php
                            echo '<img src="' . $userImage . '" class="img-circle" alt="U">';
                            ?>
                            <p>
                                <?php
                                if ($currentUser) echo $currentUser['name'];
                                ?>
                            </p>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-right">
                                <?= $menuItems[] = Html::beginForm(['/logout'], 'post')
                                    . Html::submitButton(
                                        Yii::t('app', 'Выйти'),
                                        [
                                            'class' => 'btn btn-default btn-flat',
                                            'style' => 'padding: 6px 16px 6px 16px;'
                                        ]
                                    )
                                    . Html::endForm();
                                ?>
                            </div>
                        </li>
                    </ul>
                </li>
                <?php
                    echo $this->render('header_control');
                ?>
            </ul>
        </div>
    </nav>

</header>
