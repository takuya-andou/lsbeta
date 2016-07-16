<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 17.04.2016
 * Time: 16:06
 */

namespace app\extensions\parsing;


use app\modules\betting\models\Bet;

abstract class DBSearcher extends LogWritable
{
    static $log_filename = 'db_save.log';

    public function __construct($params = array()) {

    }

    abstract public function findEvent($data);

    protected function compareStrWithSynonym($str, $synonym, $identity_threshold){
        $pers = StringSimilarity::getStringSimilarity(['str1' => $str, 'str2' => $synonym]);
        $pers_str_a = StringSimilarity::getStringSimilarity(['str1' => StringSimilarity::abbreviateText($str), 'str2' => $synonym]);
        $pers_synonym_a = StringSimilarity::getStringSimilarity(['str1' => $str, 'str2' => StringSimilarity::abbreviateText($synonym)]);

        if ($pers > $identity_threshold ||
            $pers_str_a > $identity_threshold ||
            $pers_synonym_a > $identity_threshold) {

            return true;
        }
        return false;
    }
    protected function saveSynonyms($id, $member_db_name, $parsed_name){
        if(strcmp($member_db_name, $parsed_name) != 0)
            if(StringSimilarity::saveSynonym($id, $parsed_name)){
                self::logPush('DBWriter. Synonym (' . $parsed_name . ') for (' . $member_db_name . ') was successfuly saved.', self::$log_filename, self::INFO);
            }
    }
}