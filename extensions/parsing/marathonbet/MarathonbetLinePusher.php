<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 23.04.2016
 * Time: 23:23
 */

namespace app\extensions\parsing\marathonbet;


use app\extensions\parsing\LinePusher;
use app\modules\betting\models\Line;

class MarathonbetLinePusher extends LinePusher
{
    const DB_STACK_SIZE=50;
    public function run()
    {
        self::logPush('MarathonbetLinePusher. Linepusher started.', self::$log_filename, self::INFO);
        $data = $this->getBets();


        self::logPush('MarathonbetLinePusher. Linepusher finished its work.', self::$log_filename, self::INFO);
    }

    /**
     * должен вернуть
     * обработчик
     * данные [
     * лига
     * команда 1
     * команда 2
     * ИД бука
     * дата
     * тип кэфа
     * событие
     * значение
     * коэф
     * ]
     */
    protected function getBets()
    {
        $parser = new MarathonbetFootballParser();
        $page = $parser->getMainPage();
        $data = null;
        if ($page) {
            $html = str_get_html($page['content']);
            if (!empty($html)) {
                $categories = $parser->getCategories($html);
                $html->clear();
                $html = null;
                if(!empty($categories)){
                    foreach($categories as $category){
                        $events = $parser->getCategoryEvents($category);
                        //print_r($data);
                        foreach($events as $event){
                            $totals = $parser->getEventTotals($event);
                            $this->saveBets($totals);
                        }
                    }
                }
                else{
                    self::logPush('MarathonbetLinePusher. No required categories found.',self::$log_filename, self::ERROR);
                }
            }
            else{
                self::logPush('MarathonbetLinePusher. Simple html cannot process page.',self::$log_filename, self::ERROR);
            }
        }
        else{
            self::logPush('MarathonbetLinePusher. HTML page is empty.',self::$log_filename, self::ERROR);
        }
        return $data;
    }

    protected function saveBets($data)
    {
        if(!empty($data)){
            $db_stack = array();
            $tableName = 'soccer_line';
            $columnNameArray = ['id', 'date', 'json_string'];
            foreach($data as $coef){
                $data_to_write = array('source' => 'MBLP', 'data' => $coef);
                $json_data = json_encode($data_to_write);
                $model = new Line();
                $model->json_string = $json_data;
                //date_default_timezone_set('UTC');
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
            //self::logPush('MarathonbetLinePusher. Coefs were saved in line.',self::$log_filename, self::SUCCESS);

        }

    }


}