<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
$this->registerJs($this->render('_script.js'));

/* @var $this yii\web\View */
/* @var $model app\modules\soccer\models\TeamSynonym */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="team-synonym-form">

    <?php $form = ActiveForm::begin(); ?>
    <?= Html::textInput('team_name_pattern', null, ['id' => 'team_name_pattern']); ?>
    <?= $form->field($model, 'team_id')->dropDownList([], [
        'id' => 'teams_available'
     ] ) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
