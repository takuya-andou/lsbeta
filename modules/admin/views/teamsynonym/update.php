<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\soccer\models\TeamSynonym */

$this->title = 'Update Team Synonym: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Team Synonyms', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="team-synonym-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
