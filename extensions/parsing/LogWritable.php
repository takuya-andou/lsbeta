<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 20.04.2016
 * Time: 17:02
 */

namespace app\extensions\parsing;


use app\components\Logger;

abstract class LogWritable
{
    const INFO = 1;
    const WARNING = 2;
    const ERROR = 3;
    const SUCCESS = 4;
    public static $MESSAGE_TYPE = array(
        self::INFO =>    'info   ',
        self::WARNING => 'warning',
        self::ERROR =>   'error  ',
        self::SUCCESS => 'success'
    );
    //static $log_filename;
    public static function logPush($message='', $log_filename, $type=1, $id=0)
    {
        if (array_key_exists($type,self::$MESSAGE_TYPE)) {
            $id = (!$id)?'':' {'.$id.'} ';
            (new Logger)->push('['.self::$MESSAGE_TYPE[$type].']'.$id.$message,$log_filename);
        }
    }
}