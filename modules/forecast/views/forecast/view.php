<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

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
            'status',
            'date',
            'title',
            'summary',
            'content:ntext',
            'result',
            'bet_id',
            'current_coef',
            'bet_amount',
            'views',
            'update_date',
            'updated_by_user_id',
        ],
    ]) ?>
    <?php Pjax::begin(['enablePushState' => false,
	'timeout' => 4000]); ?>
    <div id="like_rate"><?= isset($rate) ? $rate : 'No rates'; ?></div>
    <?= Html::a("Like",
        ['like', 'id' => $model->id],
        ['class' => 'btn btn-lg btn-primary']) ?>
    <?= Html::a("Dislike",
        ['dislike', 'id' => $model->id],
        ['class' => 'btn btn-lg btn-primary']) ?>
    <?php Pjax::end(); ?>
</div>
