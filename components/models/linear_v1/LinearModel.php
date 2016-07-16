<?php
/*
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', 900);
 */

namespace app\components\models\linear_v1;

use Yii;
use yii\base\Exception;
use app\modules\soccer;
use app\components\ExtendedComponent;
use \app\modules\soccer\models\MatchStats;
use \app\modules\soccer\models\Match;
use yii\caching\Cache;

/**
 * Class LMVar Linear model variable with name
 * now only total's
 * todo gandicape, 1x2 and other [amorgunov 21-04-2016]
 */
class LMVar {
    const BET_TYPE_TOTAL = 0;

    const NORMALIZE_TYPE_SIMPLE = 0;
    const NORMALIZE_TYPE_SUMTOONE = 1;

    const TIME_FUNCTION_HYPERBOLIC_TANGENT = 10;
    const TIME_FUNCTION_SIMPLE = 11;

    const SMOOTHING_FUNCTION_SIGMOID = 20;

    // 'PERSONAL'     - Personal matches
    // 'PREVIOUS'     - Previous matches
    // 'INCOMPETITION'- Matches from this competition,
    // 'REFEREE'      - Matches with current referee (for cards)
    // 'ALL'          - All matches (don't used)

    const MATCH_TYPE_PERSONAL       = 'PERSONAL';
    const MATCH_TYPE_PREVIOUS       = 'PREVIOUS';
    const MATCH_TYPE_INCOMPETITION  = 'INCOMPETITION';
    const MATCH_TYPE_REFEREE        = 'REFEREE';
    const MATCH_TYPE_ALL            = 'ALL';
    const MATCH_TYPE_HOME           = 'HOME';
    const MATCH_TYPE_AWAY           = 'AWAY';
    
    
    const MATCH_CLASS_MAIN      = 0;
    const MATCH_CLASS_REFEREE   = 1;
    const MATCH_CLASS_COEF      = 2;

}



/**
 * Class LinearModel
 * This tiny class can process probability of some football events
 * You should to specify match's identifier and bet's type.
 * And makes money hand over fist!
 * @package app\components\models\linear_v1
 */
class LinearModel extends ExtendedComponent {
    /**
     * @var array $params (necessary: matchId, output, bet, property)
     */
    protected $params = [];

    /**
     * @var array $requireFields
     */
    private static $requireFields = ['matchId', 'event', 'value', 'difference'];

    /**
     * @var array $data 2x with matches (keys in $relevances)
     */
    private $data;
    
    /**
     * @var Match $match current match
     */
    private $match;

    /**
     * @var int $time
     */
    private $time = 0;

    /**
     * @var bool $debug
     */
    protected $debug = false;

    /**
     * @var Cache $cache
     */
    private $cache = null;

    /**
     * @var string $cacheKey
     */
    private $cacheKey = null;

    /**
     * @var ModelFunction
     */
    private $functionClass = null;

    /**
     * @var array $result
     */
    private $results = [
        'bookie_value'  => 0,
        'model_value'   => 0,
        'probability'   => 0,
    ];

    /**
     * @var array $arrPossibleSign possible signs in first letter in $this->parmas['value']
     * There is only simply math signs in this realization.
     */
    private static $arrPossibleSign = ['>','<','=','!'];

    /**
     * @var array $allowEvents
     */
    private static $allowEvents = ['Yellow Card', 'Red Card', 'Goals', 'Corners'];

    /**
     * @var int $timeFunction name of function, with help to consider importance of time
     * There's only name of function, without params. It was picked up separately.
     */
    private static $timeFunction = ModelFunction::TIME_FUNCTION_HYPERBOLIC_TANGENT; //(-tanh(5*x-2.5)+1)*0.5

    /**
     * @var int $smoothingFunction
     * @see readme
     */
    private static $smoothingFunction = ModelFunction::SMOOTHING_FUNCTION_SIGMOID;

    /**
     * @var array $normalizeSpace of normalize
     */
    private static $normalizeSpace = [0,1];

    /**
     * @var bool $smoothingMode
     */
    private $smoothingMode = false;

    /**
     * @var bool $cutDeflectionMode
     */
    private $cutDeflectionMode = false;

    /**
     * @var array $relevances of possible field of $this->data with importance
     * Picked up experimentally.
     * Before used this data should be normalized.
     */
    private $relevances = [
        LMVar::MATCH_TYPE_PERSONAL      => 0.55,
        LMVar::MATCH_TYPE_PREVIOUS      => 0.3,
        LMVar::MATCH_TYPE_INCOMPETITION => 0.15,
        LMVar::MATCH_TYPE_REFEREE       => 0,
        LMVar::MATCH_TYPE_ALL           => 0,
    ];

    /**
     * Accordiances between model params and db_values
     */
    public static function getAccordances(){
        return [
            'event_id_to_db_stat' => [
                1 => MatchStats::$FIELDS['Corners'],
                2 => MatchStats::$FIELDS['Goals'],
                3 => MatchStats::$FIELDS['YellowCard'],
                4 => MatchStats::$FIELDS['RedCard'],
                5 => MatchStats::$FIELDS['Penalty'],
                6 => MatchStats::$FIELDS['Fouls'],
            ],
            'event_id_to_model_event' => [
                1 => 'Corners',
                //2 => MatchStats::$FIELDS['Goals'],
                3 => 'Yellow Card',
                /*4 => MatchStats::$FIELDS['RedCard'],
                5 => MatchStats::$FIELDS['Penalty'],
                6 => MatchStats::$FIELDS['Fouls'],*/
            ]

        ];
    }


    public function __construct() {}

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Function for logging
     * @param string $message message to write
     * @param bool $error
     * @return bool
     */
    public function log($message = '', $error = false){
        if (!$this->debug) {
            return false;
        }

        if (array_key_exists('matchId', $this->params)) {
            $message = '{matchId: ' . $this->params['matchId'] . '} ' . $message;
        }

        //fix for logging errors
        $error = ($error) ? $this->params['errorPath'] : $error;
        
        $this->getLogger()->setPath($error)->push($message, $error);
        return true;
    }

    /**
     * Init function instead constructor
     * @param array $params
     * @return $this
     * @throws Exception
     */
    public function init($params = []){
        $params = array_merge(
            require(__DIR__. "/config.php"),
            $params
        );

        $this->time = microtime(true);
        $this->cache = Yii::$app->cache;

        foreach(self::$requireFields as $key) {
            if (!$params[$key]) {
                throw new Exception("Empty require params: " . $key);
            }
        }

        $params['eventId'] = $this->getEventId($params['event']);
        $params['value'] = $this->parseBetValue($params['value']);

        $this->error = false;
        $this->debug = ($params['debug']) ? $params['debug'] : false;
        $this->params = $params;
        $this->functionClass = new ModelFunction();

        $this->smoothingMode = $params['smoothingMode'];
        $this->cutDeflectionMode = $params['cutDeflectionMode'];

        $this->relevances = $this->getRelevances($params['event']);

        $this->cacheKey = md5(json_encode($this->params));

        $this->rebalanceRelevances($params); // ?

        $this->log('Init model wth params: ' . json_encode($params) . ')');
        return $this;
    }

    /**
     * @param $event
     * @return array
     */
    public function getRelevances($event) {
        $r = $this->params['relevance'];
        return (array_key_exists($event, $r)) ? $r[$event] : $r['default'];
    }

    /**
     * @param bool $names
     * @return array
     */
    public function getBetProperties($names = false) {
        return ($names) ? array_values(array_flip(MatchStats::$FIELDS)) : MatchStats::$FIELDS;
    }

    /**
     * Get current result
     * @return string
     */
    public function __toString(){
        //todo implement [amorgunov 21-04-2016]
        return $this->results['probability'] . "\n\r";
    }

    /**
     * Function rebalanced relevances
     * Now only for referee
     * @param $params
     */
    public function rebalanceRelevances($params){

        //Если событие (event) не карточки (фолы), то коэффициент значимости арбитра ставим в 0
        if (!in_array($params['eventId'], [MatchStats::getFieldId('YellowCard'), MatchStats::getFieldId('RedCard')] )) {
            $this->relevances[LMVar::MATCH_TYPE_REFEREE] = 0;
        }
    }

    /**
     * Function get eventId by name
     * For example, 'Corners' or 'Yellow Card'
     * @param $name
     * @return mixed
     * @throws Exception
     */
    public function getEventId($name){
        if (!in_array($name, self::$allowEvents)) {
            throw new Exception("Disable event: '" . $name ."'");
        }

        $name = $string = preg_replace('/\s+/', '', $name);
        //$name = ucfirst(mb_strtolower($name));

        if (!array_key_exists($name, MatchStats::$FIELDS)) {
            throw new Exception("Event wasn't found: '" . $name);
        }

        return MatchStats::$FIELDS[$name];
    }

    /**
     * Function transforms string bet like '>4.5' or '<10.5' in array
     * @param $value
     * @return array
     * @throws Exception
     */
    public function parseBetValue($value){

        /*$name = $string = preg_replace('/\s+/', '', $this->params['PROPERTY']);
        $name = ucfirst(mb_strtolower($name));
        $this->params['eventId'] = \app\modules\soccer\models\MatchStats::$FIELDS[$name];
        >4.5*/

        //todo need think something more safelty reliable [amorgunov 21-04-2016]
        $sign  = preg_replace("/[^\<\>\=\!\+\-]/", "" , $value);

        if (!in_array($sign, self::$arrPossibleSign)) {
            throw new Exception("Wrong value's sign: '" . $sign . "' {value: ". $value . "}");
        }

        $number = str_replace($sign, '', $value);

        if ($number != (string)((float)$number)) {
            throw new Exception("Wrong value's number: '" . $number . "' {value: ". $value . "}");
        }

        /*print (float)$number . "\r\n";
        print $number . "\r\n";
        print $number == (string)((float)$number);
        exit();*/

        return [
            'type'   => LMVar::BET_TYPE_TOTAL,
            'sign'   => $sign,
            'number' => (float)$number
        ];
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function process() {
        $this->loadData();
        $this->setRelevanceForMatches();
        $this->processProbability();
        
        //echo 'Time ending: ' . (microtime(true) - $this->time) . "\r\n";
        return $this;
    }

    /**
     * Function load from base data all necessary
     * Matches is divided from into parts.
     *
     * @return $this
     * @throws Exception
     */
    private function loadData() {
        $key = __FUNCTION__ . $this->params['matchId'];
        $currentMatch = $this->cache->get($key);

        if ($currentMatch === false) {
            //find match by id in soccer_bet before using model
            $currentMatch = Match::getMatch($this->params['matchId']);
            $this->cache->set($key, $currentMatch, 10800);
        }

        if (!$currentMatch) {
            throw new Exception('Match {id:' . $this->params['matchId'] . '} wasn\'t found');
        }
        $this->match = $currentMatch;

        $key = __FUNCTION__ . $currentMatch->id . $this->params['eventId'];
        $matches = $this->cache->get($key);

        if ($matches === false) {
            //get Match object for two teams
            $matches = Match::getMatchOneTeam(
                $currentMatch->home_id,
                $currentMatch->away_id,
                $currentMatch->date,
                $this->params['eventId']

            );

            foreach($matches as &$match) {
                $match['date'] = strtotime($match['date']);
                $match['type'] = [LMVar::MATCH_CLASS_MAIN];
            }

            $matchIds = $this->getMatchIds();

            //@todo Получать матчи схожие по кэфам (Либо этих команд, либо всех - всех, либо из этого турнира) [amorgunov 02-05-2016]


            //+ Получаем матчи с текущим рефери
            if ($this->relevances[LMVar::MATCH_TYPE_REFEREE] > 0 && $this->match->referee_id) {

                //@todo в кэш [amorgunov 02-05-2016]

                $matchesReferee = Match::getMatchesByReferee(
                    $currentMatch->referee_id,
                    $currentMatch->date,
                    $this->params['eventId']
                );

                /*
                $count = count($matches);
                print "Count: {$count}";*/

                foreach($matchesReferee as $m) {

                    if (array_key_exists($m["id"], $matchIds)) {

                        $matches[$matchIds[$m['id']]]['type'] = array_merge(
                            $matches[$matchIds[$m['id']]]['type'],
                            [LMVar::MATCH_CLASS_REFEREE]
                        );

                    } else {
                        $m['date'] = strtotime($m['date']);
                        $m['type'] = [LMVar::MATCH_CLASS_REFEREE];

                        $matches[] = $m;
                    }
                }
            }

            $this->cache->set($key, $matches, 10800);
        }

        $this->data = $matches;
        return $this;
    }

    /**
     * @return $this
     */
    private function setRelevanceForMatches() {

        $matches = $this->data;
        
        // удаляем "крайние" значения
        if ($this->cutDeflectionMode) {

            $count = count($matches);
            $deleted = round( $count * 2 / 100 );

            $matchesDiff = [];
            $value = $this->params['value']['number'];

            foreach($matches as $key => $match) {
                $matchesDiff[$key] = abs($value - $match["event_value"]);
            }

            arsort($matchesDiff);
            $needDelete = array_slice($matchesDiff, 0, $deleted , true);

            foreach($needDelete as $id) {
                unset($matches[$id]);
            }
        }

        //Каждому матчу даем коэффициент значимость
        $this->data = $this->setRelevance($matches);
        
        return $this;
    }

    /**
     * @param array $matches
     * @return array
     */
    private function getMatchIds(array $matches = []) {
        $matchIds = [];
        foreach($matches as $key => $match) {
            $matchIds[$match['id']] = $key;
        }
        return $matchIds;
    }

    /**
     * @param array $matches
     * @return array
     */
    private function setRelevance(array $matches = []) {

        $h = $this->match->home_id;
        $a = $this->match->away_id;
        $c = $this->match->competition_id;
        $r = $this->match->referee_id; // (?)

        /*$usedMatches = [];
        $usedMatches[$m['id']] = $key;
        if (array_key_exists($m['id'], $usedMatches)) {
            $key = $usedMatches[$m['id']];
        }*/

        foreach($matches as $key => $m) {
            $relevance = 0;

            if (in_array(LMVar::MATCH_CLASS_MAIN, $m["type"])) {

                //Если это встреча из этого же турнира
                if ($c && $m["competition_id"] == $c) {
                    $relevance += $this->relevances[LMVar::MATCH_TYPE_INCOMPETITION];
                }

                //Если это личная встреча
                if (($m["home_id"] == $h && $m["away_id"] == $a) ||
                    ($m["home_id"] == $a && $m["away_id"] == $h)) {
                    $relevance += $this->relevances[LMVar::MATCH_TYPE_PERSONAL]; //may be x2 ?
                }

                //Если из выбранного матча и списка матчей команда играет дома
                if ($m["home_id"] == $h) {
                    $relevance += $this->relevances[LMVar::MATCH_TYPE_HOME];
                }

                //Если из выбранного матча и списка матчей команда играет в гостях
                if ($m["away_id"] == $a) {
                    $relevance += $this->relevances[LMVar::MATCH_TYPE_AWAY];
                }

                //Добавляем значимость просто как игре
                $relevance += $this->relevances[LMVar::MATCH_TYPE_PREVIOUS];
            }

            if (in_array(LMVar::MATCH_CLASS_REFEREE, $m["type"])) {
                $relevance += $this->relevances[LMVar::MATCH_TYPE_REFEREE];
            }

            $matches[$key]['relevance'] = $relevance;
        }

        return $matches;

    }

    /**
     * Простая функция для нормализации данных
     * По умолчанию используется [0, 1]
     *
     * @param array $arr
     * @param bool $normalizeSpace
     * @param int $normalizeType
     * @return array|void
     */
    private function normalizeData($arr = array(), $normalizeSpace = false, $normalizeType = 0) {
        if (!$normalizeSpace && !is_array($normalizeSpace)) {
            $normalizeSpace = self::$normalizeSpace;
        }
        $max = max($arr);
        $min = min($arr);
        $count = count($arr);

        if ($normalizeType == 0) {
            //работаем так же с ассоциативными массивами
            foreach ($arr as $key => $val) {
                $current  = $arr[$key];
                if ($max === $min) {
                    $arr[$key] = 1;
                } else {
                    $arr[$key] = ($current - $min) * ($normalizeSpace[1] - $normalizeSpace[0]) / ($max - $min) + $normalizeSpace[0];
                }
            }
        }
        return $arr;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getRelevanceByDateFunction(){

        if (self::$timeFunction == ModelFunction::TIME_FUNCTION_HYPERBOLIC_TANGENT) {}

        $dates = array_column($this->data, 'date');
        $count = count($this->data);
        $res = [];

        $normalize = $this->normalizeData($dates);

        for($i=0; $i < $count; $i++) {

            //(tanh(5*($normDates[$i])-2.5)+1)*0.5,
            //$value = $this->functionClass->process(self::$timeFunction, $normDates[$i])->getValue();

            $a = [5.0, 2.5, 1.0, 0.5];
            $value = (tanh($a[0] * ($normalize[$i]) - $a[1]) + $a[2]) * $a[3];
            if (!$value) {
                throw new Exception('Error function.. {fn: ' . self::$timeFunction . ', x: ' . $normalize[$i] . '}');
            }

            $res[$dates[$i]] = $value;
        }
        return $res;
    }

    /**
     * @throws Exception
     */
    private function processProbability() {
        $key = __METHOD__ . $this->cacheKey;
        $datesRelevance = $this->cache->get($key);

        if ($datesRelevance === false) {
            $datesRelevance = $this->getRelevanceByDateFunction();
            $this->cache->set($key, $datesRelevance, 10800);
        }

        $params = [];

        if ($this->smoothingMode) {
            $params['max'] = max(array_column($this->data, "event_value"));
            $params['min'] = min(array_column($this->data, "event_value"));;
        }

        $sumWeights         = 0;
        $totalRelevance     = 0;

        //X = (a1*x1 + a2*x2 + a3*x3 ... an*xn ) {relevanceRes} / (a1 + a2 + an) {sumWeights}
        foreach($this->data as $match) {

            //получаем значение от 0 до 1 (0 - ставка не прошла, 1 - прошла)
            $x = $this->checkBetsCondition($match["event_value"], $params);

            $localRelevance = $datesRelevance[$match["date"]] * $match["relevance"];

            //к общей значимости приваляем значимость одного матча
            $totalRelevance += ($x * $localRelevance);

            //увеличиваем суммы весов
            $sumWeights += $localRelevance;
        }

        $probability    = $totalRelevance / ($sumWeights);

        if ($this->debug) {

            $modelCoef = $bookieCoef = 0;

            if (abs($probability - 0.0001) > 0.0001) {
                $modelCoef      = round((1 / $probability) * 100) / 100;
                $bookieCoef     = round(((1 - 0.07) / $probability) * 100) / 100;
            }
            /*print "{$totalRelevance} / {$sumWeights} \r\n";
            print "Probability: {$probability} \r\n";
            print "Model coef: {$modelCoef} \r\n";
            print "Bookie coef: {$bookieCoef} \r\n";*/

        }

        $this->results['probability'] = $probability;
        $this->log('Probability: ' . $probability);
    }

    /**
     * @param $value
     * @param array $params
     * @return float|int
     * @throws Exception
     */
    private function checkBetsCondition($value, $params = []) {
        $p = $this->params['value'];
        if ($p['type'] == LMVar::BET_TYPE_TOTAL) {

            if (in_array($p['sign'], ['>', '<']) && $value == $p['number']) {
                return 0.5;
            }

            if (!$this->smoothingMode || empty($params)) {
                if (($p['sign'] == '>' && $value > $p['number']) ||
                    ($p['sign'] == '<' && $value < $p['number']) ||
                    ($p['sign'] == '=' && $value = $p['number']) ||
                    ($p['sign'] == '!' && $value != $p['number']))
                {
                    return 1.0;
                }
            } else {

                //1 / 1 + (1 / $AR - 1) ^ (-$x + $T / $Tmax)

                $errormax = 0.9;
                $errormin = 0.1;
                $x = ($value);
                $T = ($p['number']);
                $Tmax = ($params['max']);
                $Tmin = ($params['min']);

                $result = 1;

                if ($p['sign'] == '>') {
                    if ($value > $p['number']) {
                        $result = 1.0 / (1.0 + pow((1.0 / $errormax - 1.0),((-$x + $T) / (-$Tmax + $T))));
                    } else {
                        $result = 1.0 / (1.0 + pow((1.0 / $errormin - 1.0),((-$x + $T) / (-$Tmin + $T))));
                    }
                }
                else if($p['sign'] == '<'){
                    if ($value > $p['number']) {
                        $result = - 1.0 / (1.0 + pow((1.0 / (1.0 -$errormin) - 1.0),((-$x + $T) / (-$Tmax + $T)))) + 1.0;
                    } else {
                        $result = - 1.0 / (1.0 + pow((1.0 / (1.0 -$errormax) - 1.0),((-$x + $T) / (-$Tmin + $T)))) + 1.0;
                    }
                }
                //print $x . " " . $T . " " . $Tmax . " " . $Tmin . " = " . $result . "\r\n";

                return $result;
            }
        }

        throw new Exception('Disable type of bets');
    }

}