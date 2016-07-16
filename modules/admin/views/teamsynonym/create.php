<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\soccer\models\TeamSynonym */

$this->title = 'Create Team Synonym';
$this->params['breadcrumbs'][] = ['label' => 'Team Synonyms', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="team-synonym-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
