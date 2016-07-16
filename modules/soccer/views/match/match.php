<?php
use yii\helpers\Html;
use yii\widgets\LinkPager;
use yii\helpers\Url;

$item = $result;
?>
<h4>
    <?=date('d.m.Y',strtotime($item->date))?>.&nbsp;
    <?= Html::img('http://corner-stats.com//catalog/view/theme/default/images/32/'.ucfirst($item->competition->country->image), ['alt'=>'some', 'style'=>'width: 16px','class'=>'thing']);?>		<?=$item->competition->name?> (<?=$item->competition->country->name?>).&nbsp;
    <?= Html::img('http://corner-stats.com//catalog/view/theme/default/images/32/'.ucfirst($item->home->country->image), ['alt'=>'some', 'style'=>'width: 16px','class'=>'thing']);?>		<?= Html::encode("{$item->home->name}") ?> - <?= Html::encode("{$item->away->name}") ?>		<?= Html::img('http://corner-stats.com//catalog/view/theme/default/images/32/'.ucfirst($item->away->country->image), ['alt'=>'some', 'style'=>'width: 16px','class'=>'thing']);?>	</h4>

<table>
<tr>
<td valign="top" style="min-width:300px">
    <table border="0">
        <tbody><tr>
            <td><b>Match start:</b></td>
            <td width="15"></td>
            <td><?=date('d/m/Y H:i',strtotime($item->date)) ?></td>
        </tr>
        <tr>
            <td><b>Tournament:</b></td>
            <td></td>
            <td><?=$item->competition->name?></td>
        </tr>
        <tr>
            <td><b>Season:</b></td>
            <td></td>
            <td><?=$item->season?></td>
        </tr>
        <?php if (isset($item->matchday) && $item->matchday!='') { ?>
        <tr>
            <td><b>Week/round:</b></td>
            <td></td>
            <td><?=$item->matchday?></td>
        </tr>
        <?php } ?>
        <?php if (isset($item->referee->name)) { ?>
        <tr>
            <td><b>Referee:</b></td>
            <td></td>
            <td><a href="/craig-pawson/england/referee/210"><?=$item->referee->name?></a></td>
        </tr>
        <?php } ?>
        <?php if (isset($item->stadium->name)) { ?>
        <tr>
            <td><b>Stadium:</b></td>
            <td></td>
            <td><a href="/craig-pawson/england/referee/210"><?=$item->stadium->name?></a></td>
        </tr>
        <?php } ?>
        </tbody></table>

</td>
</tr>
</table>

