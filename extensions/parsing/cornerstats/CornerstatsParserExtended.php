<?php
namespace app\extensions\parsing\cornerstats;
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 21.04.2016
 * Time: 14:47
 */
use app\extensions\parsing\helpers\HTMLHelper;
use app\extensions\parsing\HTMLParser;
use app\modules\betting\config\BettingConfig;
use app\modules\soccer\models\Competition;
use app\modules\soccer\models\Match;
use Yii;
//TODO: переделать тип и эвенты на константные
class CornerstatsParserExtended extends HTMLParser
{
    protected $start_competition_id=null;
    public static $start_competition_id_key = 'start_competition_id_key';

    public function getStartCompetitionId()
    {
        return $this->start_competition_id;
    }
    public function setStartCompetitionId($start_competition_id)
    {
        $this->start_competition_id = $start_competition_id;
    }
    public function nextStartCompetitionId(){
        $categories = CornerstatsRequirement::$config['parsing_rules']['categories']['list'];
        if(isset($this->start_competition_id) && !empty($categories)) {
            $keys = array_keys($categories);
            $cur_ind = array_search($this->start_competition_id, $keys);
            if ($cur_ind !== false) {
                $cur_ind++;
            } else {
                return null;
            }
            if (isset($keys[$cur_ind])) {
                return $keys[$cur_ind];
            }
        }
            return null;
    }
    public function getStackCount()
    {
        return $this->stack_count;
    }
    public function setStackCount($stack_count)
    {
        $this->stack_count = $stack_count;
    }

    public function getStartFapiId()
    {
        return $this->start_fapi_id;
    }
    public function setStartFapiId($start_fapi_id)
    {
        $this->start_fapi_id = $start_fapi_id;
    }

    protected function pauseRequests(){
        $this->cur_requests_num = 0;
        sleep(self::PAUSE_LENGTH);
    }

    public function __construct($params = array()) {
        parent::__construct(array_merge($params, CornerstatsRequirement::$config['connection_rules'] ));
    }

    public function parseMatch($match, &$start_category_id, &$start_fapi_id){
        $totals_group_required = CornerstatsRequirement::$config['parsing_rules']['totals'];
        $data = array();
        if(isset($match->fapi_id)){
            $start_fapi_id = $fapi_id = $match->fapi_id;
            $match_id = $match->id;
            $start_category_id = $match->competition_id;
            foreach($totals_group_required as $totals_of_group_required){
                //$fapi_id = 79788;
                $page = $this->connector->curl($totals_of_group_required['url'].'&id='.$fapi_id, null);
                $this->incrementRequests();
                if(!empty($page['content'])){
                    $totals_table = json_decode($page['content']);
                    $html_table = str_get_html($totals_table->output);
                    if(!empty($html_table)){
                        $handler = $totals_of_group_required['handler'];
                        $result = $this->$handler($html_table, $totals_of_group_required, $fapi_id, $match_id);
                        if(isset($result))
                            $data = array_merge($data,$result);
                    }
                    else{
                        self::logPush('CornerstatsParserExtended. Simple html cannot process page.', self::$log_filename, self::ERROR);
                    }
                }
                else{
                    self::logPush('CornerstatsParserExtended. Empty html page.', self::$log_filename, self::ERROR);
                }
            }
        }
        if(!empty($data)){
            return $data;
        }
        else return null;

    }

    public function loadMatches(&$start_category_id, &$start_fapi_id, $stack_size){
        $categories = CornerstatsRequirement::$config['parsing_rules']['categories']['list'];
        $matches = array();
        if(!empty($categories)){
            foreach($categories as $key => $category){
                if(isset($start_category_id) && $key < $start_category_id) continue;
                if(count($matches) < $stack_size){
                    if(!isset($start_fapi_id) || $key != $start_category_id ){
                        $matches = array_merge($matches,Match::getStackOfMatchesFapiIDAndCompetition(0, $key, $stack_size));
                    }
                    else
                    $matches = array_merge($matches,Match::getStackOfMatchesFapiIDAndCompetition($start_fapi_id, $key, $stack_size));

                }
                else break;
            }
        }
        else{

            if(!isset($start_fapi_id)){
                $query = new \yii\db\Query;
                $query->select('min(fapi_id)')
                    ->from('soccer_match');
                $row = $query->one();
                $start_fapi_id = $row['min(fapi_id)'];
                $start_fapi_id--;
            }
            $matches = Match::getStackOfMatchesFapiID($start_fapi_id, $stack_size);
        }
        if(!empty($matches))
        return $matches;
        else return null;
    }
    protected function parseTable($html, $totals_of_group_required, $fapi_id, $match_id){
        $values = array();

        //structure bookmaker|1|X|2|member1|member2|Total|Under|Over
        $table = HTMLHelper::findOne('table[class=odds_table]', $html);
        if(isset($table)){
            $table_trs = HTMLHelper::findAll('tr', $table);
            $current_bookie_name = null;
            $current_bookie_id=null;
            $first_row = true;
            //для того, чтобы спарсить вторую строчку кэфов double chance введена перменная
            $bookie_first_row = false;
            if($this->checkTableStructure($table_trs[0])){
                foreach($table_trs as $table_tr){
                    //echo $i++.'<br>';
                    //echo count($table_tr->children()).'<br>';
                    //10 - expanded, 11 - no + icon, 12 - with + icon
                    if(count($table_tr->children()) == 12 || count($table_tr->children()) == 11) {
                        $prev_bookie_id = $current_bookie_id;
                        $current_bookie_id = $this->getCurrentBookie($table_tr->children(0), $current_bookie_id);
                        if($current_bookie_id == null){
                            $current_bookie_id = $prev_bookie_id;
                            continue;
                        }
                        if(isset($current_bookie_id)){
                            if(!isset($prev_bookie_id) || ($prev_bookie_id != $current_bookie_id))
                                $bookie_first_row=true;
                            else
                                $bookie_first_row = false;
                            //если $bookie_first_row=1, то это обычный 1х2, если нет, то это double chance
                            //но он пока что не парсится
                            $current_bookie_name = CornerstatsRequirement::$config['parsing_rules']['bookies'][$current_bookie_id]['title'];
                            $values = array_merge($values, $this->processTableRow11and12($table_tr, $current_bookie_id, $totals_of_group_required, $bookie_first_row, $fapi_id, $match_id));
                        }

                    }
                    else if(count($table_tr->children()) == 10 && !$first_row) {
                        $values = array_merge($values, $this->processTableRow10($table_tr, $current_bookie_id, $totals_of_group_required,$fapi_id, $match_id));
                    }
                    else{
                        self::logPush('CornerstatsParserExtended. Table found for FAPIID('.$fapi_id.') but it is unparseable.',self::$log_filename, self::ERROR);
                    }
                    $first_row = false;
                }
            }
            else{

            }
        }
        else{
            self::logPush('CornerstatsParserExtended. Table not found for FAPIID('.$fapi_id.').',self::$log_filename, self::ERROR);
        }

        if(!empty($values)){
            return $values;
        }
        else return null;
    }
    protected function getCurrentBookie($td, $cur_bookie_id){
        //$a_bookie = HTMLHelper::findOne('a',$td);
        $a_bookie = $td->children(0);
        $pat_a = '/<object(.*)/m';
        if(!isset($a_bookie ) || empty($a_bookie->innertext) || preg_match($pat_a, $td->innertext)) {
            return $cur_bookie_id;
        }
        $bookie_title = $a_bookie->innertext;
        //echo $bookie_title;
        //если сменился бук, то надо изменить ид
        //var_dump($cur_bookie);
        if(isset($cur_bookie_id)){
            $cur_bookie_title = CornerstatsRequirement::$config['parsing_rules']['bookies'][$cur_bookie_id]['title'];
        }
        if(!isset($cur_bookie) || strcmp($cur_bookie_title, $bookie_title) != 0){
            $current_bookie_id = null;
            foreach(CornerstatsRequirement::$config['parsing_rules']['bookies'] as $key => $bookie_req)
                if(strcmp($bookie_req['title'], $bookie_title) == 0) $current_bookie_id = $key;
            //$current_bookie_id = array_search($th_bookie, );
            if(isset($current_bookie_id)){
                return $current_bookie_id;
            }
            else{
               return null;
            }
        }
        else{

            return null;
        }
    }
    protected function processTableRow11and12($table_tr, $current_bookie_id, $totals_of_group_required,$bookie_first_row, $fapi_id, $match_id){
        $values = array();
        date_default_timezone_set('UTC');
        $date = date('Y-m-d H:i:s');
        //echo $table_tr;
        if(isset($totals_of_group_required['list']['1x3']) && $bookie_first_row){
            $td_1_coef = $table_tr->children(1);
            $td_1_coef = HTMLHelper::findOne('a', $td_1_coef);
            $td_1_coef = $td_1_coef->innertext;
            $td_x_coef = $table_tr->children(2);
            $td_x_coef = HTMLHelper::findOne('a', $td_x_coef);
            $td_x_coef = $td_x_coef->innertext;
            $td_2_coef = $table_tr->children(3);
            $td_2_coef = HTMLHelper::findOne('a', $td_2_coef);
            $td_2_coef = $td_2_coef->innertext;

            if(!empty($td_1_coef))
                $values[] = array(
                    'type' => '1x3',
                    'event' => $totals_of_group_required['list']['1x3']['event'],
                    'member' => BettingConfig::MEMBER1,
                    'value' => BettingConfig::WIN,
                    'coef' => $td_1_coef,
                    'bookie_id' => $current_bookie_id,
                    'date' => $date,
                    'external_match_id' => $fapi_id,
                    'match_id' => $match_id,
                );
            if(!empty($td_x_coef))
                $values[] = array(
                    'type' => '1x3',
                    'event' => $totals_of_group_required['list']['1x3']['event'],
                    'member' => BettingConfig::NO_MEMBER,
                    'value' => BettingConfig::DRAW,
                    'coef' => $td_x_coef,
                    'bookie_id' => $current_bookie_id,
                    'date' => $date,
                    'external_match_id' => $fapi_id,
                    'match_id' => $match_id,
                );
            if(!empty($td_2_coef))
                $values[] = array(
                    'type' => '1x3',
                    'event' => $totals_of_group_required['list']['1x3']['event'],
                    'member' => BettingConfig::MEMBER2,
                    'value' => BettingConfig::WIN,
                    'coef' => $td_2_coef,
                    'bookie_id' => $current_bookie_id,
                    'date' => $date,
                    'external_match_id' => $fapi_id,
                    'match_id' => $match_id,
                );
        }
        //print_r($values);
        if(isset($totals_of_group_required['list']['handicap'])){
            $td_handicap_member1_value = $table_tr->children(4);
            $td_handicap_member1_value = HTMLHelper::findOne('b', $td_handicap_member1_value);
            $td_handicap_member1_value = $td_handicap_member1_value->innertext;
            $td_handicap_member1_coef = $table_tr->children(5);
            $td_handicap_member1_coef = HTMLHelper::findOne('a', $td_handicap_member1_coef);
            $td_handicap_member1_coef = $td_handicap_member1_coef->innertext;

            if(!empty($td_handicap_member1_value) && !empty($td_handicap_member1_coef)){
                if($td_handicap_member1_value > 0) $sign =BettingConfig::GREATER_THAN;
                else if($td_handicap_member1_value < 0) $sign =BettingConfig::LESSER_THAN;
                else $sign = BettingConfig::EQUAL;

                if(fmod($td_handicap_member1_value, 0.5) == 0) $bet_type = 'handicap';
                else $bet_type= 'asian_handicap';

                $values[] = array(
                    'type' => $bet_type,
                    'event' => $totals_of_group_required['list']['handicap']['event'],
                    'member' => BettingConfig::MEMBER1,
                    'value' => abs($td_handicap_member1_value),
                    'sign' => $sign,
                    'coef' => $td_handicap_member1_coef,
                    'bookie_id' => $current_bookie_id,
                    'date' => $date,
                    'external_match_id' => $fapi_id,
                    'match_id' => $match_id,
                );
            }

            $td_handicap_member2_value = $table_tr->children(6);
            $td_handicap_member2_value = HTMLHelper::findOne('b', $td_handicap_member2_value);
            $td_handicap_member2_value = $td_handicap_member2_value->innertext;
            $td_handicap_member2_coef = $table_tr->children(7);
            $td_handicap_member2_coef = HTMLHelper::findOne('a', $td_handicap_member2_coef);
            $td_handicap_member2_coef = $td_handicap_member2_coef->innertext;

            if(!empty($td_handicap_member2_value) && !empty($td_handicap_member2_coef)){
                if($td_handicap_member2_value > 0) $sign =BettingConfig::GREATER_THAN;
                else if($td_handicap_member2_value < 0) $sign =BettingConfig::LESSER_THAN;
                else $sign = BettingConfig::EQUAL;

                if(fmod($td_handicap_member2_value, 0.5) == 0) $bet_type = 'handicap';
                else $bet_type= 'asian_handicap';

                $values[] = array(
                    'type' => $bet_type,
                    'event' => $totals_of_group_required['list']['handicap']['event'],
                    'member' => BettingConfig::MEMBER2,
                    'value' => abs($td_handicap_member2_value),
                    'sign' => $sign,
                    'coef' => $td_handicap_member2_coef,
                    'bookie_id' => $current_bookie_id,
                    'date' => $date,
                    'external_match_id' => $fapi_id,
                    'match_id' => $match_id,
                );
            }
        }

        if(isset($totals_of_group_required['list']['total'])){
            $td_total_value = $table_tr->children(8);
            $td_total_value = HTMLHelper::findOne('b', $td_total_value);
            $td_total_value = $td_total_value->innertext;
            $td_total_under_coef = $table_tr->children(9);
            $td_total_under_coef = HTMLHelper::findOne('a', $td_total_under_coef);
            $td_total_under_coef = $td_total_under_coef->innertext;
            $td_total_over_coef = $table_tr->children(10);
            $td_total_over_coef = HTMLHelper::findOne('a', $td_total_over_coef);
            $td_total_over_coef = $td_total_over_coef->innertext;

            if(!empty($td_total_value)){
                if(fmod($td_total_value, 0.5) == 0) $total_type = 'total';
                else $total_type= 'asian_total';
                if(!empty($td_total_under_coef)){
                    $values[] = array(
                        'type' => $total_type,
                        'event' => $totals_of_group_required['list']['total']['event'],
                        'member' => BettingConfig::NO_MEMBER,
                        'value' => $td_total_value,
                        'sign' => BettingConfig::LESSER_THAN,
                        'coef' => $td_total_under_coef,
                        'bookie_id' => $current_bookie_id,
                        'date' => $date,
                        'external_match_id' => $fapi_id,
                        'match_id' => $match_id,
                    );
                }
                if(!empty($td_total_over_coef)){
                    $values[] = array(
                        'type' => $total_type,
                        'event' => $totals_of_group_required['list']['total']['event'],
                        'member' => BettingConfig::NO_MEMBER,
                        'value' => $td_total_value,
                        'sign' => BettingConfig::GREATER_THAN,
                        'coef' => $td_total_over_coef,
                        'bookie_id' => $current_bookie_id,
                        'date' => $date,
                        'external_match_id' => $fapi_id,
                        'match_id' => $match_id,
                    );
                }
            }
        }
        return $values;
    }
    protected function processTableRow10($table_tr, $current_bookie_id, $totals_of_group_required, $fapi_id, $match_id){
        $values = array();
        date_default_timezone_set('UTC');
        $date = date('Y-m-d H:i:s');
        if(isset($totals_of_group_required['list']['handicap'])){
            $td_handicap_member1_value = $table_tr->children(3);
            $td_handicap_member1_value = HTMLHelper::findOne('b', $td_handicap_member1_value);
            $td_handicap_member1_value = $td_handicap_member1_value->innertext;
            $td_handicap_member1_coef = $table_tr->children(4);
            $td_handicap_member1_coef = HTMLHelper::findOne('a', $td_handicap_member1_coef);
            $td_handicap_member1_coef = $td_handicap_member1_coef->innertext;

            if(!empty($td_handicap_member1_value) && !empty($td_handicap_member1_coef)){
                if($td_handicap_member1_value > 0) $sign =BettingConfig::GREATER_THAN;
                else if($td_handicap_member1_value < 0) $sign =BettingConfig::LESSER_THAN;
                else $sign = BettingConfig::EQUAL;

                if(fmod($td_handicap_member1_value, 0.5) == 0) $bet_type = 'handicap';
                else $bet_type= 'asian_handicap';

                $values[] = array(
                    'type' => $bet_type,
                    'event' => $totals_of_group_required['list']['handicap']['event'],
                    'member' => BettingConfig::MEMBER1,
                    'value' => abs($td_handicap_member1_value),
                    'sign' => $sign,
                    'coef' => $td_handicap_member1_coef,
                    'bookie_id' => $current_bookie_id,
                    'date' => $date,
                    'external_match_id' => $fapi_id,
                    'match_id' => $match_id,
                );
            }

            $td_handicap_member2_value = $table_tr->children(5);
            $td_handicap_member2_value = HTMLHelper::findOne('b', $td_handicap_member2_value);
            $td_handicap_member2_value = $td_handicap_member2_value->innertext;
            $td_handicap_member2_coef = $table_tr->children(6);
            $td_handicap_member2_coef = HTMLHelper::findOne('a', $td_handicap_member2_coef);
            $td_handicap_member2_coef = $td_handicap_member2_coef->innertext;

            if(!empty($td_handicap_member2_value) && !empty($td_handicap_member2_coef)){
                if($td_handicap_member2_value > 0) $sign =BettingConfig::GREATER_THAN;
                else if($td_handicap_member2_value < 0) $sign =BettingConfig::LESSER_THAN;
                else $sign = BettingConfig::EQUAL;

                if(fmod($td_handicap_member2_value, 0.5) == 0) $bet_type = 'handicap';
                else $bet_type= 'asian_handicap';

                $values[] = array(
                    'type' => $bet_type,
                    'event' => $totals_of_group_required['list']['handicap']['event'],
                    'member' => BettingConfig::MEMBER2,
                    'value' => abs($td_handicap_member2_value),
                    'sign' => $sign,
                    'coef' => $td_handicap_member2_coef,
                    'bookie_id' => $current_bookie_id,
                    'date' => $date,
                    'external_match_id' => $fapi_id,
                    'match_id' => $match_id,
                );
            }
        }

        if(isset($totals_of_group_required['list']['total'])){
            $td_total_value = $table_tr->children(7);
            $td_total_value = HTMLHelper::findOne('b', $td_total_value);
            $td_total_value = $td_total_value->innertext;
            $td_total_under_coef = $table_tr->children(8);
            $td_total_under_coef = HTMLHelper::findOne('a', $td_total_under_coef);
            $td_total_under_coef = $td_total_under_coef->innertext;
            $td_total_over_coef = $table_tr->children(9);
            $td_total_over_coef = HTMLHelper::findOne('a', $td_total_over_coef);
            $td_total_over_coef = $td_total_over_coef->innertext;

            if(!empty($td_total_value)){
                if(fmod($td_total_value, 0.5) == 0) $total_type = 'total';
                else $total_type= 'asian_total';
                if(!empty($td_total_under_coef)){
                    $values[] = array(
                        'type' => $total_type,
                        'event' => $totals_of_group_required['list']['total']['event'],
                        'member' => BettingConfig::NO_MEMBER,
                        'value' => $td_total_value,
                        'sign' => BettingConfig::LESSER_THAN,
                        'coef' => $td_total_under_coef,
                        'bookie_id' => $current_bookie_id,
                        'date' => $date,
                        'external_match_id' => $fapi_id,
                        'match_id' => $match_id,
                    );
                }
                if(!empty($td_total_over_coef)){
                    $values[] = array(
                        'type' => $total_type,
                        'event' => $totals_of_group_required['list']['total']['event'],
                        'member' => BettingConfig::NO_MEMBER,
                        'value' => $td_total_value,
                        'sign' => BettingConfig::GREATER_THAN,
                        'coef' => $td_total_over_coef,
                        'bookie_id' => $current_bookie_id,
                        'date' => $date,
                        'external_match_id' => $fapi_id,
                        'match_id' => $match_id,
                    );
                }
            }
        }
        return $values;
    }
    protected function checkTableStructure($th_row){
        if(count($th_row->children) == 9 || count($th_row->children) == 10){
            //make sure that columns match with db info
            $th_bookie = $th_row->children(0); $th_bookie = $th_bookie->innertext;
            $th_1 = $th_row->children(1); $th_1 = $th_1->innertext;
            $th_x = $th_row->children(2); $th_x = $th_x->innertext;
            $th_2 = $th_row->children(3); $th_2 = $th_2->innertext;
            //better not to check
            /*
            $th_member1 = $th_row->children(4);
            $th_member2 = $th_row->children(5);
            */
            $th_total = $th_row->children(6); $th_total = $th_total->innertext;
            $th_under = $th_row->children(7); $th_under = $th_under->innertext;
            $th_over = $th_row->children(8); $th_over = $th_over->innertext;
            if(strcasecmp($th_bookie, 'Bookmaker') != 0) return false;
            if(strcasecmp($th_1, '1') != 0) return false;
            if(strcasecmp($th_x, 'X') != 0) return false;
            if(strcasecmp($th_2, '2') != 0) return false;
            if(strcasecmp($th_total, 'Total') != 0) return false;
            if(strcasecmp($th_under, 'Under') != 0) return false;
            if(strcasecmp($th_over, 'Over') != 0) return false;

            return true;
        }
        return false;
    }
}