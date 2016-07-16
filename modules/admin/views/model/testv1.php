<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\assets\AdminAsset;
use yii\grid\GridView;

AdminAsset::register($this);

$this->title = 'Test Model: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Models', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Test';
?>
<h1>Model test "<?= $model->name; ?>"</h1>
<?php
    $max_bets_num = 0;
    $chart_data = [];
    foreach($results['bookies_info'] as $key => $bookie_info){
        if($bookie_info['bets_done'] > $max_bets_num) $max_bets_num = $bookie_info['bets_done'];
        foreach($bookie_info['dynamics'] as $bet){
            $chart_data[$key][] = $bet['bank_size'];
        }

    }
?>
<div class="chart_container ct-perfect-fourth"></div>
<script type="text/javascript">
    var chart_series_parsed = JSON.parse('<?= json_encode($chart_data); ?>');
    var chart_series_arr = [];
    for (var k in chart_series_parsed){
        if (chart_series_parsed.hasOwnProperty(k)) {
            chart_series_arr.push(chart_series_parsed[k]);
            //alert("Key is " + k + ", value is" + target[k]);
        }
    }
    //var t = $.parseJSON(chart_series);

    console.log(chart_series_arr);
    var chart_data = {
        // A labels array that can contain any sort of values
        //labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
        // Our series array that contains series objects or in this case series data arrays
        series: chart_series_arr
    };
</script>