<?php

namespace app\extensions\geneticalgorithm;

use app\extensions\geneticalgorithm\helpers\Helper;
use app\extensions\parsing\LogWritable;
use app\modules\betting\config\BettingConfig;
use app\modules\betting\models\Bet;
use app\modules\betting\models\BetEvent;
use app\modules\betting\models\BetType;
use app\modules\soccer\models\Match;
use app\modules\soccer\models\MatchStats;
ini_set('max_execution_time', 1500);
class GACore extends LogWritable
{
    protected $id;
    protected $generations_num;
    static $log_filename = 'ga_core.log';

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function __construct($params = array()) {
        $this->generations_num = GAConfig::$config['common']['generations_num'];
        $this->debug = ($params['debug']) ? $params['debug'] : false ;
    }
    
    private $debug = false;


    /*public function testing() {
        $bet_info = array(
            'type_id' => 1,
            'event_id' => 1,
            'sign' => 1,
            'event' => 'Corners',
            'stat_id' => MatchStats::$FIELDS['Corners'],
            'bet_type' => 'total'
        );

        $ms = ModelSubject::randomModelSubject(['model_params_range' => GAConfig::$config['model']['model_params_range']]);
        $bets_line = array();
        $bookie_required_keys = array_keys(GAConfig::$config['model']['bookies']);

        $bets = Bet::find()
            ->where('type_id = :type_id', [':type_id' => $bet_info['type_id']] )
            ->andWhere('event_id = :event_id', [':event_id' => $bet_info['event_id']] )
            ->andWhere('value = :value', [':value' => $bet_info['value']] )
            ->andWhere('sign = :sign', [':sign' => $bet_info['sign']])
            //->limit(1)
            //->groupBy('match_id')
            ->all();
        $matches_line = array();
        foreach($bets as $key => $bet ){
            if(array_search($bet->bookie_id, $bookie_required_keys) !== false)
                $matches_line[$bet->match_id]['bookie_coefs'][$bet->bookie_id] = $bet;
        }
        $data = [
            'matches_line' => $matches_line,
            'initial_bank_size' => GAConfig::$config['model']['execution']['initial_bank_size'],
            'bet_size' => GAConfig::$config['model']['execution']['bet_size'],
            'event' => $bet_info['event'],
            'stat_id' => $bet_info['stat_id'],
            'bet_type' => $bet_info['bet_type']
        ];
        $ms->execute($data);
        return;
    }*/

    public function run($id = null){
        if(isset($id))$this->id = $id;
        else $this->id = Helper::generateRandomString(4);
        self::logPush('GA ID('.$this->id.') started.', self::$log_filename, self::INFO);
        $bet_info = array(
            'type_id' => BettingConfig::$TYPE_ID['total'],
            'event_id' => BettingConfig::$EVENT_ID['yellow_card'],
            'event' => 'Yellow Card', //Corners
            'stat_id' => MatchStats::$FIELDS['YellowCard'],
            //'bet_type' => 'total',
            'lower_initial_coef' => 1.7,
            'upper_initial_coef' => 2.3
        );

        $bookie_required_keys = array_keys(GAConfig::$config['model']['bookies']);

        $bets = Bet::find()
            ->where('type_id = :type_id', [':type_id' => $bet_info['type_id']] )
            ->andWhere('event_id = :event_id', [':event_id' => $bet_info['event_id']] )
            ->andWhere('initial_coef>:l_initial_coef and initial_coef<:u_initial_coef',
                [':l_initial_coef' => $bet_info['lower_initial_coef'], ':u_initial_coef' => $bet_info['upper_initial_coef'] ])
            //->limit(30)
            ->all();

        $matches_line = array();
        foreach($bets as $key => $bet ){
            if(array_search($bet->bookie_id, $bookie_required_keys) !== false)
                $matches_line[$bet->match_id]['bookie_coefs'][$bet->bookie_id][$bet->toModelType()] = $bet;
        }
        //echo count($matches_line);
        $data = [
            'matches_line' => $matches_line,
            'initial_bank_size' => GAConfig::$config['model']['execution']['initial_bank_size'],
            'bet_size' => GAConfig::$config['model']['execution']['bet_size'],
            'event' => $bet_info['event'],
            'stat_id' => $bet_info['stat_id'],
            //'bet_type' => $bet_info['bet_type']
        ];
        $generation = new ModelGeneration();

        $generation->setData($data);
        $generation->generateFirstGeneration();

        $current_generation_n = 0;
        $generation->setId($this->id.'-'.($current_generation_n+1));
        while($current_generation_n < $this->generations_num){
            $current_generation_n++;
            $generation->execute();
            $generation->save();
            //break;
            $generation->getNewGeneration();
            $generation->setId($this->id.'-'.($current_generation_n+1));
            //break;
        }

        self::logPush('GA ID('.$this->id.') finished it\'s work.', self::$log_filename, self::INFO);

       /* if ($this->debug) {
            return $this->testing();
        }*/

        //подготовка выборки матчей
        //генерация начального поколение
        //прогонка текущего поколения по заготовленным матчам
        //оценка успешности каждойи из них и выдача вероятностей стать родителем
        //скрещивание и генерация нового поколения
        //подведение итогов
        /*$current_generation_n = 0;
        $generation = new TestGeneration();
        $data = ['equation_coefs' => array('x1' => 2, 'x2' => 1, 'x3' => -0.5, 'x4' => 2), 'result_required' => 15];
        //$result_required = 15;
        $generation->setData($data);
        $generation->generateFirstGeneration();

        //до запуска отбора нужно сделать выборку 500-1000 матчей на которых все будет расчитываться
        while($current_generation_n < $this->generations_num){
            $current_generation_n++;
            $generation->execute();
            //break;
            $generation->getNewGeneration();
            //break;
        }

        echo '<pre>';
        print_r($generation);
        echo '</pre>';*/





       /* $stats = MatchStats::getMatchStat($matches_line[0]->id, 1);
        //$stats = $matches_line[0]->matchStats;
        echo '<pre>';
        print_r($stats);
        echo '</pre>';*/
        //$ms->execute($data);
        /*echo '<pre>';
        print_r($ms);
        echo '</pre>';*/
       // echo Helper::randomValue('float', 20, 30);


    }

}