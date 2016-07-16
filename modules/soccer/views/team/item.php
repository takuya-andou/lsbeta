<?php
use yii\helpers\Html;
use yii\widgets\LinkPager;
use yii\helpers\Url;
?>

<h1 class="project_header section_header">
    <?= Html::img('http://corner-stats.com//catalog/view/theme/default/images/32/'.ucfirst($item->country->image), ['alt'=>'some', 'style'=>'width: 16px','class'=>'thing']);?>
    <?= Html::encode("{$item->name}") ?>.
    <!--Bundesliga, Germany.-->
    Team stats.
</h1>

<table id="matchs">

    <thead>
    <tr id="tb_head_alt">
        <th class="sorting_desc">Date</th>
        <th title="Tournament" class="sorting"  style="width: 55px;">Tourn</th>
        <th style="text-align:center" title="Round" class="td_center sorting_disabled" >R</th>
        <th style="text-align: right; width: 130px;" title="Home team" class="sorting">Team1</th>
        <th class="team_1_goals_quantity td_center sorting"></th>
        <th class="team_2_goals_quantity td_center sorting"></th>
        <th title="Away team" class="sorting"style="width: 130px;">Team2</th>
    </tr>
    </thead>
    <tbody>
<?php foreach ($matches as $match) { ?>
<tr class="teamstats homeGame odd" role="row">
    <td class="sorting_1"><?=date('d/m/Y',strtotime($match->date)) ?></td>
    <td><a href="/bundesliga/germany/tournament/2">BunL1</a></td>
    <td class=" td_center">14</td>
    <td style="text-align:right">
        <a href="/borussia-dortmund/germany/team/26"><?=$match->home->name?></a>
    </td>
    <td class="team_1_goals_quantity td_center" style="padding:0; background-color:#<?=$goals[$match->id]['color']?>"><a href="/" class="score_href"><b><?=$goals[$match->id]['home']?></b></a></td>

    <td class="team_2_goals_quantity td_center" style="padding:0; background-color:#<?=$goals[$match->id]['color']?>"><a href="/" class="score_href"><b><?=$goals[$match->id]['away']?></b></a></td>
    <td style="text-align:left">
        <a href="/vfb-stuttgart/germany/team/38"><?=$match->away->name?></a>
    </td>
</tr>
<?php } ?>
    </tbody>
</table>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<!--<script type="text/javascript" async="" src="//cdn.datatables.net/1.10.10/js/jquery.dataTables.min.js"></script>-->
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.10/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="//cdn.datatables.net/1.10.10/js/jquery.dataTables.js"></script

<script>
    $(document).ready(function(){
        $('#matchs').dataTable();
    });
</script>

