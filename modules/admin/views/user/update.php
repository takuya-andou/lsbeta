<?php

use yii\helpers\Html;

/* @var $this  yii\web\View */
/* @var $model app\modules\admin\models\BizRule */

$this->title = Yii::t('rbac-admin', 'Update User') . ': ' . $model->username;
?>
<div class="auth-item-update">

    <h1><?= Html::encode($this->title) ?></h1>
    <?=
    $this->render('_form', [
        'model' => $model,
        'statuses' => $statuses
    ]);
    ?>
</div>
