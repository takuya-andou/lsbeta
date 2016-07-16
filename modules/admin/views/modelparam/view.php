<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\betting\models\ModelParam */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Model Params', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="model-param-view">

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
            'system_name',
            'name',
            'required',
            'default_value',
        ],
    ]) ?>

</div>
