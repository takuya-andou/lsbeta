<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\betting\models\ModelParam */

$this->title = 'Create Model Param';
$this->params['breadcrumbs'][] = ['label' => 'Model Params', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="model-param-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
