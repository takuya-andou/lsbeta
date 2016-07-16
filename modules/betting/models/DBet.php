<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 14.07.2016
 * Time: 19:21
 */

namespace app\modules\betting\models;

use app\modules\betting\config\BettingConfig;
use app\modules\soccer\models\Match;
use app\modules\soccer\models\MatchStats;
use Yii;

abstract class DBet extends \yii\db\ActiveRecord
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = ['match_id', 'date', 'bookie_id', 'type_id', 'event_id', 'coef', 'member', 'sign', 'value'];
        $scenarios[self::SCENARIO_UPDATE] = ['match_id', 'date', 'bookie_id', 'type_id', 'event_id', 'coef', 'member', 'sign', 'value'];
        return $scenarios;
    }
    /**
     * функция преобразует значение ставки из бд в модельное
     * @return null|string
     */
    public function toModelType(){
        switch($this->type_id){
            case BettingConfig::TYPE_TOTAL: // total
                if($this->sign == -1) return '<'.$this->value;
                else if($this->sign == 1) return '>'.$this->value;
                else return null;
                break;
            default:
                return null;

        }
    }

    /**
     * @param $type_id
     * @param $event_id
     * @param $limits
     * @return array
     */
    public static function getLastBets($type_id, $event_id, $limits){
        $lower_coef = empty($limits['lower_coef']) ? null : $limits['lower_coef'];
        $upper_coef = empty($limits['upper_coef']) ? null : $limits['upper_coef'];
        $bets_num = empty($limits['bets_num']) ? null : $limits['bets_num'];
        $matches_num = empty($limits['matches_num']) ? null : $limits['matches_num'];
        $since_date = empty($limits['since_date']) ? null : $limits['since_date'];
        $until_date = empty($limits['until_date']) ? null : $limits['until_date'];
        $bookie_ids = empty($limits['bookie_ids']) ? null : $limits['bookie_ids'];
        /*выборка последних N матчей, на которые есть ставки у жанных буков.
        И при этом неважно что заданы пределы кэфов, матчи все равно считаются,
        просто на них не ставим.*/
        $matches_sorted_date_desc_query =  new \yii\db\Query();
        $matches_sorted_date_desc_query
            ->select('m.id as mid, m.date as mdate')
            ->from('{{%match}} m')
            ->rightJoin('{{%bet}} bb', 'bb.match_id = m.id')
            ->where('bb.type_id=:type_id and bb.event_id=:event_id',
                [':type_id' => $type_id, ':event_id' => $event_id])
            ->andWhere(['in','bb.bookie_id',$bookie_ids]);
        if(isset($lower_coef))
            $matches_sorted_date_desc_query->andWhere('bb.coef >= :l_coef', [':l_coef' => $lower_coef]);
        if(isset($upper_coef))
            $matches_sorted_date_desc_query->andWhere('bb.coef <= :u_coef', [':u_coef' => $upper_coef]);
        if(isset($since_date))
            $matches_sorted_date_desc_query->andWhere('m.date >= :s_date', [':s_date' => $since_date]);
        if(isset($until_date))
            $matches_sorted_date_desc_query->andWhere('m.date <= :u_date', [':u_date' => $until_date]);
        $matches_sorted_date_desc_query
            ->orderBy('m.date DESC');
        if(isset($bets_num))
            $matches_sorted_date_desc_query->limit($matches_num);
        $matches_sorted_date_desc_query->groupBy('m.id');
        /*
         * Теперь матчи расположены от самого давнего, до сегодняшнего
         */
        //return $matches_sorted_date_desc_query->all();
        $matches_sorted_date_asc_query = new \yii\db\Query();
        $matches_sorted_date_asc_query
            ->from(['sorted_desc' => $matches_sorted_date_desc_query])
            ->orderBy('mdate ASC');
        //return $matches_sorted_date_asc_query->all();
        $bets_query = self::find();
        $bets_query
            ->rightJoin(['mm' => $matches_sorted_date_asc_query], 'match_id=mm.mid')
            ->where('type_id=:type_id and event_id=:event_id',
                [':type_id' => $type_id, ':event_id' => $event_id])
            ->andWhere(['in','bookie_id',$bookie_ids]);
        if(isset($lower_coef))
            $bets_query->andWhere('coef >= :l_coef', [':l_coef' => $lower_coef]);
        if(isset($upper_coef))
            $bets_query->andWhere('coef <= :u_coef', [':u_coef' => $upper_coef]);
        $bets_query
            ->orderBy('mm.mdate ASC');
        if(isset($bets_num))
            $bets_query->limit($bets_num);
        return $bets_query->all();
    }

    /**
     * @return null|int
     */
    public function getBetResult(){
        $ret = null;
        switch($this->type_id){

            case BettingConfig::TYPE_TOTAL: return $this->getTotalResult(); break;
            case BettingConfig::TYPE_INDIVIDUAL_TOTAL: return $this->getIndividualTotalResult();
            case BettingConfig::TYPE_HANDICAP: return null;
            case BettingConfig::TYPE_1X3: return $this->getMatchOutcomeResult(); break;

                break;
            default:
        }
        return null;

    }

    private function getTotalResult(){
        $stat_id = BettingConfig::$BEtoMS_ACCORDIANCES[$this->event_id];
        $fact_result = MatchStats::getMatchStat($this->match_id, $stat_id);
        $fact_total = $fact_result['event_value'];
        $sign = $this->sign;
        $total_required = $this->value;

        switch($sign){
            case -1:
                if($fact_total < $total_required) return BettingConfig::BET_WIN;
                else if($fact_total == $total_required) return null;
                else return BettingConfig::BET_LOSS;
                break;
            case 1:
                if($fact_total > $total_required) return BettingConfig::BET_WIN;
                else if($fact_total == $total_required) return null;
                else return BettingConfig::BET_LOSS;
                break;
            default:
                break;
        }
        return null;
    }
    private function getIndividualTotalResult(){
        $stat_id = BettingConfig::$BEtoMS_ACCORDIANCES[$this->event_id];
        $fact_result = MatchStats::getMatchStat($this->match_id, $stat_id);
        if(empty($fact_result)) return null;
        $sign = $this->sign;
        $total_required = $this->value;
        $member = $this->member;
        switch($member){
            case BettingConfig::MEMBER1:
                $fact_total= $fact_result['home_value'];
                switch($sign){
                    case -1:
                        if($fact_total < $total_required) return BettingConfig::BET_WIN;
                        else if($fact_total == $total_required) return null;
                        else return BettingConfig::BET_LOSS;
                        break;
                    case 1:
                        if($fact_total > $total_required) return BettingConfig::BET_WIN;
                        else if($fact_total == $total_required) return null;
                        else return BettingConfig::BET_LOSS;
                        break;
                    default:
                        break;
                }
                break;
            case BettingConfig::MEMBER2:
                $fact_total= $fact_result['away_value'];
                switch($sign){
                    case -1:
                        if($fact_total < $total_required) return BettingConfig::BET_WIN;
                        else if($fact_total == $total_required) return null;
                        else return BettingConfig::BET_LOSS;
                        break;
                    case 1:
                        if($fact_total > $total_required) return BettingConfig::BET_WIN;
                        else if($fact_total == $total_required) return null;
                        else return BettingConfig::BET_LOSS;
                        break;
                    default:
                        break;
                }
                break;
            default: break;
        }

        return null;
    }
    private function getMatchOutcomeResult(){
        //$stat_id = BettingConfig::$BEtoMS_ACCORDIANCES[$this->event_id];
        $stat_id = BettingConfig::$BEtoMS_ACCORDIANCES[BettingConfig::EVENT_GOAL];
        $fact_result = MatchStats::getMatchStat($this->match_id, $stat_id);
        $home_goals = $fact_result['home_value'];
        $away_goals = $fact_result['away_value'];
        if(empty($fact_result)) return null;
        $winner = ($home_goals > $away_goals) ? BettingConfig::MEMBER1 : ($home_goals < $away_goals) ? BettingConfig::MEMBER2 : null;
        $result_required = $this->value;
        switch($result_required){
            case BettingConfig::WIN:
                $member = $this->member;
                if($member == $winner) return BettingConfig::BET_WIN;
                else return BettingConfig::BET_LOSS;
                break;
            case BettingConfig::WIN_OR_DRAW:
                $member = $this->member;
                if($winner == null || $member == $winner) return BettingConfig::BET_WIN;
                else return BettingConfig::BET_LOSS;
                break;
            case BettingConfig::DRAW:
                if($winner == null) return BettingConfig::BET_WIN;
                else return BettingConfig::BET_LOSS;
                break;
            case BettingConfig::NO_DRAW:
                if($winner != null) return BettingConfig::BET_WIN;
                else return BettingConfig::BET_LOSS;
                break;
            default: break;
        }

        return null;
    }

    /**
     * @param $match_id
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getBetsByMatchId($match_id){
        return self::find()
            ->where('match_id=:match_id', [':match_id' => $match_id])
            ->all();
    }

    /**
     * @return string
     */
    public function toString(){
        $str = BettingConfig::$TYPES[$this->type_id].'|'.BettingConfig::$EVENTS[$this->event_id].'|';
        switch($this->type_id){
            case BettingConfig::TYPE_TOTAL:
                $str.= BettingConfig::$TOTAL_SIGNS[$this->sign].'|'.$this->value;
                break;
            case BettingConfig::TYPE_INDIVIDUAL_TOTAL:
                $str.= BettingConfig::$MEMBERS[$this->member].'|'.BettingConfig::$TOTAL_SIGNS[$this->sign].'|'.$this->value;
                break;
            case BettingConfig::TYPE_HANDICAP:
                $str.= BettingConfig::$MEMBERS[$this->member].'|'.BettingConfig::$HANDICAP_SIGNS[$this->sign].'|'.$this->value;
                break;
            case BettingConfig::TYPE_1X3:
                $str.= BettingConfig::$MATCH_OUTCOMES[intval($this->value)].(isset($this->member) ? '|'.BettingConfig::$MEMBERS[$this->member] : '');
                break;
            case BettingConfig::TYPE_ASIAN_TOTAL:
                $str.= BettingConfig::$TOTAL_SIGNS[$this->sign].'|'.$this->value;
                break;
            case BettingConfig::TYPE_ASIAN_HANDICAP:
                $str.= BettingConfig::$MEMBERS[$this->member].'|'.BettingConfig::$HANDICAP_SIGNS[$this->sign].'|'.$this->value;
                break;
        }
        return $str;
    }

    /**
     * @param $data
     * @return bool|string
     * @throws \Exception
     */
     public static function updateOrSave($data){
        $type_id = $data['type_id'];
        $event_id = $data['event_id'];
        if (isset($type_id) && isset($event_id)) {
            $existing_bet = static::isNewBet($data);
            //$existing_bet = null;
            if(empty($existing_bet)){
                $class_name = '\\'.static::className();
                $bet = new $class_name;
                $bet->scenario=self::SCENARIO_CREATE;
                $bet->match_id = $data['match_id'];
                $bet->bookie_id = $data['bookie_id'];
                $bet->external_match_id = (isset($data['external_match_id']) ? strval($data['external_match_id']) : 'undef');
                $bet->type_id = $type_id;
                $bet->event_id = $event_id;
                if(isset($data['member'])) $bet->member = $data['member'];
                if(isset($data['sign'])) $bet->sign = $data['sign'];
                if(isset($data['value'])) $bet->value = floatval($data['value']);
                $bet->date = $data['date'];
                if(isset($data['coef'])) $bet->coef = $data['coef'];
                if ($bet->save(false)) {
                    return $bet;
                } else {
                   // print_r( $bet->errors);
                    return false;
                }
            }
            else{
                $existing_bet->scenario=self::SCENARIO_UPDATE;
                $existing_bet->update_date = isset($data['update_date']) ? $data['update_date']: $data['date'];
                if(isset($data['coef'])) $existing_bet->coef = $data['coef'];
                if ($existing_bet->save(false)) {
                    return $existing_bet;
                } else{
                  //  print_r( $existing_bet->errors);
                    return false;
                }
            }
        }
        else{
            return false;
        }
    }

    /**
     * @param $data
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function isNewBet($data)
    {
        $query = static::find()
            ->where('match_id=:match_id',  [':match_id' => $data['match_id']])
            ->andWhere('type_id=:type_id',  [':type_id' => $data['type_id']])
            ->andWhere('event_id=:event_id',  [':event_id' => $data['event_id']])
            ->andWhere('bookie_id=:bookie_id',  [':bookie_id' => $data['bookie_id']]);
        if(isset($data['value']))
            $query->andWhere('value=:value',  [':value' => $data['value']]);
        if(isset($data['member']))
            $query->andWhere('member=:member',  [':member' => $data['member']]);
        if(isset($data['sign']))
            $query->andWhere('sign=:sign',  [':sign' => $data['sign']]);
        return $query->one();
    }

    abstract public function getForecasts();
}