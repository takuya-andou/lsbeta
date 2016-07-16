<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\betting\models\ModelParam */

$this->title = 'Update Model Param: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Model Params', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="model-param-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
