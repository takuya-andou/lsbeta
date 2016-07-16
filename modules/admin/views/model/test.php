<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\assets\AdminAsset;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

AdminAsset::register($this);

$this->title = 'Test Models';
$this->params['breadcrumbs'][] = ['label' => 'Models', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Test';
?>
<div id="chart_container">

</div>
<div id="options_container">
    <?php $form = ActiveForm::begin(['id' => 'model-selection-form',
    ]); ?>
    <?= Html::dropDownList('model_selection', null, \yii\helpers\ArrayHelper::map($models, 'id', 'name'),
        ['id'=>'model_selection']) ?>

    <?= Html::checkboxList('bookie_selection', null, \yii\helpers\ArrayHelper::map($bookies, 'id', 'name'),
        ['id'=>'bookie_selection']) ?>

    <?= Html::textInput('bet', '3', ['id'=>'bet_sizing']) ?>

    <?= Html::textInput('matches_since_date', '2016-04-01 00:00:00', ['id'=>'matches_since_date']) ?>

    <?= Html::textInput('matches_until_date', '2016-06-06 00:00:00', ['id'=>'matches_until_date']) ?>

    <?= Html::textInput('lower_coef', '1', ['id'=>'lower_coef']) ?>

    <?= Html::textInput('upper_coef', '4', ['id'=>'upper_coef']) ?>

    <?= Html::textInput('matches_num', '12', ['id'=>'matches_num']) ?>

    <?= Html::textInput('bets_num', '20', ['id'=>'bets_num']) ?>

    <div class="form-group">
        <?= Html::Button('Run', ['id' => 'run_test_button', 'class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
<div id="model_result">
    <div id="chart"></div>
    <div id="stats"></div>
    <div id="message"></div>
    <div id="matches"></div>
    <div id="bets"></div>
</div>
