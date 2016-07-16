<?php

namespace app\components\models\linear_v1;

class Helper {

//-------------------------------------------------------------------------------------------------------------

    /*else if ($normalizeType == 1) {
        $sum = array_sum($arr);
        if ($sum == 0) {
            return false;
        }

        $k = 1 / $sum;
        $i=0;
        foreach ($arr as &$item) {
            $item = $k * $item;
            $i++;
        }
    }*/


    /**
     * @param array $matches
     * @return array
     */
    private function devideMatchsIntoParts($matches){
        $arParts = $arMatches = [];

        foreach(self::$relevances as $key=>$value) {
            if ($value>0) {
                $arParts[$key] = $value;
                $arMatches[$key] = [];
            }
        }

        //$this->normalizeData($arParts, false, LMVar::NORMALIZE_TYPE_SUMTOONE);

        //todo maybe situation, when this referee already судил еп game with current teams [amorgunov november 2015]

        $h = $this->match->home_id;
        $a = $this->match->away_id;
        $c = $this->match->competition_id;

        foreach($matches as $item) {
            if (($item["home_id"] == $h && $item["away_id"] == $a) ||
                ($item["home_id"] == $a && $item["away_id"] == $h)) {
                $arMatches[LMVar::MATCH_TYPE_PERSONAL][] = $item;
            } elseif (($c) && $item["competition_id"] == $c) {
                $arMatches[LMVar::MATCH_TYPE_INCOMPETITION][] = $item;
            } else {
                $arMatches[LMVar::MATCH_TYPE_PREVIOUS][] = $item;
            }

            $arMatches[LMVar::MATCH_TYPE_ALL][] = $item;
        }
        return $arMatches;
    }



    /**
     * @param $matchId
     * @return int|string
     */
    private function getCategoryMatch($matchId) {
        foreach($this->data as $key => $matches) {
            //print_r($matches[0]);
            //print json_encode(array_column($matches, 'id'));
            if (in_array((string)$matchId, array_column($matches, 'id'))) {
                return $key;
            }
        }
        return false;
    }

    /**
     * Function return count of days between period
     * Time in SECONDS (after used strtotime)
     * @param $point1
     * @param $point2
     * @param $weeks
     * @return bool|int
     */
    public static function getCountDays($point1,$point2,$weeks = false) {
        $k = ($weeks)?7:1; //solve in weeks
        if ($point1 > $point2) {
            return (int)(($point1 - $point2) / (60 * 60 * 24) / $k)+1;
        } else
            return (int)(($point2 - $point1) / (60 * 60 * 24) / $k)+1;
    }

    /**
     * @param $point
     * @param array $period
     * @param bool $weeks
     * @return bool
     */
    public static function inPeriod($point, $period = array(), $weeks = false) {
        if ($weeks) {
            $period[1] *= 7;
        }
        echo $point.' - '.$period[0].' - '.($period[1]+$period[0])."\r\n";
        if ($point>$period[0] && $point<($period[1]+$period[0])) {
            return true;
        }
        return false;

    }

    /**
     * @param $arr
     * @param string $type
     * @return bool|int
     */
    public static function getDate($arr, $type='max') {
        $res = strtotime($arr[0]->date);
        foreach ($arr as $item) {
            $time = strtotime($item->date);
            if ($res < $time && $type == 'max') {
                $res = $time;
            } else if ($res > $time && $type == 'min') {
                $res =  $time;
            }
        }
        return $res;
    }
}