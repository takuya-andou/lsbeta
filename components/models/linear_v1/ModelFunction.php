<?php
namespace app\components\models\linear_v1;

use Yii;
use yii\base\Exception;
use app\modules\soccer;
use app\components\ExtendedComponent;
use \app\modules\soccer\models\MatchStats;
use \app\modules\soccer\models\Match;

class ModelFunction {

    //Константы с названиями функций, который используем для выхова функции извне
    const TIME_FUNCTION_HYPERBOLIC_TANGENT  = 'getHyperbolicTangent';

    const SMOOTHING_FUNCTION_SIGMOID        = '';
    const SMOOTHING_FUNCTION_SIGMOID_LEFT   = '';


    // Коэффициенты для функции по умолчанию
    static $default = [
        'TIME_FUNCTION_HYPERBOLIC_TANGENT'  => [5.0, 2.5, 1.0, 0.5],
        'SMOOTHING_FUNCTION_SIGMOID_LEFT'   => [],
    ];

    /**
     * @var float|null $value result
     */
    private $value = null;

    /**
     * @return float|null
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * Функция сохраняем в $this->value полученное значение
     * @param $name
     * @return $this
     */
    public function process($name, $x, $params = []) {
        $this->value = null;
        //$class = (new \ReflectionClass($this))->getShortName();
        //if(defined($class . '::' . $name)){}

        if (method_exists($this, $name)) {
            call_user_func( [$this, $name], $x, $params);
        }
        return $this;
    }

    /**
     * @param string $name
     * @return array
     */
    public function getDefaultParams($name) {
        $params = [];
        if (array_key_exists($name, self::$default)) {
            $params = self::$default[$name];
        }
        return $params;
    }

    /**
     * TIME_FUNCTION_HYPERBOLIC_TANGENT
     * @param float $x - require normalize
     * @param array $params
     * @return float
     */
    public function getHyperbolicTangent($x, $params = []) {
        $name = 'TIME_FUNCTION_HYPERBOLIC_TANGENT';

        $a = array_merge($this->getDefaultParams($name), $params);
        $this->value = (tanh($a[0] * ($x) - $a[1]) + $a[2]) * $a[3];
        return $this->value;
    }

}

/*

    public function allowBet() {

        return $this;
    }


    public function render() {}

    //Function delete empty matches
    private function deleteEmptyMatches(){
        $adding = 0;

        $res = $this->data[LMVar::MATCH_TYPE_ALL];
        $this->data[LMVar::MATCH_TYPE_ALL] = array();
        foreach($res as &$item) {
            if (!empty($item->matchStats)) {
                foreach ($item->matchStats as $item2) {
                    if ($item2->fkey == $this->params['eventId']) {
                        $this->data[LMVar::MATCH_TYPE_ALL][] = $item;
                        $adding++;
                    }
                }
            }
        }
        return $adding;
    }



    //Function merge two arrays
    private function mergeGamesByDate($arGames1,$arGames2,&$res){
        usort($arGames1, array($this, "sortByDateAsc"));
        usort($arGames2, array($this, "sortByDateAsc"));

        for($i=0;$i<count($arGames1);$i++) {
            for($j=0;$j<count($arGames2);$j++) {
                if (strtotime($arGames1[$i]["date"])<strtotime($arGames2[$j]["date"])) {
                    $arGames2 = array_merge(
                        array_slice($arGames2, 0, $j),
                        array($arGames1[$i]),
                        array_slice($arGames2, $j)
                    );
                    break;
                }
            }
        }
        $res = $arGames2;
    }

    public function sortByDateAsc($a, $b)
    {
        if (strtotime($a["date"]) == strtotime($b["date"])) {
            return 0;
        }
        return (strtotime($a["date"]) > strtotime($b["date"])) ? -1 : 1;
    }

    //Function delete personal matches
    private function deletePersonalMatchs(array &$matches){
        $res = $matches;
        $matches = array();
        foreach($res as &$match) {
            if (!(($match["home_id"] == $this->match->home_id && $match["away_id"] == $this->match->away_id) ||
                (($match["home_id"] == $this->match->away_id && $match["away_id"] == $this->match->home_id)))) {
                $matches[] = $match;
            }
        }
    }


        //X = (a1*x1 + a2*x2 + a3*x3 ... an*xn ) {relevanceRes} / (a1 + a2 + an) {sumWeights}
        foreach($this->data[LMVar::MATCH_TYPE_ALL] as $match) {
            $x = $match["event_value"];
            $x = $this->checkBetsCondition($x);
            //echo ';  normalizeX='.$x.'; value = '.$x .'*'. $arMatches[$match["id"]]."\r\n";
            $relevanceRes += $x * $arMatches[$match["id"]];
        }

        //var_dump(count($arMatches),count($this->data[LMVar::MATCH_TYPE_ALL]),$relevanceRes);
        var_dump($sumWeights, $relevanceRes);

        $relevanceRes = $relevanceRes / $sumWeights;
*/
