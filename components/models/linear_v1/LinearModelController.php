<?php
namespace app\components\models\linear_v1;

use app\components\ControllerComponent;
use app\components\Logger;
use app\modules\betting\config\BettingConfig;
use app\modules\betting\models\Bet;
use app\modules\betting\models\BetEvent;
use app\modules\betting\models\BetType;
use app\modules\soccer\models\MatchStats;
use yii\base\Exception;

//use app\components\;

class LinearModelController extends ControllerComponent{

    private $info;
    const ERROR_NONE = 0;
    const PARAM_ERROR = 1;
    const MODEL_ERROR = 2;
    const EXECUTION_ERROR = 3;
    public static $STATUSES = [
        self::ERROR_NONE => 'No errors occurred.',
        self::PARAM_ERROR => 'Some of the parameters do not satisfy rules.',
        self::MODEL_ERROR => 'Model processing\'s gone wrong.',
        self::EXECUTION_ERROR => 'An error while execution occurred.',
    ];
    public function test($matchId) {

        $model = new LinearModel;

        $properties = $model->getBetProperties(true);
        //print_r($properties);

        //todo load bookie's values from soccer_bet [amorgunov 21-04-2016]
        $values = [];

        try {

            $params = [
                'matchId'           => $matchId,
                'output'            => 'percent',

                //todo possibility send [] with different values and choose best [amorgunov 21-04-2016]
                'value'             => '>3.5',

                'event'             => 'Yellow Card', //'Corners', // 'Yellow Сard'
                'bookie'            => null, //bookie coefficient
                'difference'        => 0.1,
                'differenceLimit'   => 1,
                
                'relevance'         => [
                    'Corners' => [
                        'PERSONAL'      => 1730,
                        'PREVIOUS'      => 50,
                        'INCOMPETITION' => 1260,
                        'REFEREE'       => 0,
                        'HOME'          => 610,
                        'AWAY'          => 510,
                    ],
                    'Yellow Card' => [
                        'PERSONAL'      => 530,
                        'PREVIOUS'      => 50,
                        'INCOMPETITION' => 1260,
                        'REFEREE'       => 1500,
                        'HOME'          => 610,
                        'AWAY'          => 0,
                    ],
                ],

                /*'function'  => [
                    'time' => [
                        'name'   => 'HYPERBOLIC_TANGENT',
                        'params' => [5.0, 2.5, 1.0, 0.5]
                    ],
                    'smoothing' => [
                        'name'   => '',
                        'params' => []
                    ]
                ],*/

                'smoothingMode'     => 1,
                'cutDeflectionMode' => 0,

                'debug'             => true
            ];

            $model->init($params);

            // progress probability of event
            $model->process();
            return $model->getResults();
            // get results
            //print json_encode($model->getResults());
        }
        catch( Exception $e ) {

            print $e->getMessage() . "\r\n";
            $model->log($e->getMessage(), true);
            return null;
        }


    }
    public function testV2($params){
        $this->info['status'] = self::ERROR_NONE;
        //проверка параметров
        if($this->validateData($params)){
            $accordances = LinearModel::getAccordances();
            $params['event'] = $accordances['event_id_to_model_event'][$params['event_id']];
            $params['stat_id'] = BettingConfig::$BEtoMS_ACCORDIANCES[$params['event_id']];
            //Здесь идет выбор ставок опр типа на матчи. При этом можно ограничить количество выбираемых ставок и матчей
            $bets = Bet::getLastBets( $params['type_id'],$params['event_id'], $params);
            $this->executeV2($bets, $params);
        }

        $this->finalizeInfo();
        return $this->info;
    }

    /**
     * @param $params
     * @return bool
     */
    private function validateData($params){
        if(empty($params['type_id'])){
            $this->info['status'] = self::PARAM_ERROR;
            $this->info['messages'][] = 'type_id parameter is not specified.';
            return false;
        }
        if(empty($params['event_id'])){
            $this->info['status'] = self::PARAM_ERROR;
            $this->info['messages'][] = 'event_id parameter is not specified.';
            return false;
        }
        if(empty($params['bookie_ids'])){
            $this->info['status'] = self::PARAM_ERROR;
            $this->info['messages'][] = 'bookie_ids parameter is not specified.';
            return false;
        }
        if(empty($params['bet_size'])){
            $this->info['status'] = self::PARAM_ERROR;
            $this->info['messages'][] = 'bet_size parameter is not specified.';
            return false;
        }
        if(empty($params['initial_bank_size'])){
            $this->info['status'] = self::PARAM_ERROR;
            $this->info['messages'][] = 'initial_bank_size parameter is not specified.';
            return false;
        }
        if(empty($params['model_props'])){
            $this->info['status'] = self::PARAM_ERROR;
            $this->info['messages'][] = 'model_props parameter is not specified.';
            return false;
        }

        return true;
    }

    /**
     * Метод для заключительной обработки данных теста
     */
    private function finalizeInfo(){
        if($this->info['status'] == self::ERROR_NONE){
            $this->info['results']['overall']['matches_num'] = count($this->info['results']['overall']['matches']);
            $this->info['results']['overall']['bets_done_num'] = count($this->info['results']['overall']['dynamics']) - 1;
        }
    }

    private function executeV2($bets, $params){
        $bank_size = $params['initial_bank_size'];
        //$matches_num = array_unique($bets[''])
        $bookie_ids = $params['bookie_ids'];
        $this->info['results'] = [];
        $this->info['results']['overall'] = [];//сюда будут скидываться все ставки
        $this->info['results']['overall']['matches'] = [];
        $this->info['results']['overall']['bank_size'] = $bank_size;
        $this->info['results']['overall']['dynamics'] = [];
        $this->info['results']['overall']['dynamics'][] =
            ['bet_id' => -1, 'bet_result' => -1, 'match_id' => -1,'bank_size' => $bank_size];
        $this->info['results']['overall']['bets_num'] = count($bets);
        foreach($bookie_ids as $bookie_id){// инфа по букам
            $this->info['results']['bookies'][$bookie_id] =[];
            $this->info['results']['bookies'][$bookie_id]['bets_done'] = 0;
            $this->info['results']['bookies'][$bookie_id]['bets_won'] = 0;
            $this->info['results']['bookies'][$bookie_id]['bets_lost'] = 0;
            $this->info['results']['bookies'][$bookie_id]['bets_tie'] = 0;
            $this->info['results']['bookies'][$bookie_id]['profit'] = 0;
            $this->info['results']['bookies'][$bookie_id]['dynamics'] = [];
        }

        foreach($bets as $bet) {
            $results = $this->processModel($params['model_props'], $bet,$params['event']);
            $this->processEventResult($results, $bet, $params);
        }
    }
    /**
     * @param $results
     * @param Bet $bet
     * @param $params
     */
    //TODO: support of 1x3, handicap, ind. total
    protected function processEventResult( $results, $bet, $params)
    {
        $model_props = $params['model_props'];
        $bank_size = $this->info['results']['overall']['bank_size'];
        $bet_size = $params['bet_size'];
        if(!empty($results)){
            if(!in_array($bet->match_id, $this->info['results']['overall']['matches']))
               $this->info['results']['overall']['matches'][] = $bet->match_id;
            if($results['probability'] != 0){
                $coef_computed = (1-0.07)/$results['probability'];
                $dif = $bet->coef - $coef_computed;
                if($model_props['difference'] < $dif && $dif < $model_props['differenceLimit'] ) {
                    $bet_res = $bet->getBetResult();
                    $bet_done_size = ($bank_size/100)*$bet_size;
                    $append = true;
                    switch($bet_res){
                        case BettingConfig::BET_WIN:
                            $bank_size+=$bet->coef*$bet_done_size - $bet_done_size;
                            $this->info['results']['bookies'][$bet->bookie_id]['profit']+=$bet->coef*$bet_done_size - $bet_done_size;
                            $this->info['results']['bookies'][$bet->bookie_id]['bets_won'] ++;
                            break;
                        case BettingConfig::BET_LOSS:
                            $bank_size-=$bet_done_size;
                            $this->info['results']['bookies'][$bet->bookie_id]['bets_lost'] ++;
                            break;
                        case BettingConfig::BET_DRAW:
                            $this->info['results']['bookies'][$bet->bookie_id]['bets_tie'] ++;
                            break;
                        default:
                            $append=false;
                            //$this->info['status'] = self::EXECUTION_ERROR;
                            $this->info['messages'][] = 'Can\'t process bet result '.$bet->id.'.';
                            break;
                    }
                    if($append){
                        $this->info['results']['bookies'][$bet->bookie_id]['dynamics'][$bet->id] =
                            ['bet_id' => $bet->id, 'bet_result' => $bet_res, 'match_id' => $bet->match_id,'bank_size' => $bank_size];
                        $this->info['results']['overall']['dynamics'][] =
                            ['bet_id' => $bet->id, 'bet_result' => $bet_res, 'match_id' => $bet->match_id,'bank_size' => $bank_size];
                    }
                }
            }
            else{
                //$this->info['status'] = 'error';
                $this->info['messages'][] = 'Result has 0 possibility for match MID('.$bet->match_id.').';
            }
        }
        $this->info['results']['overall']['bank_size'] = $bank_size;
    }

    /**
     * @param $props
     * @param Bet $bet
     * @param $event
     * @param string $output
     * @param null $bookie
     * @return array|null
     */
    protected function processModel($props, $bet, $event, $output = 'percent', $bookie = null){
        $model = new LinearModel();
        try{
            $model->init([
                'matchId'   => $bet->match_id,
                'output'    => $output,
                'value'     => $bet->toModelType(),
                'event'     => $event,
                'bookie'    => $bookie, //bookie coefficient
                'difference'=> $props['difference'],
                'relevance' => [
                    $event => [
                        'PERSONAL' => $props['PERSONAL'],
                        'PREVIOUS' => $props['PREVIOUS'],
                        'INCOMPETITION' => $props['INCOMPETITION'],
                        'REFEREE' => $props['REFEREE'],
                        'HOME' => $props['HOME'],
                        'AWAY' => $props['AWAY'],
                    ]
                ],
                'differenceLimit' => $props['differenceLimit'],
                'smoothingMode' => $props['smoothingMode'],
                'cutDeflectionMode' => $props['cutDeflectionMode'],
                'debug'             => true
            ]);

            // progress probability of event
            $model->process();
            return $model->getResults();
        }
        catch( \Exception $e ) {
            $model->log($e->getMessage(), true);
            $this->info['status'] = self::MODEL_ERROR;
            $this->info['messages'][] = 'Model exception. '.$e->getMessage();
            return null;
        }
    }

}