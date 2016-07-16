<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 23.04.2016
 * Time: 23:01
 */

namespace app\extensions\parsing;

ini_set('max_execution_time', 900);
abstract class LinePusher extends LogWritable
{
    static $log_filename = 'linepusher.log';

    abstract public function run();

    /**
     * @return mixed
     * данные в формате:
     * обработчик
     * данные
     */
    abstract protected function getBets();

    abstract protected function saveBets($data);
}