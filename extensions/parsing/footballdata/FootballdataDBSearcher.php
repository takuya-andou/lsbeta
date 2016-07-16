<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 19.04.2016
 * Time: 23:13
 */

namespace app\extensions\parsing\footballdata;


use app\extensions\parsing\DBSearcher;
use app\modules\soccer\models\Match;

class FootballdataDBSearcher extends DBSearcher
{
    public function findEvent($data)
    {
        $category_id = $data['category_id'];
        $member1 = $data['member1'];
        $member2 = $data['member2'];
        $date = $data['match_datetime'];
        $identity_threshold = FootballdataRequirement::$config['db_save_rules']['identity_threshold'];
        if(isset($date)) {
            $converted_datetime_obj = new \DateTime($date);
            $converted_datetime_obj->modify('-1 day');
            $lower_limit = $converted_datetime_obj->format('Y-m-d H:i:s');
            $converted_datetime_obj->modify('+2 day');
            $upper_limit = $converted_datetime_obj->format('Y-m-d H:i:s');

            $matches_in_range = Match::findByLeagueIdAndDateRange($category_id, $lower_limit, $upper_limit);

            if (!empty($matches_in_range))
                foreach ($matches_in_range as $match) {
                    $home_member = $match->home;
                    $away_member = $match->away;

                    //сначала проверить просто имена команд
                    $synonym_home_name = $home_member->name;
                    $synonym_away_name = $away_member->name;
                    if($this->compareStrWithSynonym($member1, $synonym_home_name, $identity_threshold) ||
                        $this->compareStrWithSynonym($member2, $synonym_away_name, $identity_threshold)){
                        $this->saveSynonyms($home_member->id, $home_member->name, $member1);
                        $this->saveSynonyms($away_member->id, $away_member->name, $member2);
                        return $match->id;
                    }

                    $home_synonyms = $home_member->teamSynonyms;
                    $away_synonyms = $away_member->teamSynonyms;

                    foreach($home_synonyms as $h_synonym){
                        if($this->compareStrWithSynonym($member1, $h_synonym->name, $identity_threshold)) {
                            $this->saveSynonyms($home_member->id, $home_member->name, $member1);
                            $this->saveSynonyms($away_member->id, $away_member->name, $member2);
                            return $match->id;
                        }
                    }
                    foreach($away_synonyms as $a_synonym){
                        if($this->compareStrWithSynonym($member2, $a_synonym->name, $identity_threshold)) {
                            $this->saveSynonyms($home_member->id, $home_member->name, $member1);
                            $this->saveSynonyms($away_member->id, $away_member->name, $member2);
                            return $match->id;
                        }
                    }
                }
        }
        else{
            self::logPush('FootballdataDBSearcher.  No date specified for event M1: '.$member1.' M2: '.$member2.'.', self::$log_filename, self::ERROR);
        }
        return null;

    }
}