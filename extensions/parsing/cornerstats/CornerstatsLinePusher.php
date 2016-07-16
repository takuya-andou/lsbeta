<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 24.04.2016
 * Time: 3:10
 */

namespace app\extensions\parsing\cornerstats;


use app\extensions\parsing\LinePusher;
use app\modules\betting\models\Line;

class CornerstatsLinePusher extends LinePusher
{
    static $filename = 'cornerstatslinepusher.tmp';
    const STACK_SIZE = 50;
    const INSERTS_BEFORE_PAUSE=100;
    protected $inserts_done =0;
    const DB_STACK_SIZE=100;
    //protected $save_state_per = 250;
    public function run()
    {
        self::logPush('CornerstatsLinePusher. Linepusher started.', self::$log_filename, self::INFO);

        $this->getBets();

        self::logPush('CornerstatsLinePusher. Linepusher finished its work.', self::$log_filename, self::INFO);

    }

    protected function getBets()
    {
        $parser = new CornerstatsParserExtended();
        $starts = $this->readState();
        $bets_saved =0;
        if(isset($starts)){
            $start_category_id = $starts[0];
            $start_fapi_id = $starts[1];
            $cur_category_id = $start_category_id;
            $cur_fapi_id = $start_fapi_id;
            self::logPush('CornerstatsLinePusher. Starting from CID('.$start_category_id.') FAPIID('.$start_fapi_id.').', self::$log_filename, self::INFO);
            $matches = $parser->loadMatches($start_category_id, $start_fapi_id, self::STACK_SIZE);

        }
        else{
            self::logPush('CornerstatsLinePusher. Starting from the first elements.', self::$log_filename, self::INFO);
            $matches = $parser->loadMatches($start_category_id,$start_fapi_id, self::STACK_SIZE);
        }
        if(!empty($matches)){
            foreach($matches as $match){
                self::logPush('CornerstatsLinePusher. Processing match MID('.$match->id.').', self::$log_filename, self::INFO);
                $values = $parser->parseMatch($match, $cur_category_id, $cur_fapi_id);
                if($this->saveBets($values)){
                    $bets_saved++;

                    if($bets_saved%2 == 0)
                        if(isset($cur_category_id) && isset($cur_fapi_id))
                            $this->saveState($cur_category_id, $cur_fapi_id);
                }
            }
        }
        if(isset($cur_category_id) && isset($cur_fapi_id))
        $this->saveState($cur_category_id, $cur_fapi_id);
        else{
            self::logPush('CornerstatsLinePusher. Cannot save state.', self::$log_filename, self::ERROR);
        }
    }

    protected function saveState($category_id, $fapi_id){

        $str = $category_id.','.$fapi_id;

        $fp = fopen(\Yii::getAlias('@data').DIRECTORY_SEPARATOR.self::$filename, 'w');
        fwrite($fp, $str);
        fclose($fp);
        return true;
    }

    protected function readState(){
        if(file_exists(\Yii::getAlias('@data').DIRECTORY_SEPARATOR.self::$filename)){
            $fp = fopen(\Yii::getAlias('@data').DIRECTORY_SEPARATOR.self::$filename, 'r');
            $contents="";
            if($fp) {
                $contents = stream_get_contents($fp);
                fclose($fp);
            }
            if(!empty($contents)){
                $exp = explode(',', $contents);
                if(!empty($exp[0]) && !empty($exp[1])){
                    return array($exp[0], $exp[1]);
                }
            }
        }
        return null;
    }

    protected function saveBets($data)
    {
        if(!empty($data)){
            $db_stack = array();
            $tableName = 'soccer_line';
            $columnNameArray = ['id', 'date', 'json_string'];
            foreach($data as $coef){
                $data_to_write = array('source' => 'CSLP', 'data' => $coef);
                $json_data = json_encode($data_to_write);
                $model = new Line();
                $model->json_string = $json_data;
                date_default_timezone_set('UTC');
                $model->date = date('Y-m-d H:i:s');

                if($model->validate())
                    $db_stack[] = $model;
                if(count($db_stack) >= self::DB_STACK_SIZE){
                    \Yii::$app->db->createCommand()
                        ->batchInsert(
                            $tableName, $columnNameArray, $db_stack
                        )
                        ->execute();
                    $db_stack = array();
                }
            }
            if(count($db_stack) > 0){

                \Yii::$app->db->createCommand()
                    ->batchInsert(
                        $tableName, $columnNameArray, $db_stack
                    )
                    ->execute();
            }
            self::logPush('CornerstatsLinePusher. Coefs were saved in line.',self::$log_filename, self::SUCCESS);

        }
        return true;
    }


}