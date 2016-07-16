<?php
use app\components\widgets\model\ModelCalculator;
use app\assets\AdminAsset;

AdminAsset::register($this);
?>

<?= ModelCalculator::widget(); ?>
