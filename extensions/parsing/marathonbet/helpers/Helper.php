<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 14.04.2016
 * Time: 16:52
 */

namespace app\extensions\parsing\marathonbet\helpers;


class Helper
{
    public static function getMatchData($con, $id) {
        $page = $con->curl('markets.htm', [
            'treeId'     => $id,
            'columnSize' => 8
        ]);

        return $page['content'];
    }
    public static function getTreeId($blockHTML){
        preg_match('#treeid="(.+?)"#is', $blockHTML, $arr);
        if(isset($arr[1]))
        return $arr[1];
        else{
            \Yii::$app->parsing_logger->push('MarathonbetParser error. No treeid in block was found.');
            return null;
        }
    }
    /*public static function findAll($search_str, $html){
        $object = $html->find($search_str);
        if(!empty($object)) return $object;
        else{
            \Yii::$app->html_search_logger->write('MarathonbetParser error. No '.$search_str.' was found.');
            return null;
        }
    }
    public static function findOne($search_str, $html){
        $object = $html->find($search_str);
        if(!empty($object)) return $object[0];
        else{
            \Yii::$app->html_search_logger->write('MarathonbetParser error. No '.$search_str.' was found.');
            return null;
        }
    }*/
    public static function trimText($text){
        $text = trim($text, "\n");
        $text = trim($text, " ");
        return $text;
    }
    public static function ifTextFits($string, $patterns, $ignore_spaces = false){
        $str = htmlspecialchars_decode($string, ENT_QUOTES);
        foreach($patterns as $key => $pattern)
            if($ignore_spaces == true){
                if(preg_match(str_replace(' ', '', $pattern), str_replace(' ', '', $str))) return $key;
            }
            else{
                if (preg_match($pattern, $str)) return $key;
            }
        return -1;
    }
    public static function getRequirementCategoryTitle($category_parts){
        $str ="";
        foreach($category_parts as $part )
            if(!empty($part))
            $str.=$part['title'].'.';
        return trim($str, '.');
    }
    public static function convertDate($mb_date){//return format 2016-07-16 11:35:00
        $months = array(
            '01' => '/Jan/',
            '02' => '/Feb/',
            '03' => '/Mar/',
            '04' => '/Apr/',
            '05' => '/May/',
            '06' => '/Jun/',
            '07' => '/Jul/',
            '08' => '/Aug/',
            '09' => '/Sep/',
            '10' => '/Oct/',
            '11' => '/Nov',
            '12' => '/Dec/'
        );
        /*foreach($months as $key=>$month){
            $mb_date = preg_replace ('/'.$month.'/', $key, $mb_date);
        }*/
        $time_pattern = '/\d{2}:\d{2}/';
        $date_pattern = '/\d{2} \D{3}/';
        $year_pattern = '/\d{4}/';
        $day_pattern = '/\d{2}/';
        date_default_timezone_set('UTC');
        $cur_date = date('Y-m-d H:i:s');
        $date_obj = \DateTime::createFromFormat('Y-m-d H:i:s', $cur_date);
        //���� ���������� ���
        if(preg_match($year_pattern, $mb_date, $match)){
            $date_obj->setDate($match[0], $date_obj->format('m'),  $date_obj->format('d'));
        }
        //���� ���������� ���� �����
        if(preg_match($date_pattern, $mb_date, $match)){
            $date_u = $match[0];
            $month_n = -1;
            foreach($months as $key=>$month){
                if(preg_match($month, $date_u)){
                    $month_n = $key;
                    break;
                }
                //$date_u = preg_replace ('/'.$month.'/', $key, $date_u);
            }
            preg_match($day_pattern, $date_u, $match_d);
            $day = $match_d[0];
            if($month_n!=-1 && isset($day))
            $date_obj->setDate($date_obj->format('Y'), $month_n,  $day);
        }
        //���� ����������� �����
        if(preg_match($time_pattern, $mb_date, $match)){
            $time = $match[0];
            $h_m = explode(':', $time);
            if(isset($h_m[0]) && isset($h_m[1]))
            $date_obj->setTime($h_m[0], $h_m[1], 0);
            else return null;
            //$date_obj->setDate($match[0], $date_obj->format('m'),  $date_obj->format('d'));
        }
        else return null;
        return $date_obj->format('Y-m-d H:i:s');

    }

}