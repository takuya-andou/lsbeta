<?php

use yii\web\View;
use yii\helpers\Markdown;
use yii\helpers\Url;
use yii\helpers\Html;
?>
<ul>
<li><?= Html::a('Users', ['user/index']); ?></li>
<li><?= Html::a('Roles', ['role/index']); ?></li>
<li><?= Html::a('Assignments    ', ['assignment/index']); ?></li>
<li><?= Html::a('Permissions', ['permission/index']); ?></li>
<li><?= Html::a('Routes', ['route/index']); ?></li>
<li><?= Html::a('Rules', ['rule/index']); ?></li>
<li><?= Html::a('Menu', ['menu/index']); ?></li>
<li><?= Html::a('Model', ['model/index']); ?></li>
<li><?= Html::a('Model Params', ['modelparam/index']); ?></li>
<li><?= Html::a('Model Testing', ['model/test']); ?></li>
<li><?= Html::a('Forecasts', ['/forecast/forecast/index']); ?></li>
<li><?= Html::a('Team synonyms', ['teamsynonym/index']); ?></li>
</ul>


