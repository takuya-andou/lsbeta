<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use app\components\widgets\Alert;
use app\modules\admin\components\MenuHelper;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => 'My Company',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
   /*$menuItems = [
        ['label' => 'Home', 'url' => ['/main/default/index']],
        ['label' => 'Admin', 'url' => ['/admin/default/index']],
        //['label' => 'Contact', 'url' => ['/site/contact']],
        Yii::$app->user->isGuest ?
            ['label' => 'Login', 'url' => ['/user/default/login']] :
            ['label' => 'Logout (' . Yii::$app->user->identity->username . ')',
                'url' => ['/user/default/logout'],
                'linkOptions' => ['data-method' => 'post']],
        /*['label' => 'App', 'items' => [
            ['label' => 'New Sales', 'url' => ['/sales/pos']],
            ['label' => 'New Purchase', 'url' => ['/purchase/create']],
            ['label' => 'GR', 'url' => ['/movement/create', 'type' => 'receive']],
            ['label' => 'GI', 'url' => ['/movement/create', 'type' => 'issue']],
        ]]*/
    /*];

    $menuItems = Helper::filter($menuItems);*/

    $menuItems = MenuHelper::getAssignedMenu(Yii::$app->user->id);
    $menuItems[] = Yii::$app->user->isGuest ?
        ['label' => 'Login', 'url' => ['/user/default/login']] :
        ['label' => 'Logout (' . Yii::$app->user->identity->username . ')',
            'url' => ['/user/default/logout'],
            'linkOptions' => ['data-method' => 'post']];
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
    ]);
/*
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => array_filter([
            Yii::$app->user->isGuest ?
                false :
                ['label' => 'Admin', 'url' => ['/admin']],
            ['label' => 'Home', 'url' => ['/main/default/index']],
            ['label' => 'Contact', 'url' => ['/contact/default/index']],
            Yii::$app->user->isGuest ?
                ['label' => 'Sign Up', 'url' => ['/user/default/signup']] :
                false,
            Yii::$app->user->isGuest ?
                ['label' => 'Login', 'url' => ['/user/default/login']] :
                ['label' => 'Logout (' . Yii::$app->user->identity->username . ')',
                    'url' => ['/user/default/logout'],
                    'linkOptions' => ['data-method' => 'post']],
        ]),
    ]);*/
    NavBar::end()

    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; My Company <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
