<?php
namespace app\extensions\parsing\footballdata\helpers;
use app\extensions\parsing\footballdata\FootballdataRequirement;

/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 18.04.2016
 * Time: 22:05
 */
class Helper
{
    public static function getSeasonData($blockHTML, $season){
        $pattern1 = '/'.$season['preg_pattern'].'(.*)Season/Ums';
        $pattern2 = '/'.$season['preg_pattern'].'(.*)Contact/Ums';
        preg_match_all($pattern1, $blockHTML, $matches);
        //print_r($matches);
        if(isset($matches[1][0])) return $matches[1][0];
        preg_match_all($pattern2, $blockHTML, $matches);
        if(isset($matches[1][0])) return $matches[1][0];
        return null;
    }
    public static function getLeagueFileURL(&$blockHTML, $league){
        $pattern = '/<a href="([^\"]*)">'.(isset($league['preg_pattern']) ? $league['preg_pattern'] : $league['title']).'<\/a>/iU';
        preg_match($pattern, $blockHTML, $match);
        if(isset($match[1])) return $match[1];
        return null;
    }
    public static function makeFilename($country, $season, $league, $format = '.csv'){
        return self::convertTextToFilename($country.' '.$season.' '.$league.$format);
    }
    public static function convertTextToFilename($str){
        $str=str_replace('/', '_', $str);
        $str=str_replace(' ', '_', $str);
        return $str;
    }
    public static function ifFileExists($country_results_required,$season, $league, $format = '.csv'){
        return file_exists(\Yii::getAlias('@data').DIRECTORY_SEPARATOR.FootballdataRequirement::$config['loader_rules']['save_path'].DIRECTORY_SEPARATOR.Helper::makeFilename($country_results_required,$season,$league,$format));
    }
    public static function getFilePath($country_results_required,$season, $league, $format = '.csv'){
        if(!file_exists(\Yii::getAlias('@data').DIRECTORY_SEPARATOR.FootballdataRequirement::$config['loader_rules']['save_path'].
            DIRECTORY_SEPARATOR.Helper::makeFilename($country_results_required,$season,$league,$format))){
            return null;
        }
        return \Yii::getAlias('@data').DIRECTORY_SEPARATOR.FootballdataRequirement::$config['loader_rules']['save_path'].
        DIRECTORY_SEPARATOR.Helper::makeFilename($country_results_required,$season,$league,$format);
    }
    public static function saveFile($data,$country_results_required, $season, $league, $format = '.csv'){
        if(!file_exists(\Yii::getAlias('@data').DIRECTORY_SEPARATOR.FootballdataRequirement::$config['loader_rules']['save_path'])){
            mkdir(\Yii::getAlias('@data').DIRECTORY_SEPARATOR.FootballdataRequirement::$config['loader_rules']['save_path'], 0700);
        }
        $destination =  \Yii::getAlias('@data').DIRECTORY_SEPARATOR.FootballdataRequirement::$config['loader_rules']['save_path'].DIRECTORY_SEPARATOR.Helper::makeFilename($country_results_required,$season,$league, $format);
        $file = fopen($destination, "w+");
        fputs($file, $data);
        fclose($file);
        return true;
    }

    /**
     * @param $date - ���� � ������� DD/MM/YY
     * @return $converted_date - ���� � ������� 2016-04-19 17:53:43
     */
    public static function convertDate($date){
        $date_obj = \DateTime::createFromFormat('d/m/y', $date);
        $date_obj->setTime(12,0,0);
        return $date_obj->format('Y-m-d H:i:s');
    }
    ///mmz.*.csv/U
}