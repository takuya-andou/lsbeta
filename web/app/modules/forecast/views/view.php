<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\forecast\models\Forecast */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Forecasts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="forecast-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'match_id',
            'user_id',
            'active',
            'date',
            'title',
            'summary',
            'content:ntext',
            'result',
            'coef',
            'bet_amount',
            'views',
            'update_date',
            'updated_by_user_id',
        ],
    ]) ?>

</div>
