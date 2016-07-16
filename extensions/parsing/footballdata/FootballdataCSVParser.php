<?php

/**
 * Date: 16.04.2016
 * Используется для парсинга скачанных документов. Если необходимо сам загружает их сайта.
 */
namespace app\extensions\parsing\footballdata;

use app\extensions\parsing\DBWriter;
use app\extensions\parsing\FileParser;
use app\extensions\parsing\footballdata\helpers\Helper;
use app\modules\betting\config\BettingConfig;

ini_set('max_execution_time', 3600);
class FootballdataCSVParser extends FileParser
{
    protected $last_row_parsed;

    public function getLastRowParsed()
    {
        return $this->last_row_parsed;
    }

    public function setLastRowParsed($last_row_parsed)
    {
        $this->last_row_parsed = $last_row_parsed;
    }
    protected $rows_parsed =0;
    const ROWS_TO_PARSE_NUM=100;
    public function parseStack($category_id, $document, $stack_size, $offset = 0){
        $row_n = 0;
        $rows_parsed = 0;
        $index_map = array();
        $coefs_required = FootballdataRequirement::$config['parser_rules']['coefs_required'];
        $coefs_required_keys = array_keys($coefs_required);
        $fields_required = FootballdataRequirement::$config['parser_rules']['fields_required'];
        $data = array();

        if (($handle = fopen(\Yii::getAlias('@data').DIRECTORY_SEPARATOR.FootballdataRequirement::$config['loader_rules']['save_path'].
                DIRECTORY_SEPARATOR.$document, "r")) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if($row_n==0){//словарь. из словаря выбрать нужные индексы по имени
                    $count = count($row);
                    for($index = 0; $index < $count; $index++){
                        if(array_search($row[$index], $coefs_required_keys) !== false || array_search($row[$index], $fields_required) !== false ){
                            $index_map[$row[$index]] = $index;
                        }
                    }
                }
                else{
                    if($row_n < $offset){
                        $row_n++;
                        continue;
                    }
                    $member1_title = FootballdataRequirement::$config['parser_rules']['fields_required']['member1'];
                    $member2_title = FootballdataRequirement::$config['parser_rules']['fields_required']['member2'];
                    $date_title = FootballdataRequirement::$config['parser_rules']['fields_required']['match_datetime'];

                    foreach($coefs_required as $key => $coef_required){
                        if(!isset($index_map[$key])) continue;
                        $successfully_parsed = true;

                        $total = array();
                        switch($coef_required['type']){
                            case '1x3':
                                switch (substr($key, -1)){
                                    case 'H': $total['member'] = BettingConfig::MEMBER1; $total['value'] = BettingConfig::WIN; break;
                                    case 'D': $total['member'] = null; $total['value'] = BettingConfig::DRAW; break;
                                    case 'A': $total['member'] = BettingConfig::MEMBER2; $total['value'] = BettingConfig::WIN; break;
                                    default: //TODO: дописать логирование
                                        self::logPush('FootballdataCSVParser. Key title is wrong. '.$key.'.', self::$log_filename, self::ERROR);
                                        $successfully_parsed = false;
                                        break;
                                }
                                break;
                            case 'total':
                                $preg_pattern = '/\w*(>|<)\d+[\.,]?\d*/';
                                if(preg_match($preg_pattern, $key)){
                                    if(strpos($key, '<') !== false) $total['sign'] = BettingConfig::LESSER_THAN;
                                    else $total['sign'] = BettingConfig::GREATER_THAN;
                                    $num_pattern = '/(>|<)\d+[\.,]?\d*/';
                                    preg_match($num_pattern, $key, $match);
                                    //print_r($match);
                                    $total['value'] = $match[0];
                                    $total['value'] = trim($total['value'], '<');
                                    $total['value'] = trim($total['value'], '>');
                                }
                                else{
                                    $successfully_parsed = false;
                                    self::logPush('FootballdataCSVParser. Parse error key doesn\'t match pattern is wrong.'. $key.'.', self::$log_filename, self::ERROR);
                                }
                                break;
                            default: //TODO: дописать логирование
                                $successfully_parsed = false;
                                self::logPush('FootballdataCSVParser. Wrong type for key.'. $key.'.', self::$log_filename, self::ERROR);
                                break;
                        }
                        if(!empty($row[$index_map[$key]])){
                            $total['coef'] = $row[$index_map[$key]];
                        }
                        else{
                            $successfully_parsed = false;
                            self::logPush('FootballdataCSVParser. Coef is empty for.'. $key.'.', self::$log_filename, self::ERROR);

                        }

                        $total['bookie_id'] = $coef_required['id'];
                        if($successfully_parsed){
                            $data[] = array_merge(array(
                                'category_id' => $category_id,
                                'type' => $coef_required['type'],
                                'event' => $coef_required['event'],
                                'member1' => $row[$index_map[$member1_title]],
                                'member2' => $row[$index_map[$member2_title]],
                                'match_datetime' => helpers\Helper::convertDate($row[$index_map[$date_title]]),
                                'date' => date('Y-m-d H:i:s'),
                            ),$total);
                        }
                            //$totals[$key]['values'][] = $total;
                        else{
                            self::logPush('FootballdataCSVParser. Total wasn\'t parsed.'.$key.'.', self::$log_filename, self::ERROR);
                        }
                    }
                    //$totals = array('type' => $coef_required['type'], 'values' => $totals);
                    date_default_timezone_set('UTC');
                    $rows_parsed++;
                }
                $row_n++;

                if($rows_parsed >= $stack_size){
                    break;
                }
            }
            fclose($handle);
        }
        else{
            //self::logPush('FootballdataCSVParser. Unable to open file for '.$country.'-'.$season.'-'.$league.'.', self::$log_filename, self::ERROR);
        }
        return array('data' => $data, 'rows_parsed' => $rows_parsed);
    }
    public function run()
    {
        self::logPush('FootballdataCSVParser. Parsing started.', self::$log_filename, self::INFO);

        $loader = new FootballdataLoader();
        $data = $loader->loadData();
        if(!empty($data)){
            $data = $this->parseData($data);
            if(!empty($data)){
                $this->saveData($data);
            }
            else{
                self::logPush('FootballdataCSVParser. Data to save is empty.', self::$log_filename, self::ERROR);
                return false;
            }
        }
        else{
            self::logPush('FootballdataCSVParser. Data to parse is empty.', self::$log_filename, self::ERROR);
            return false;
        }

        /*echo '<pre>';

        print_r($data);
        echo '</pre>';*/

        self::logPush('FootballdataCSVParser. Parsing is over.', self::$log_filename, self::INFO);
        return $data;
    }

    /**
     * @param $data - массив документов сгруппированных по странам - сезонам - лигам
     * @return mixed - массив коэффициентов сгруппированных по странам - сезонам - лигам - типам
     */
    protected function parseData($data){

        $result_data = array();
        foreach($data as $country => $seasons){
            foreach($seasons as $season => $leagues){
                foreach($leagues as $league => $info){
                    $result_data[$info['category_id']] = $info;
                    $result_data[$info['category_id']]['events'] = array();

                    $totals = array();
                    $row_n = 1;
                    $index_map = array();
                    $coefs_required = FootballdataRequirement::$config['parser_rules']['coefs_required'];
                    $coefs_required_keys = array_keys($coefs_required);
                    $fields_required = FootballdataRequirement::$config['parser_rules']['fields_required'];

                    if (($handle = fopen(Helper::getFilePath($country,$season,$league), "r")) !== FALSE) {
                        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                            $event = array();
                            if($row_n==1){//словарь. из словаря выбрать нужные индексы по имени
                                $count = count($row);
                                for($index = 0; $index < $count; $index++){
                                    if(array_search($row[$index], $coefs_required_keys) !== false || array_search($row[$index], $fields_required) !== false ){
                                        $index_map[$row[$index]] = $index;
                                    }
                                }
                            }
                            else{
                                if(isset($this->last_row_parsed) && $row_n < $this->last_row_parsed){
                                    $row_n++;
                                    continue;
                                }
                                $member1_title = FootballdataRequirement::$config['parser_rules']['fields_required']['member1'];
                                $member2_title = FootballdataRequirement::$config['parser_rules']['fields_required']['member2'];
                                $date_title = FootballdataRequirement::$config['parser_rules']['fields_required']['match_datetime'];
                                $totals=array();
                                foreach($coefs_required as $key => $coef_required){
                                    if(!isset($index_map[$key])) continue;
                                    $successfully_parsed = true;
                                    $totals[$key] = array('title' => $coef_required['title'],
                                        'type' => $coef_required['type'],
                                        'event' => $coef_required['event']);

                                    $total = array();
                                    switch($coef_required['type']){
                                        case '1x3':
                                            switch (substr($key, -1)){
                                                case 'H': $total['member'] = BettingConfig::MEMBER1; $total['value'] = BettingConfig::WIN; break;
                                                case 'D': $total['member'] = null; $total['value'] = BettingConfig::DRAW; break;
                                                case 'A': $total['member'] = BettingConfig::MEMBER2; $total['value'] = BettingConfig::WIN; break;
                                                default: //TODO: дописать логирование
                                                    self::logPush('FootballdataCSVParser. Key title is wrong. '.$key.'.', self::$log_filename, self::ERROR);
                                                    $successfully_parsed = false;
                                                    break;
                                            }
                                            break;
                                        case 'total':
                                            $preg_pattern = '/\w*(>|<)\d+[\.,]?\d*/';
                                            if(preg_match($preg_pattern, $key)){
                                                if(strpos($key, '<') !== false) $total['sign'] = BettingConfig::LESSER_THAN;
                                                else $total['sign'] = BettingConfig::GREATER_THAN;
                                                $num_pattern = '/(>|<)\d+[\.,]?\d*/';
                                                preg_match($num_pattern, $key, $match);
                                                //print_r($match);
                                                $total['value'] = $match[0];
                                                $total['value'] = trim($total['value'], '<');
                                                $total['value'] = trim($total['value'], '>');
                                            }
                                            else{
                                                $successfully_parsed = false;
                                                self::logPush('FootballdataCSVParser. Parse error key doesn\'t match pattern is wrong.'. $key.'.', self::$log_filename, self::ERROR);
                                            }
                                            break;
                                        default: //TODO: дописать логирование
                                            $successfully_parsed = false;
                                            self::logPush('FootballdataCSVParser. Wrong type for key.'. $key.'.', self::$log_filename, self::ERROR);
                                            break;
                                    }
                                    if(!empty($row[$index_map[$key]])){
                                        $total['coef'] = $row[$index_map[$key]];
                                    }
                                    else{
                                        $successfully_parsed = false;
                                        self::logPush('FootballdataCSVParser. Coef is empty for.'. $key.'.', self::$log_filename, self::ERROR);

                                    }

                                    $total['bookie_id'] = $coef_required['id'];
                                    if($successfully_parsed)
                                    $totals[$key]['values'][] = $total;
                                    else{
                                        self::logPush('FootballdataCSVParser. Total wasn\'t parsed.'.$key.'.', self::$log_filename, self::ERROR);
                                    }
                                }
                                //$totals = array('type' => $coef_required['type'], 'values' => $totals);
                                date_default_timezone_set('UTC');

                                $event = array(
                                    'member1' => $row[$index_map[$member1_title]],
                                    'member2' => $row[$index_map[$member2_title]],
                                    'match_datetime' => helpers\Helper::convertDate($row[$index_map[$date_title]]),
                                    'date' => date('Y-m-d H:i:s'),
                                    'totals_grouped' => array(
                                        array(
                                            'title' => 'default_group',
                                            'totals' => $totals
                                        ),

                                    )
                                    );
                                $this->rows_parsed++;
                                $this->last_row_parsed = $row_n;
                            }
                            if(!empty($event)){
                                $result_data[$info['category_id']]['events'][] = $event;
                                unset($result_data[$info['category_id']]['file']);
                            }

                            //if($row_n==5) break;
                            $row_n++;
                            if($this->rows_parsed >= self::ROWS_TO_PARSE_NUM){
                                break;
                            }
                        }
                        fclose($handle);
                    }
                    else{
                        self::logPush('FootballdataCSVParser. Unable to open file for '.$country.'-'.$season.'-'.$league.'.', self::$log_filename, self::ERROR);
                    }
                }
            }
        }

        return $result_data;
    }
    protected function saveData($data){
        $db_writer = new FootballdataDBWriter();
        return $db_writer->write($data);
    }

}