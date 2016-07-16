<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $model app\modules\betting\models\Model */
/* @var $form yii\widgets\ActiveForm */

function checkboxTextboxList($form, $model, $params){
    foreach($params as $key => $param){?>
        <div class="checkboxTextboxGroup">
            <?php
            echo \yii\helpers\BaseHtml::checkbox(
                $param->name, isset($model->params[$param->id]) || $param->required == \app\modules\betting\models\ModelParam::PARAM_REQUIRED,
                [
                    'label' => $param->name,
                    'class' => 'param-checkbox '.($param->required == \app\modules\betting\models\ModelParam::PARAM_REQUIRED ? 'required' : 'non-required'),
                    'disabled' => ($param->required == \app\modules\betting\models\ModelParam::PARAM_REQUIRED)
                ]);
            echo $form->field($model,
                'params['.$param->id.']',
                ['options' => ['class' => 'param-value']])->textInput(
                [
                    'value' => isset($model->params[$param->id]) ? $model->params[$param->id] : $param->default_value
                ])->label(false);
        ?>
        </div>
    <?php
    }
}

?>

<div class="model-form">

    <?php $form = ActiveForm::begin(['id' => 'model-form',
    ]); ?>

    <?= $form->field($model, 'type_id')->dropDownList(ArrayHelper::map($types, 'id', 'name')) ?>

    <?= $form->field($model, 'event_id')->dropDownList(ArrayHelper::map($events, 'id', 'name')) ?>

    <?= $form->field($model, 'usable')->dropDownList($usable_states) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?php
        checkboxTextboxList($form, $model,  $params);///*ArrayHelper::map($params, 'id', 'name'*/
    ?>


    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
