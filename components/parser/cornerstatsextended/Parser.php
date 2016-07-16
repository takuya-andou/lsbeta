<?php
namespace app\components\parser\cornerstatsextended;
use Yii;
use app\extensions\parsing\cornerstats\CornerstatsParserExtended;
class Parser
{
    public static $filename = 'csparser.tmp';
    public function execute(){

        $cs_parser = new CornerstatsParserExtended();
        $pair = $this->readPair();
        if(isset($pair[0]))
        $cs_parser->setStartFapiId($pair[0]);
        if(isset($pair[1]))
            $cs_parser->setStartCompetitionId($pair[1]);

        if($cs_parser->run()!=false){
            echo 'ASCID: '. $cs_parser->getStartCompetitionId().'<br>';
            echo 'AFAPIID: '. $cs_parser->getStartFapiId().'<br>';
            $fapi_id = $cs_parser->getStartFapiId();
            $comp_id = $cs_parser->getStartCompetitionId();
            $this->savePair($fapi_id, $comp_id);
        }
    }

    protected function savePair($start_fapi_id = null, $start_competition_id = null){
        $fp = fopen(\Yii::getAlias('@data').DIRECTORY_SEPARATOR.self::$filename, 'w');
        if(isset($start_fapi_id)) fwrite($fp, $start_fapi_id.',');
        if(isset($start_competition_id)) fwrite($fp, $start_competition_id);
        fclose($fp);
        return true;
    }
    protected function readPair(){
        if(file_exists(\Yii::getAlias('@data').DIRECTORY_SEPARATOR.self::$filename)){
            $fp = fopen(\Yii::getAlias('@data').DIRECTORY_SEPARATOR.self::$filename, 'r');
            $contents="";
            if($fp) {
                $contents = stream_get_contents($fp);
                fclose($fp);
            }
            $arr = explode(',', $contents);
        }

        $start_fapi_id=null;
        if(isset($arr[0]))
            $start_fapi_id = $arr[0];
        $start_competition_id = null;
        if(isset($arr[1]))
            $start_competition_id= $arr[1];
        return array($start_fapi_id, $start_competition_id);
    }
}