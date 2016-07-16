<?php

use yii\helpers\Html;
use app\assets\AdminAsset;
AdminAsset::register($this);

/* @var $this yii\web\View */
/* @var $model app\modules\betting\models\Model */

$this->title = 'Create Model';
$this->params['breadcrumbs'][] = ['label' => 'Models', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="model-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'types' => $types,
        'events' => $events,
        'params' => $params,
        'usable_states' => $usable_states
    ]) ?>

</div>
