<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 18.04.2016
 * Time: 21:41
 */

namespace app\extensions\parsing\helpers;

use app\extensions\parsing\LogWritable;

class HTMLHelper extends LogWritable
{
    static $log_filename = 'html_searcher.log';

    public static function findAll($search_str, $html, $depth=null){
        if(!$depth)
            $object = $html->find($search_str);
        else
            $object = $html->find($search_str, $depth);
        if(!empty($object)) return $object;
        else{
            self::logPush('No '.$search_str.' was found.', self::$log_filename, self::ERROR);
            return null;
        }
    }
    public static function findOne($search_str, $html, $depth=null){
        if(!$depth)
        $object = $html->find($search_str);
        else
            $object = $html->find($search_str, $depth);
        if(!empty($object)) return $object[0];
        else{
            self::logPush('No '.$search_str.' was found.', self::$log_filename, self::ERROR);
            return null;
        }
    }
}