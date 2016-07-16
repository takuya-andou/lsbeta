<?php
use yii\bootstrap\BaseHtml;
use yii\helpers\ArrayHelper;
use app\assets\ModelAsset;

?>
<?php
ModelAsset::register($this);
?>
<div id="calculation_options_container">
    <?php
    echo BaseHtml::dropDownList('type_selection',
        null,
        ArrayHelper::map($types, 'id', 'name'),
        [ 'prompt' => 'Select type', 'id' => 'type_selection']);
    echo BaseHtml::dropDownList('event_selection',
        null,
        ArrayHelper::map($events, 'id', 'name'),
        [ 'prompt' => 'Select event',  'id' => 'event_selection']);?>
    <div id="params_container">

    </div>
    <?php
    echo BaseHtml::dropDownList('model_selection',
        null,
        [],
        [ 'prompt' => 'Select model',  'id' => 'model_selection']);
    echo BaseHtml::button('Calculate',['id' => 'calculate_button']);
    ?>
</div>
<div id="upcoming_matches_container">
    <?php
    if(!empty($matches))
    foreach($matches as $match){?>
        <div class="upcoming_match">
            <?php echo BaseHtml::checkbox('', true, [
                'class' => 'to_calculate_checkbox',
                'data-match-id' => $match->id
            ]) ?>
            <div class="league">
                <?php echo $match->competition->name; ?>
            </div>
            <div class="teams">
                <?php /*echo $match->home->name;*/ ?> vs <?php /*echo $match->away->name;*/ ?>
            </div>
            <div class="date">
                <?php echo $match->date; ?>
            </div>
            <div class="bets">
                <?php
                if(count($match->bets) > 0){?>
                    <div class="parsed_bets_flag active"></div>
                <?php
                }
                else{?>
                    <div class="parsed_bets_flag"></div>
                <?php
                }
                ?>
            </div>
        </div>
    <?php
    }
    ?>
</div>
