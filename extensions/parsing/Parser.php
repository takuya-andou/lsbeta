<?php
namespace app\extensions\parsing;
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 12.04.2016
 * Time: 19:02
 */
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', 1500);
ini_set('memory_limit', '-1');
abstract class Parser extends  LogWritable
{
    /*const MEMBER1 = 1;
    const MEMBER2 = 2;
    const NO_MEMBER = 0;
    const DRAW = 0;
    const WIN = 1;
    const WIN_OR_DRAW = 2;
    const NO_DRAW = 3;
    const LESSER_THAN = -1;
    const EQUAL = 0;
    const GREATER_THAN =1;*/

    static $log_filename = 'parsing.log';

    //abstract public function run();

}