<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\forecast\models\Forecast */

$this->title = 'Update Forecast: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Forecasts', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="forecast-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'upcoming_matches' => $upcoming_matches
    ]) ?>

</div>
