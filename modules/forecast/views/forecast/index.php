<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Forecasts';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="forecast-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Forecast', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'match_id',
            'user_id',
            'status',
            'date',
            // 'title',
            // 'summary',
            // 'content:ntext',
             'result',
            // 'coef',
            // 'bet_amount',
            // 'views',
            // 'update_date',
            // 'updated_by_user_id',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
