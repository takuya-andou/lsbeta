<?php

/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 21.04.2016
 * Time: 0:46
 */
class Parser
{
    public static $filename = 'fcparser.tmp';
    public function execute() {
        $parser = new \app\extensions\parsing\footballdata\FootballdataCSVParser();
        $val = $this->readVal();
        $parser->setLastRowParsed($val);
        $parser->run();
    }
    protected function saveVal($start_str = null){
        $fp = fopen(\Yii::getAlias('@data').DIRECTORY_SEPARATOR.self::$filename, 'w');
        if(isset($start_str)) fwrite($fp, $start_str);
        fclose($fp);
        return true;
    }
    protected function readVal(){
        $start_str = null;
        if(file_exists(\Yii::getAlias('@data').DIRECTORY_SEPARATOR.self::$filename)){
            $fp = fopen(\Yii::getAlias('@data').DIRECTORY_SEPARATOR.self::$filename, 'r');
            $contents="";
            if($fp) {
                $start_str = stream_get_contents($fp);
                fclose($fp);
            }
        }

        return $start_str;
    }
}