<?php
namespace app\extensions\parsing;


use app\extensions\parsing\cornerstats\CornerstatsDBSearcher;
use app\extensions\parsing\footballdata\FootballdataDBSearcher;
use app\extensions\parsing\marathonbet\MarathonbetDBSearcher;
use app\modules\betting\models\Bet;
use app\modules\betting\models\Line;
ini_set('max_execution_time', 900);
class LineHandler extends LogWritable
{
    const STACK_SIZE = 3000;
    static $log_filename = 'linehandler.log';
    private $new_match = false;
    private $prev_match_was_found=false;
    private $prev_match_id=null;
    private $prev_match_date=null;
    private $prev_match_member1=null;
    private $prev_match_member2=null;
    public function run(){
        self::logPush('LineHandler. LineHandler started.', self::$log_filename, self::INFO);
        $records = $this->retrieveStack();
        $this->processRecords($records);
        self::logPush('LineHandler. LineHandler finished its work.', self::$log_filename, self::INFO);

    }
    protected function processRecords($records){
        foreach($records as $record){
            $record_data = json_decode($record->json_string, true);
            //echo $record_data->source;
            $bet = null;
            switch($record_data['source']){
                case 'MBLP': $bet = $this->handleMarathonbetRecord($record_data['data']); break;
                case 'CSLP': $bet = $this->handleCornerstatsRecord($record_data['data']);break;
                case 'FDLP':$bet = $this->handleFootballdataRecord($record_data['data']); break;
                default: break;
            }
            if(isset($bet)){
                if($this->saveBet($bet) === false){
                    self::logPush('LineHandler. T('.$bet['type'].') E('.$bet['event'].') was not saved.', self::$log_filename, self::ERROR);
                }
                else{
                    //echo 'YES';
                    //self::logPush('LineHandler. Bet saved.', self::$log_filename, self::SUCCESS);
                }
            }
            $record->delete();
        }
    }
    protected function retrieveStack(){
        return Line::retrieveRecords(self::STACK_SIZE);
        //return Line::find()->where('id=20492')->all();
    }
    protected function saveBet($bet){
        return Bet::updateOrSave($bet);
    }
    protected function handleMarathonbetRecord($record_data){
        //сначала найти матч
        $bet =null;
        $searcher = new MarathonbetDBSearcher();
        $this->new_match = true;
        if($this->prev_match_was_found &&
            strcmp($this->prev_match_date, $record_data['match_datetime']) == 0 &&
            strcmp($this->prev_match_member1, $record_data['member1']) == 0 &&
            strcmp($this->prev_match_member2, $record_data['member2']) == 0){
            $id = $this->prev_match_id;
            $this->new_match = false;
        }
        else{
            if(strcmp($this->prev_match_date, $record_data['match_datetime']) == 0 &&
                strcmp($this->prev_match_member1, $record_data['member1']) == 0 &&
                strcmp($this->prev_match_member2, $record_data['member2']) == 0){
                $id = $this->prev_match_id;
                $this->new_match = false;
            }
            else{
                $id = $searcher->findEvent($record_data );
                $this->prev_match_date = $record_data['match_datetime'];
                $this->prev_match_member1 = $record_data['member1'];
                $this->prev_match_member2 = $record_data['member2'];
                $this->new_match = true;
            }
        }
        $this->prev_match_id= $id;
        if(isset($id)){
            $this->prev_match_was_found=true;
            $bet = array_merge($record_data, array('match_id' => $id));
        }
        else{
            $this->prev_match_was_found=false;
            if($this->new_match)
            self::logPush('LineHandler-handleMarathonbetRecord. Event M1: '.$record_data['member1'].' M2: '.$record_data['member2'].
                ' on '. (isset($record_data['match_datetime']) ? $record_data['match_datetime'] : 'undefined_date') .
                ' in League: '.$record_data['category_id'].' was not found.', self::$log_filename, self::ERROR);
        }
        return $bet;
    }
    protected function handleCornerstatsRecord($record_data){
        $bet =null;
        $id = $record_data['match_id'];
        if(isset($id)){
            self::logPush('LineHandler-handleCornerstatsRecord. Event match_id('.$record_data['match_id'] .
                ') was found!.', self::$log_filename, self::INFO);
            $bet = array_merge($record_data, array('match_id' => $id));
        }
        else{
            self::logPush('LineHandler-handleCornerstatsRecord. Event was not found!.', self::$log_filename, self::ERROR);
        }
        return $bet;
    }
    protected function handleFootballdataRecord($record_data){
        $bet =null;
        $searcher = new FootballdataDBSearcher();
        if(isset($this->prev_match_id) && isset($this->prev_match_date) && isset($this->prev_match_member1)&& isset($this->prev_match_member2)){
            if(strcmp($this->prev_match_date, $record_data['match_datetime']) == 0 &&
                strcmp($this->prev_match_member1, $record_data['member1']) == 0 &&
                strcmp($this->prev_match_member2, $record_data['member2']) == 0){
                $id = $this->prev_match_id;
            }
            else{
                $id = $searcher->findEvent($record_data );
                $this->prev_match_id= $id;
                $this->prev_match_date = $record_data['match_datetime'];
                $this->prev_match_member1 = $record_data['member1'];
                $this->prev_match_member2 = $record_data['member2'];
            }
        }
        else{
            $id = $searcher->findEvent($record_data );
            $this->prev_match_id= $id;
            $this->prev_match_date = $record_data['match_datetime'];
            $this->prev_match_member1 = $record_data['member1'];
            $this->prev_match_member2 = $record_data['member2'];
        }
        if(isset($id)){
            self::logPush('LineHandler-handleFootballdataRecord. Event M1: '.$record_data['member1'].' M2: '.$record_data['member2'].
                ' on '. (isset($record_data['match_datetime']) ? $record_data['match_datetime'] : 'undefined_date') .
                ' was found.', self::$log_filename, self::INFO);
            $bet = array_merge($record_data, array('match_id' => $id));
        }
        else{
            self::logPush('LineHandler-handleFootballdataRecord. Event M1: '.$record_data['member1'].' M2: '.$record_data['member2'].
                ' on '. (isset($record_data['match_datetime']) ? $record_data['match_datetime'] : 'undefined_date') .
                ' was not found.', self::$log_filename, self::ERROR);
        }
        return $bet;
    }

}