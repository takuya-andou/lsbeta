<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\forecast\models\Forecast */

$this->title = 'Create Forecast';
$this->params['breadcrumbs'][] = ['label' => 'Forecasts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="forecast-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
