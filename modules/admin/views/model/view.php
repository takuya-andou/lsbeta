<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\assets\AdminAsset;
use yii\grid\GridView;

AdminAsset::register($this);
/* @var $this yii\web\View */
/* @var $model app\modules\betting\models\Model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Models', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="model-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Test', ['test', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
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
            'type_id',
            'event_id',
            'usable',
            'name',
        ],
    ]) ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'label'=>'Param',
                'value'=>function ($data) {
                    return $data->param->name;
                },
            ],
            'value',



        ],
    ]); ?>


</div>
