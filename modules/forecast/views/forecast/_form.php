<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\forecast\models\Forecast */
/* @var $form yii\widgets\ActiveForm */

$this->registerJs($this->render('_script.js'));
?>

<div class="forecast-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'summary')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'content')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'match_id')->dropDownList(
        \yii\helpers\ArrayHelper::map($upcoming_matches, 'id',
        function($model, $defaultValue) {
            return $model['home_name'].' - '.$model['away_name'].' ('.$model['date'].')';
        }),
        ['prompt' => 'Select upcoming match',
        'id' => 'match_selection']); ?>

    <?= $form->field($model, 'bet_id')->dropDownList( [],
        ['prompt' => 'Select bets for the match',
        'id' => 'bet_selection']); ?>

    <?= $form->field($model, 'bet_amount')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
