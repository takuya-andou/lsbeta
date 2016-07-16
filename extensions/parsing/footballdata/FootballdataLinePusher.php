<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 24.04.2016
 * Time: 16:14
 */

namespace app\extensions\parsing\footballdata;


use app\extensions\parsing\LinePusher;
use app\modules\betting\models\Line;

class FootballdataLinePusher extends LinePusher
{
    static $filename = 'footballdatalinepusher.tmp';
    const STACK_SIZE = 100;
    public function run()
    {
        self::logPush('FootballdataLinePusher. Linepusher started.', self::$log_filename, self::INFO);
        $loader = new FootballdataLoader();
        $documents = $loader->loadData();
        $parser = new FootballdataCSVParser();
        $initial_offset =0;
        foreach($documents as $document){
            $initial_offset = $this->readDocumentOffset($document['file']);
            if(!isset($initial_offset)) $initial_offset = 0;
            $data = $parser->parseStack($document['category_id'], $document['file'], self::STACK_SIZE, $initial_offset);
            $rows_parsed = $data['rows_parsed'];
            if($this->saveBets($data['data'])){
                $this->saveDocumentOffset($document['file'], intval($initial_offset)+$rows_parsed);
            }
        }
        self::logPush('FootballdataLinePusher. Linepusher finished its work.', self::$log_filename, self::INFO);

        // TODO: Implement run() method.
    }

    /**
     * @return mixed
     * данные в формате:
     * обработчик
     * данные
     */
    protected function getBets()
    {
        // TODO: Implement getBets() method.
    }

    protected function saveDocumentOffset($document, $offset){
        $offsets = $this->readDocumentOffset();
        $pattern = '/'.$document.'\[(\d+)\]/U';
        $str = $document.'['.$offset.']';
        if(!empty($offsets) && preg_match($pattern, $offsets)){
            $str = preg_replace($pattern, $str, $offsets);
        }
        else{
            $offsets.=$str;
            $str = $offsets;
        }

        $fp = fopen(\Yii::getAlias('@data').DIRECTORY_SEPARATOR.self::$filename, 'w');
        fwrite($fp, $str);
        fclose($fp);
        return true;
    }

    protected function readDocumentOffset($document = null){
        if(file_exists(\Yii::getAlias('@data').DIRECTORY_SEPARATOR.self::$filename)){
            $fp = fopen(\Yii::getAlias('@data').DIRECTORY_SEPARATOR.self::$filename, 'r');
            $contents="";
            if($fp) {
                $contents = stream_get_contents($fp);
                fclose($fp);
            }
            if(isset($document)){
                $pattern = '/'.$document.'\[(\d+)\]/U';
                if(preg_match($pattern, $contents, $match)){
                    return $match[1];
                }
            }
            else{
                return $contents;
            }
        }
        return null;
    }

    protected function saveBets($data)
    {
        if(!empty($data))
            foreach($data as $coef){
                $data_to_write = array('source' => 'FDLP', 'data' => $coef);
                $json_data = json_encode($data_to_write);
                $model = new Line();
                $model->json_string = $json_data;
                if($model->save()){
                    self::logPush('FootballdataLinePusher. Coef. M1('.$coef['member1'].') M2('.$coef['member2'].') saved in line.',self::$log_filename, self::SUCCESS);
                }
                else{
                    self::logPush('FootballdataLinePusher. Coef. M1('.$coef['member1'].') M2('.$coef['member2'].') was not saved in line.',self::$log_filename, self::ERROR);
                }
            }
        return true;
    }

}