<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 17.04.2016
 * Time: 23:58
 */

namespace app\extensions\parsing;
use app\modules\soccer\models\TeamSynonym;

interface IStringSimilarity
{
    public static function saveSynonym($id_p, $str);
}

abstract class StringSimilarity implements IStringSimilarity
{
    public static function getStringSimilarity($data){
        $str1 = $data['str1'];
        $str2 = $data['str2'];
        similar_text($str1, $str2, $percent);
        //if($percent > 90) $this->saveSynonym($str2);
        return $percent/100;
    }
    public static function getStringSimularityA($data){
        $str1 = $data['to_abbreviate'];
        $str2 = $data['str2'];

        $str1 = self::abbreviateText($str1);
        similar_text($str1, $str2, $percent);
        //if($percent > 90) $this->saveSynonym($str2);
        return $percent/100;

    }
    public static function abbreviateText($str){
        if(preg_match_all('/\b(\w)/',strtoupper($str),$m)) {
            return implode('',$m[1]);
        }
    }
    public static function saveSynonym($id_p, $str)
    {
        $synonym = new TeamSynonym();
        $synonym->team_id = $id_p;
        $synonym->name=$str;
        return $synonym->save();
    }
}