<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\assets\AdminAsset;
use app\modules\admin\components\Helper;

AdminAsset::register($this);
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Models';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="model-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Model', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'type_id',
            'event_id',
            'usable',
            'name',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => Helper::filterActionColumn(['view', 'update', 'delete', 'test']),
                'buttons' => [
                    'test' => function($url, $model) {
                        return \yii\helpers\Html::a( '<span class="glyphicon glyphicon-signal"></span>', $url,
                            ['title' => Yii::t('yii', 'Test'), 'data-pjax' => '0']);}
                ]
            ],
        ],
    ]); ?>
</div>
