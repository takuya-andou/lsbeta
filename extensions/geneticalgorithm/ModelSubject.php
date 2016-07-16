<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 25.04.2016
 * Time: 18:44
 */

namespace app\extensions\geneticalgorithm;


use app\components\models\linear_v1\LinearModel;
use app\extensions\geneticalgorithm\helpers\Helper;
use app\modules\betting\models\BetType;
use app\modules\soccer\models\MatchStats;

class ModelSubject extends Subject
{

    public function __construct($params = array()) {
        parent::__construct($params);
    }

    public static function interbreed($subject1, $subject2, $mutationOn = false, $mutation_chance = 0.05)
    {
        $params_range = GAConfig::$config['model']['model_params_range'];
        if(count($subject1->props) == count($subject2->props)){
            $child = new self;
            foreach($subject1->props as $key=>$prop){
                if($mutationOn){
                    $mut_probability = mt_rand() / mt_getrandmax();
                    if($mut_probability < $mutation_chance){
                        $child->props[$key] = Helper::randomValue($params_range[$key]['type'], $params_range[$key]['lower_limit'], $params_range[$key]['upper_limit']);
                    }
                    else{
                        $child->props[$key] = (mt_rand(0,1) == 1) ? $subject1->props[$key] : $subject2->props[$key];
                    }
                }
                else{
                    $child->props[$key] = (mt_rand(0,1) == 1) ? $subject1->props[$key] : $subject2->props[$key];

                }
            }
            return $child;
        }
        return null;
    }

    function execute($data)
    {
        /*echo '<pre>';
        print_r($this->props);
        echo '</pre>';*/
        $matches_line = $data['matches_line'];
        $bank_size = $data['initial_bank_size'];
        $bet_size = $data['bet_size'];
        $event = $data['event'];
        $stat_id = $data['stat_id'];
        $this->info= array();
        $this->info['matches_num'] = count($matches_line);
        foreach(GAConfig::$config['model']['bookies'] as $key => $bookie_coef){
            $this->info['bookies_info'][$key] = array('bets_overall' => 0 ,'bets_done' => 0, 'profit' => 0,
                'bets_success' => 0, 'bets_lost' => 0, 'bets_tie' => 0);
        }

        foreach($matches_line as $match_id => $match_info){
            foreach($match_info['bookie_coefs'] as $bookie_id => $totals){
                $this->info['bookies_info'][$bookie_id]['bets_overall'] += count($totals);
                foreach($totals as $total_str => $bet){
                    $results = $this->processModel($match_id,$bet,$event);
                    $bank_size = $this->processEventResult($results, $bet, $match_id, $stat_id, $bank_size, $bet_size);

                }

            }
        }
        $this->result = $bank_size;
        $this->info['result'] = $bank_size;
        /*echo '<br>';
        echo '<pre>';
        print_r($this->info);
        echo '</pre>';*/

    }

    /**
     * @param $results
     * @param $bet
     * @param $bet_type
     * @param $match_id
     * @param $stat_id
     * @param $bank_size
     * @param $bet_size
     * @return float
     */
    //TODO: support of 1x3, handicap, ind. total
    protected function processEventResult($results, $bet,
                                          $match_id, $stat_id,
                                          $bank_size, $bet_size)
    {
        if(!empty($results)){
            if($results['probability'] != 0){
                $coef_computed = (1-0.07)/$results['probability'];
                $dif = $bet->initial_coef - $coef_computed;
                if($this->props['difference'] < $dif && $dif < $this->props['differenceLimit'] ) {
                    $fact_total_val = MatchStats::getMatchStat($match_id, $stat_id);
                    if(!empty($fact_total_val)){//если есть с чем сравнивать
                        $bet_res = $this->handleBetOfType($bet->toModelType(), $bet, $fact_total_val['event_value']);
                        if($bet_res !=-1){
                            $this->info['bookies_info'][$bet->bookie_id]['bets_done'] ++;
                            $bet_done_size = ($bank_size/100)*$bet_size;
                            $bank_size -= $bet_done_size;
                            $this->info['bookies_info'][$bet->bookie_id]['profit']-=$bet_done_size;
                            if($bet_res ==0){
                                $this->info['bookies_info'][$bet->bookie_id]['bets_lost'] ++;
                            }
                            else if($bet_res == 1) {
                                $bank_size+=$bet->initial_coef*$bet_done_size;
                                $this->info['bookies_info'][$bet->bookie_id]['profit']+=$bet->initial_coef*$bet_done_size;
                                $this->info['bookies_info'][$bet->bookie_id]['bets_success'] ++;
                            }
                            else if($bet_res == 2) {
                                $bank_size+=$bet_done_size;
                                $this->info['bookies_info'][$bet->bookie_id]['profit']+=$bet_done_size;
                                $this->info['bookies_info'][$bet->bookie_id]['bets_tie'] ++;
                            }
                        }
                    }
                    else{
                        self::logPush('ModelSubject. No statistic of type ID('.$bet->type_id.') and statistic id stat_id('.$stat_id.') was found for match MID('.$match_id.').', self::$log_filename, self::ERROR);
                    }
                }
            }
            else{
                self::logPush('ModelSubject. Result for match MID('.$match_id.') has 0 possibility.', self::$log_filename, self::ERROR);
            }
        }
        else{
            self::logPush('ModelSubject. Results for match MID('.$match_id.') is empty.', self::$log_filename, self::ERROR);
        }


        return $bank_size;
    }

    /**
     * @param $total_required
     * @param $bet_type
     * @param $fact_total
     * @return int - code -1 - ошибка, 0 - не прошла, 1 - прошла, 2 - возврат средств
     */
    protected function handleBetOfType($total_required, $bet, $fact_total){
        switch($bet->type_id){
            case BettingConfig::$TYPE_ID['total']:
                $pattern = '/(<|>|=)(\d+[\.,]?\d*)/';
                preg_match($pattern, $total_required,  $match);
                if(count($match) == 3){
                    $sign = $match[1];
                    $total_required_val = $match[2];

                    switch($sign){
                        case '<':
                            if($fact_total < $total_required_val) return 1;
                            else if($fact_total == $total_required) return 2;
                            else return 0;
                            break;
                        case '>':
                            if($fact_total > $total_required_val) return 1;
                            else if($fact_total == $total_required) return 2;
                            else return 0;
                            break;
                        default:
                            self::logPush('ModelSubject. Error in sign matching for '.$total_required.'.', self::$log_filename, self::ERROR);
                            return -1;
                            break;
                    }
                }
                else{
                    self::logPush('ModelSubject. Error in preg matching for '.$total_required.'.', self::$log_filename, self::ERROR);
                }

                break;
            default:
                self::logPush('ModelSubject. Unsupported bet type.', self::$log_filename, self::ERROR);
                return -1;
        }
    }

    /**
     * @param $match_id
     * @param $bet
     * @param $event
     * @param string $output
     * @param null $bookie
     * @return array|null
     */
    protected function processModel($match_id, $bet, $event, $output = 'percent', $bookie = null){
        $model = new LinearModel();
        try{
            $model->init([
                'matchId'   => $match_id,
                'output'    => 'percent',
                'value'     => $bet->toModelType(),
                'event'     => $event, // 'Yellow card'
                'bookie'    => null, //bookie coefficient
                'difference'=> $this->props['difference'],
                'relevance' => [
                    $event => [
                        'PERSONAL' => $this->props['PERSONAL'],
                        'PREVIOUS' => $this->props['PREVIOUS'],
                        'INCOMPETITION' => $this->props['INCOMPETITION'],
                        'REFEREE' => $this->props['REFEREE'],
                        'HOME' => $this->props['HOME'],
                        'AWAY' => $this->props['AWAY'],
                    ]
                ],
                'differenceLimit' => $this->props['differenceLimit'],
                'smoothingMode' => $this->props['smoothingMode'],
                'cutDeflectionMode' => $this->props['cutDeflectionMode'],
                'debug'             => true
            ]);

            // progress probability of event
            $model->process();
            return $model->getResults();
        }
        catch( \Exception $e ) {
            $model->log($e->getMessage(), true);
            self::logPush('ModelSubject. Model exception '.$e->getMessage().'.', self::$log_filename, self::ERROR);
            return null;
        }
    }

    public static function randomModelSubject($data){
        $params = $data['model_params_range'];
        $props = array();
        $subject = new self;
        if(!empty($params)) {
            foreach ($params as $key => $param_required)
                $props[$key] = Helper::randomValue($param_required['type'], $param_required['lower_limit'], $param_required['upper_limit']);
            $subject->setProps($props);
            return $subject;
        }
        return null;
    }


}