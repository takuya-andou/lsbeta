<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 13.04.2016
 * Time: 20:57
 */

namespace app\extensions\parsing\marathonbet;


use app\extensions\parsing\DBWriter;
use app\extensions\parsing\helpers\HTMLHelper;
use app\extensions\parsing\HTMLParser;
use app\modules\betting\config\BettingConfig;

class MarathonbetFootballParser extends HTMLParser
{
    protected $log_file = 'parser_cornerstats.log';

    public function __construct($params = array()) {
        parent::__construct(array_merge($params, MarathonbetRequirement::$config['connection_rules'] ));
    }

    protected function getCategoryTitle($categoryHTML){
        $category_title_html = HTMLHelper::findAll('h2[class=category-header] span[class=category-label] span', $categoryHTML);
        if($category_title_html) {
            $category_title = "";
            foreach ($category_title_html as $title_part) {
                $category_title .= $title_part->innertext;
            }
            return $category_title;
        }
    }
    public function getMainPage(){
        $page = $this->getConnector()->curl($this->getUrl(), null);
        $this->incrementRequests();
        //print_r($page);
        return $page;
    }
    public function getCategories($pageHTML){
        $category_container = HTMLHelper::findOne('div[class=sport-category-content]', $pageHTML);
        if($category_container) {
            $leagues = array();
            $leagues_required = MarathonbetRequirement::$config['parsing_rules']['football']['categories']['list'];
            $unparsed_leagues = array();
            foreach ($leagues_required as $key=> $league) {
                $unparsed_leagues[$key] = $league;
            }
            foreach ($category_container->children() as $category) {
                $cat = null;
                $category_title = $this->getCategoryTitle($category);
                foreach ($leagues_required as $league_id => $list_league) {
                    
                    if (preg_match($list_league, $category_title)) {

                        $events_container = HTMLHelper::findOne('div[class=category-content] div[class=foot-market-border] table[class=foot-market]', $category);

                        if ($events_container) {
                            $events_container = $events_container->outertext;
                            $leagues[] = array('category_id' => $league_id, 'events_container' => $events_container);
                            unset($unparsed_leagues[$league_id]);
                        }
                        break;
                    }
                }
            }
            if(!empty($unparsed_leagues))
            foreach ($unparsed_leagues as $key => $unparsed_league) {
                self::logPush('MarathonbetFootballParser. League ' . $key. ' was not parsed.', self::$log_filename, self::ERROR);
            }
            if(!empty($leagues))
            return $leagues;
        }
        return null;
    }
    public function getEvents(&$categories){
        $data = array();
        if(!empty($categories))
        foreach($categories as $key => &$category) {
            if (isset($category['events_container'])) {
                $events_container = $category['events_container'];
                //$category['events'] = array();
                $category_events = HTMLHelper::findAll('tr[class=event-header]', $events_container);
                if ($category_events) {
                    foreach ($category_events as $category_event) {
                        $event = null;
                        $tr_members = $category_event->children(0);
                        $member_cells = HTMLHelper::findAll('div[class=member-name], div[class=today-member-name]',$tr_members);
                        $date_cell = HTMLHelper::findOne('td[class=date]', $tr_members);
                        if ($member_cells && count($member_cells) == 2) {
                            $event = array();
                            $event['member1'] = $member_cells[0]->innertext;
                            $event['member2'] = $member_cells[1]->innertext;
                            date_default_timezone_set('UTC');
                            $event['date'] = date('Y-m-d H:i:s');
                            $tr_treeId = $category_event->children(1);
                            $event['external_match_id'] = helpers\Helper::getTreeId($tr_treeId->outertext);
                            if($date_cell) $event['match_datetime'] = helpers\Helper::convertDate(helpers\Helper::trimText($date_cell->innertext));
                            else{
                                self::logPush('MarathonbetFootballParser. Date cell for event of ' . $category['title'] . ' was empty.', self::$log_filename, self::ERROR);
                                $event = null;
                            }
                        }
                        if ($event) {
                            $data[] = array_merge(array('category_id' => $category['category_id']), $event);
                        }
                        else {
                            self::logPush('MarathonbetFootballParser. Event for ' . $category['title'] . ' was not parsed.',self::$log_filename, self::ERROR);
                        }
                    }
                }
                $categories[$key]['events_container'] = null;
            } else {
                self::logPush('MarathonbetFootballParser. Events for ' . $category['title'] . ' are empty.', self::$log_filename, self::ERROR);
            }
        }
        if(!empty($data))
            return $data;
        else
            return null;
    }
    public function getCategoryEvents(&$category){
        $data = array();
        if(!empty($category))
                if (isset($category['events_container'])) {
                    $events_container = str_get_html($category['events_container']);
                    //$category['events'] = array();
                    $category_events = HTMLHelper::findAll('tr[class=event-header]', $events_container);
                    if ($category_events) {
                        foreach ($category_events as $category_event) {
                            $event = null;
                            $tr_members = $category_event->children(0);
                            $member_cells = HTMLHelper::findAll('div[class=member-name], div[class=today-member-name]',$tr_members);
                            $date_cell = HTMLHelper::findOne('td[class=date]', $tr_members);
                            if ($member_cells && count($member_cells) == 2) {
                                $event = array();
                                $event['member1'] = $member_cells[0]->innertext;
                                $event['member2'] = $member_cells[1]->innertext;
                                date_default_timezone_set('UTC');
                                $event['date'] = date('Y-m-d H:i:s');
                                $tr_treeId = $category_event->children(1);
                                $event['external_match_id'] = helpers\Helper::getTreeId($tr_treeId->outertext);
                                if($date_cell) $event['match_datetime'] = helpers\Helper::convertDate(helpers\Helper::trimText($date_cell->innertext));
                                else{
                                    self::logPush('MarathonbetFootballParser. Date cell for event of ' . $category['title'] . ' was empty.', self::$log_filename, self::ERROR);
                                    $event = null;
                                }
                            }
                            if ($event) {
                                $data[] = array_merge(array('category_id' => $category['category_id']), $event);
                            }
                            else {
                                self::logPush('MarathonbetFootballParser. Event for ' . $category['title'] . ' was not parsed.',self::$log_filename, self::ERROR);
                            }
                        }
                    }
                    $category['events_container'] = null;
                    $events_container->clear();
                    $events_container = null;
                } else {
                    self::logPush('MarathonbetFootballParser. Events for ' . $category['title'] . ' are empty.', self::$log_filename, self::ERROR);
                }
        if(!empty($data))
            return $data;
        else
            return null;
    }
    public function getEventTotals(&$event){
        $data = array();
        if(isset($event) && isset($event['external_match_id'])){
            $page = $this->connector->curl('markets.htm', [
                'treeId'     => $event['external_match_id'],
                'columnSize' => 8
            ]);
            $this->incrementRequests();
            $market = $page['content'];
            if($market) {
                $market = json_decode($market, true);

                $market_html = str_get_html($market['ADDITIONAL_MARKETS']);
                if (!empty($market_html)) {

                    $totals_group_required = MarathonbetRequirement::$config['parsing_rules']['football']['totals']['list'];
                    //все тоталы делятся на группы фолы, голы, углы
                    foreach ($totals_group_required as $total_group_required) {
                        //каждая группа делится на сами тоталы тотал голов 1 тайм, общий, карточки за игру 1 тима и тд
                        foreach ($total_group_required['list'] as $total_required) {
                            $totals_of_group_vals = $this->handlerWrapper($event, $market_html, $total_group_required['title'], $total_required);
                            if(!empty($totals_of_group_vals))
                                foreach($totals_of_group_vals as $totals_of_group_val){
                                    $data[] = array_merge($event, array_merge(array('bookie_id' => MarathonbetRequirement::$config['common_rules']['bookie_id'], 'type_id' => $total_required['type'], 'event_id' => $total_required['event']), $totals_of_group_val));
                                }
                            //$totals_of_group[] = array('title' => $total_required['title'], 'type' => $total_required['type'], 'event' => $total_required['event'], 'values' => $totals_of_group_vals);
                        }
                    }
                }
                else {
                    self::logPush('MarathonbetFootballParser. Simple html cannot convert market.htm page.', self::$log_filename, self::ERROR);
                }
            }
        }
                // return $categories;// TODO: удалить это дело для парса всех матчей а не 1


        if(!empty($data))
            return $data;
        else
            return null;
    }
    public function getTotals($events){
        $data = array();
        if(!empty($events))
        foreach ($events as &$event) {
            if(isset($event) && isset($event['external_match_id'])){
               $page = $this->connector->curl('markets.htm', [
                   'treeId'     => $event['external_match_id'],
                   'columnSize' => 8
               ]);
                $this->incrementRequests();
                $market = $page['content'];
                if($market) {
                    $market = json_decode($market, true);

                    $market_html = str_get_html($market['ADDITIONAL_MARKETS']);
                    if (!empty($market_html)) {

                        $totals_group_required = MarathonbetRequirement::$config['parsing_rules']['football']['totals']['list'];
                        //все тоталы делятся на группы фолы, голы, углы
                        foreach ($totals_group_required as $total_group_required) {
                            $totals_of_group=array();
                            //каждая группа делится на сами тоталы тотал голов 1 тайм, общий, карточки за игру 1 тима и тд
                            foreach ($total_group_required['list'] as $total_required) {
                                $handler = $total_required['handler'];
                                $totals_of_group_vals = $this->handlerWrapper($event, $market_html, $total_group_required['title'], $total_required);
                                if(!empty($totals_of_group_vals))
                                foreach($totals_of_group_vals as $totals_of_group_val){
                                    $data[] = array_merge($event, array_merge(array('bookie_id' => MarathonbetRequirement::$config['common_rules']['bookie_id'], 'type_id' => $total_required['type'], 'event_id' => $total_required['event']), $totals_of_group_val));
                                }
                                //$totals_of_group[] = array('title' => $total_required['title'], 'type' => $total_required['type'], 'event' => $total_required['event'], 'values' => $totals_of_group_vals);
                            }
                        }
                    }
                    else {
                        self::logPush('MarathonbetFootballParser. Simple html cannot convert market.htm page.', self::$log_filename, self::ERROR);
                    }
                }
                $this->cur_requests_num++;
                if($this->cur_requests_num >= self::REQUEST_NUM_TO_PAUSE){
                    self::logPush('MarathonbetFootballParser. Pausing requests...', self::$log_filename, self::INFO);
                    $this->pauseRequests();
                }
            }
            else{
            }
           // return $categories;// TODO: удалить это дело для парса всех матчей а не 1
        }

        if(!empty($data))
            return $data;
        else
            return null;
    }
    public function getUrl(){
        return MarathonbetRequirement::$config['parsing_rules']['football']['url'];
    }

    protected function saveBet($data)
    {

    }

    //Handlers------------------------------------------
    public function handlerWrapper($event, $market_html, $total_group_title, $total){
        $blocks = HTMLHelper::findAll('div[class=block-market-wrapper]',$market_html);
        if ($blocks) {
            foreach ($blocks as $block) {
                $block_title = HTMLHelper::findOne('div[class=market-block-name-menu]', $block);
                //echo $block_title;
                if($block_title){
                    $block_title = helpers\Helper::trimText($block_title->innertext);
                    $handler = $total['handler'];
                    if(strcmp($block_title, $total_group_title)==0){//найдена нужная подтаблица
                        return $this->$handler(
                         [
                             'block' =>$block,
                             'event' => $event,
                             'total_title' => $total['title'],
                         ]
                        );
                    }
                }
            }
            //self::logPush('MarathonbetFootballParser. No equal block titles for '.$total_group_title.' on market.htm were found.', self::$log_filename, self::WARNING);
            return null;
        }
        else{
            self::logPush('MarathonbetFootballParser. No blocks on market.htm were found.', self::$log_filename, self::WARNING);
            return null;
        }
    }
    protected function resultHandler($data){
        $block_html = $data['block'];
        $total_title = $data['total_title'];
        $member1 = $data['event']['member1'];
        $member2 = $data['event']['member2'];
        /*Не прописаны регулярки потому что некоторые команды могут иметь названия через слеш,
        который будет в дальнейшем экранирован вмете с самим РВ*/
        $patterns = array(
            '1x1' => '/^'.preg_quote($member1, '/\'').' To Win$/',
            '2x1' => '/^'.preg_quote($member2, '/\'').' To Win$/',
            '0' => '/^'.'Draw$/',
            '1x2' => '/^'.preg_quote($member1, '/\'').' To Win or Draw$/',
            '2x2' => '/^'.preg_quote($member2, '/\'').' To Win or Draw$/',
            '3' => '/^'.preg_quote($member1, '/\'').' To Win or '.preg_quote($member2, '/').' To Win$/');
        $market_table_wrappers = HTMLHelper::findAll('div[class=market-inline-block-table-wrapper]', $block_html);
        if($market_table_wrappers){
            //echo count($market_table_wrappers);
            foreach($market_table_wrappers as $market_table_wrapper){
                //внутри подтаблицы куча всяких названий групп кэфов. среди них отобрать нужный.
                $table_title = HTMLHelper::findOne('div[class=name-field]', $market_table_wrapper);
                if($table_title){
                    $table_title = helpers\Helper::trimText($table_title->innertext);
                    //echo $table_title.' '.$total_title.'<br>';
                    if(strcmp($table_title, $total_title)==0){//найдено нужное название тотала

                        $coef_table = HTMLHelper::findOne('table[class=td-border table-layout-fixed]', $market_table_wrapper);
                        if($coef_table){
                            $tds = HTMLHelper::findAll('td[class=price]', $coef_table);
                            foreach($tds as $td){//в полученной таблице проходимся по строчкам и извлекаем инфу
                                $data_sel = $td->getAttribute('data-sel');
                                $params = json_decode($data_sel, true);
                                if(isset($params['sn']) && isset($params['prices'][1])){
                                    if(($key = helpers\Helper::ifTextFits($params['sn'], $patterns, true))!=-1){
                                        //$val = $match[0];
                                        $member=null;
                                        $val = null;
                                        switch($key){
                                            case '1x1':$member=BettingConfig::MEMBER1; $val=BettingConfig::WIN; break;
                                            case '2x1':$member=BettingConfig::MEMBER2; $val=BettingConfig::WIN; break;
                                            case '0': $val=BettingConfig::DRAW; break;
                                            case '1x2':$member=BettingConfig::MEMBER1; $val=BettingConfig::WIN_OR_DRAW; break;
                                            case '2x2':$member=BettingConfig::MEMBER2; $val=BettingConfig::WIN_OR_DRAW; break;
                                            case '3': $val=BettingConfig::NO_DRAW;break;
                                        }
                                        $values[] = array('member' => $member,'value' => $val, 'coef' => $params['prices'][1], 'bookie_id' => MarathonbetRequirement::$config['common_rules']['bookie_id']);
                                    }
                                    else{
                                        self::logPush('MarathonbetFootballParser->resultHandler. No suitable pattern for '.$params['sn'].' found.', self::$log_filename, self::WARNING);
                                    }
                                }
                            }
                            //таблица пройдена, можно выходить
                            if(!empty($values)) return $values;
                            return null;
                        }
                    }
                }

            }
        }
        return null;
    }
    protected function totalUnderOverHandler($data){
        $block_html = $data['block'];
        $total_title = $data['total_title'];
        $member = $data['event']['member1'];

        $total_title = preg_replace('/\*member1\*/', $member, $total_title);

        $lesser_word = 'Under';
        $greater_word = 'Over';

        $patterns = array(
            '/'.$lesser_word.' \d+[\.,]?\d*/',
            '/'.$greater_word.' \d+[\.,]?\d*/'
        );
        $market_table_wrappers = HTMLHelper::findAll('div[class=market-inline-block-table-wrapper]', $block_html);
        if($market_table_wrappers){
            //echo count($market_table_wrappers);
            foreach($market_table_wrappers as $market_table_wrapper){
                //внутри подтаблицы куча всяких названий групп кэфов. среди них отобрать нужный.
                $table_title = HTMLHelper::findOne('div[class=name-field]', $market_table_wrapper);
                if($table_title){
                    $table_title = helpers\Helper::trimText($table_title->innertext);
                    //echo $table_title.' '.$total_title.'<br>';
                    if(strcmp($table_title, $total_title)==0){//найдено нужное название тотала

                        $coef_table = HTMLHelper::findOne('table[class=td-border table-layout-fixed]', $market_table_wrapper);
                        if($coef_table){
                            $tds = HTMLHelper::findAll('td[class=price]', $coef_table);
                            foreach($tds as $td){//в полученной таблице проходимся по строчкам и извлекаем инфу
                                $data_sel = $td->getAttribute('data-sel');
                                $params = json_decode($data_sel, true);
                                if(isset($params['sn']) && isset($params['prices'][1])){
                                    if(helpers\Helper::ifTextFits($params['sn'], $patterns)!=-1){
                                        if(preg_match('/'.$lesser_word.'/', $params['sn'])){$sign = BettingConfig::LESSER_THAN;}
                                        else if(preg_match('/'.$greater_word.'/', $params['sn'])){$sign = BettingConfig::GREATER_THAN;}
                                        preg_match('/\d+[\.,]?\d*/', $params['sn'], $match);
                                        $val =$match[0];
                                        //$val = $match[0];
                                        $values[] = array('sign' => $sign, 'value' => $val, 'coef' => $params['prices'][1], 'bookie_id' => MarathonbetRequirement::$config['common_rules']['bookie_id']);
                                    }
                                    else{
                                        self::logPush('MarathonbetFootballParser->totalUnderOverHandler. No suitable pattern for '.$params['sn'].' found.', self::$log_filename, self::WARNING);
                                    }
                                }
                            }
                            //таблица пройдена, можно выходить
                            if(!empty($values)) return $values;
                            return null;
                        }
                    }
                }

            }
        }
        return null;
    }
    protected function individualTotalM1Handler($data){
        $block_html = $data['block'];
        $total_title = $data['total_title'];
        $member = $data['event']['member1'];

        $total_title = preg_replace('/member1/', $member, $total_title);

        $lesser_word = 'Under';
        $greater_word = 'Over';

        $patterns = array('/'.$lesser_word.' \d+[\.,]?\d*/', '/'.$greater_word.' \d+[\.,]?\d*/');
        $market_table_wrappers = HTMLHelper::findAll('div[class=market-inline-block-table-wrapper]', $block_html);
        if($market_table_wrappers){
            //echo count($market_table_wrappers);
            foreach($market_table_wrappers as $market_table_wrapper){
                //внутри подтаблицы куча всяких названий групп кэфов. среди них отобрать нужный.
                $table_title = HTMLHelper::findOne('div[class=name-field]', $market_table_wrapper);
                if($table_title){
                    $table_title = helpers\Helper::trimText($table_title->innertext);
                    //echo $table_title.' '.$total_title.'<br>';
                    if(strcmp($table_title, $total_title)==0){//найдено нужное название тотала

                        $coef_table = HTMLHelper::findOne('table[class=td-border table-layout-fixed]', $market_table_wrapper);
                        if($coef_table){
                            $tds = HTMLHelper::findAll('td[class=price]', $coef_table);
                            foreach($tds as $td){//в полученной таблице проходимся по строчкам и извлекаем инфу
                                $data_sel = $td->getAttribute('data-sel');
                                $params = json_decode($data_sel, true);
                                if(isset($params['sn']) && isset($params['prices'][1]) && $params['mn']==$total_title){
                                    if(helpers\Helper::ifTextFits($params['sn'], $patterns)!=-1){
                                        if(preg_match('/'.$lesser_word.'/', $params['sn'])){$sign = BettingConfig::LESSER_THAN;}
                                        else if(preg_match('/'.$greater_word.'/', $params['sn'])){$sign = BettingConfig::GREATER_THAN;}
                                        preg_match('/\d+[\.,]?\d*/', $params['sn'], $match);
                                        $val =$match[0];
                                        //$val = $match[0];
                                        $values[] = array('member' => BettingConfig::MEMBER1, 'sign' => $sign, 'value' => $val, 'coef' => $params['prices'][1], 'bookie_id' => MarathonbetRequirement::$config['common_rules']['bookie_id']);
                                    }
                                    else{
                                        self::logPush('MarathonbetFootballParser->individualTotalM1Handler. No suitable pattern for '.$params['sn'].' found.', self::$log_filename, self::WARNING);
                                    }
                                }
                            }
                            //таблица пройдена, можно выходить
                            if(!empty($values)) return $values;
                            return null;
                        }
                    }
                }

            }
        }
        return null;
    }
    protected function individualTotalM2Handler($data){
        $block_html = $data['block'];
        $total_title = $data['total_title'];
        $member = $data['event']['member2'];

        $total_title = preg_replace('/member2/', $member, $total_title);

        $lesser_word = 'Under';
        $greater_word = 'Over';

        $patterns = array('/'.$lesser_word.' \d+[\.,]?\d*/', '/'.$greater_word.' \d+[\.,]?\d*/');
        $market_table_wrappers = HTMLHelper::findAll('div[class=market-inline-block-table-wrapper]', $block_html);
        if($market_table_wrappers){
            //echo count($market_table_wrappers);
            foreach($market_table_wrappers as $market_table_wrapper){
                //внутри подтаблицы куча всяких названий групп кэфов. среди них отобрать нужный.
                $table_title = HTMLHelper::findOne('div[class=name-field]', $market_table_wrapper);
                if($table_title){
                    $table_title = helpers\Helper::trimText($table_title->innertext);
                    //echo $table_title.' '.$total_title.'<br>';
                    if(strcmp($table_title, $total_title)==0){//найдено нужное название тотала

                        $coef_table = HTMLHelper::findOne('table[class=td-border table-layout-fixed]', $market_table_wrapper);
                        if($coef_table){
                            $tds = HTMLHelper::findAll('td[class=price]', $coef_table);
                            foreach($tds as $td){//в полученной таблице проходимся по строчкам и извлекаем инфу
                                $data_sel = $td->getAttribute('data-sel');
                                $params = json_decode($data_sel, true);
                                if(isset($params['sn']) && isset($params['prices'][1]) && $params['mn']==$total_title){
                                    if(helpers\Helper::ifTextFits($params['sn'], $patterns)!=-1){
                                        if(preg_match('/'.$lesser_word.'/', $params['sn'])){$sign = BettingConfig::LESSER_THAN;}
                                        else if(preg_match('/'.$greater_word.'/', $params['sn'])){$sign = BettingConfig::GREATER_THAN;}
                                        preg_match('/\d+[\.,]?\d*/', $params['sn'], $match);
                                        $val =$match[0];
                                        //$val = $match[0];
                                        $values[] = array('member' => BettingConfig::MEMBER2, 'sign' => $sign, 'value' => $val, 'coef' => $params['prices'][1], 'bookie_id' => MarathonbetRequirement::$config['common_rules']['bookie_id']);
                                    }
                                    else{
                                        self::logPush('MarathonbetFootballParser->individualTotalM2Handler. No suitable pattern for '.$params['sn'].' found.', self::$log_filename, self::WARNING);
                                    }
                                }
                            }
                            //таблица пройдена, можно выходить
                            if(!empty($values)) return $values;
                            return null;
                        }
                    }
                }

            }
        }
        return null;
    }
    protected function handicapHandler($data){
        $block_html = $data['block'];
        $total_title = $data['total_title'];

        $patterns = array(
            BettingConfig::MEMBER1 => '/'.preg_quote($data['event']['member1'], '/').'\s*(\([-,+]?\d+[\.,]?\d*\)|$)/',
            BettingConfig::MEMBER2 => '/'.preg_quote($data['event']['member2'], '/').'\s*(\([-,+]?\d+[\.,]?\d*\)|$)/'
        );
        $market_table_wrappers = HTMLHelper::findAll('div[class=market-inline-block-table-wrapper]', $block_html);
        if($market_table_wrappers){
            foreach($market_table_wrappers as $market_table_wrapper){
                //внутри подтаблицы куча всяких названий групп кэфов. среди них отобрать нужный.
                $table_title = HTMLHelper::findOne('div[class=name-field]', $market_table_wrapper);
                if($table_title){
                    $table_title = helpers\Helper::trimText($table_title->innertext);
                    if(strcmp($table_title, $total_title)==0){//найдено нужное название тотала
                        $coef_table = HTMLHelper::findOne('table[class=td-border table-layout-fixed]', $market_table_wrapper);
                        if($coef_table){
                            $tds = HTMLHelper::findAll('td[class=price]', $coef_table);
                            foreach($tds as $td){//в полученной таблице проходимся по строчкам и извлекаем инфу
                                $data_sel = $td->getAttribute('data-sel');
                                $params = json_decode($data_sel, true);
                                if(isset($params['sn']) && isset($params['prices'][1])){
                                    if(($key = helpers\Helper::ifTextFits($params['sn'], $patterns, true))!=-1){
                                        if(preg_match('/\([-,+]?\d+[\.,]?\d*\)/', $params['sn'], $match)){
                                            $val =trim($match[0], ')');
                                            $val = trim($val, '(');
                                            if($val < 0) $sign = BettingConfig::LESSER_THAN;
                                            else if($val == 0) $sign = BettingConfig::EQUAL;
                                            else $sign = BettingConfig::GREATER_THAN;
                                        }
                                        else{
                                            //self::logPush($params['sn'].'1', self::$log_filename, self::ERROR);
                                            $val=0;
                                            $sign=0;
                                        }
                                        $values[] = array('member' => $key, 'sign' => $sign,'value' => abs(floatval($val)), 'coef' => $params['prices'][1], 'bookie_id' => MarathonbetRequirement::$config['common_rules']['bookie_id']);
                                    }
                                    else{
                                        self::logPush('MarathonbetFootballParser->handicapHandler. No suitable pattern for '.$params['sn'].' found.', self::$log_filename, self::WARNING);
                                    }
                                }
                            }
                            //таблица пройдена, можно выходить
                            if(!empty($values)) return $values;
                            return null;
                        }
                    }
                }

            }
        }
        return null;
    }
}