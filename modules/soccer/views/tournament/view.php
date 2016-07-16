<?php
use app\components\widgets\soccer\UpcomingMatches;

/** @var array $matches */
$matches;

?>





<h3>Предстоящие матчи</h3>
<?= UpcomingMatches::widget(['matches' => $matches['upcomingMatches']]); ?>

<h3>Фильтры</h3>
todo

<h3>Таблица</h3>
todo

<h3>Последние матчи</h3>
todo

<ul>
    <?php foreach ($matches as $item): ?>
        <li>
            <?= Html::img('http://corner-stats.com//catalog/view/theme/default/images/32/'.ucfirst($item->home->country->image), ['alt'=>'some', 'style'=>'width: 16px','class'=>'thing']);?>
            <?= Html::encode("{$item->home->name}") ?>
            <?= " - " ?>
            <?= Html::img('http://corner-stats.com//catalog/view/theme/default/images/32/'.ucfirst($item->away->country->image), ['alt'=>'some', 'style'=>'width: 16px','class'=>'thing']);?>
            <?= Html::encode("{$item->away->name}") ?>
        </li>
    <?php endforeach; ?>
</ul>

<?= LinkPager::widget(['pagination' => $pagination]) ?>

<?/*
<table style="width: 550px;">
    <?php foreach ($matches as $item): ?>
        <tr>
            <td width="66px"><?= "list", "{$item->date}" ?></td>
            <td style="padding-top:0; padding-bottom:0;"><?= Html::img('http://corner-stats.com//catalog/view/theme/default/images/32/'.ucfirst($item->home->country->image), ['alt'=>'some', 'style'=>'width: 18px','class'=>'thing']);?></td>
            <td><?= Html::encode("{$item->competition->name}") ?></td>
            <td align="right">
                <a href="<?= Url::toRoute(['post/index','id'=>234]) ?>"><?= Html::encode("{$item->home->name}") ?></a>
            </td>
            <td>
                &nbsp;<b><a href="<?= Url::toRoute(['match/match','id'=>$item->id]) ?>">VS</a></b>&nbsp;
                <!--/union-espanola-audax-italiano-09-11-2015/primera-division-chile/match/68406-->
            </td>
            <td>
                <a href="/audax-italiano/chile/team/2653"><?= Html::encode("{$item->away->name}") ?></a>
            </td>
        </tr>
    <?php endforeach; ?>
</table> */ ?>

