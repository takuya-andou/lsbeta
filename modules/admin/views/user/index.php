<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use \yii\bootstrap\Modal;
use app\modules\admin\components\Helper;
use app\assets\AdminAsset;
AdminAsset::register($this);
/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\searchs\User */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('rbac-admin', 'Users');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Pjax::begin(); ?>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'username',
            'email:email',
            'created_at:date',
            [
                'attribute' => 'status',
                'value' => function($model) {
                    $statuses = \app\modules\user\models\User::getStatusesArray();
                    return $statuses[$model->status];
                },
                'filter' => \app\modules\user\models\User::getStatusesArray()
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => Helper::filterActionColumn(['view', 'update', 'delete']),
                'buttons' => [
                    'update' => function($url, $model) {
                        return '<span class="modal_button glyphicon glyphicon-pencil blue" data-url="'.$url.'" style="color:#337ab7"></span>';
                    }
                    ]
                ],
            ],
        ]);
        ?>
    <?php Pjax::end(); ?>
</div>
<?php
Modal::begin([
    'id' => 'modal_edit_form_container',
    'size' => 'modal-lg',
    'closeButton' => ['id' => 'close-button'],
]);
echo '<div id="modal_content"></div>';
Modal::end();
?>

