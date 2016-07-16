<?php
namespace app\extensions\geneticalgorithm\helpers;
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 26.04.2016
 * Time: 3:05
 */
class Helper
{
    public static function normalizeVector($vector){
        $len =0;
        $res = array();
        foreach($vector as $value)
            $len += $value*$value;
        foreach($vector as $key => $value)
            $res[$key] = $value/$len;
        return $res;
    }
    public static function normalizeVectorProb($vector){
        $len =0;
        $res = array();
        foreach($vector as $value)
            $len += $value*$value;
        foreach($vector as $key => $value)
            $res[$key] = $value*$value/$len;
        return $res;
    }

    public static function randomValue($type, $lower_limit = 0, $upper_limit = 1){

        switch($type){
            case 'int': $val = rand($lower_limit, $upper_limit); break;
            case 'float':
                $decimals = 2;
                $scale = pow(10, $decimals);
                $val = mt_rand($lower_limit * $scale, $upper_limit * $scale) / $scale;
                break;
            case 'bool':
                $val = mt_rand($lower_limit,$upper_limit) == 1;
                break;
            default: return null;
        }
        return $val;
    }

    public static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}