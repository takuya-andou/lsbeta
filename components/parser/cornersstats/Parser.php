<?php
/**
 * Parser from corners-stats.com
 *
 * @author Morgunov Alexander <fxl@list.ru>
 * @link https://github.com/saaaaaaaaasha
 */

namespace app\components\parser\cornersstats;

use Yii;
use yii\base\Component;
use app\modules\soccer\models\Country;
use app\modules\soccer\models\Match;
use app\modules\soccer\models\MatchEvent;
use app\modules\soccer\models\MatchStats;
use app\components\Logger;
use app\components\ExtendedComponent;

date_default_timezone_set('Europe/London');

require_once \Yii::getAlias('@components') . '/simple_html_dom/simple_html_dom.php';

class Parser extends ExtendedComponent
{
    /**
     * @var array $json
     */
    private $json = [];

    /**
     * @var array $map
     */
    private $map = [
        'home'   => 1,
        'away'   => 2,
        'goal'   => 1,
        'pen'    => 2,
        'yellow' => 3,
        'red'    => 4,
        'sub'    => 5
    ];
    
    /**
     * @param $matchId - external match id
     * @return string - url for parsing
     */
    public function getUrl($matchId) {
        return $this->config['url'] . '/atletico-madrid-d-coruna-30-11-2014/primera-division-spain/match/'. $matchId;
    }

    public function __construct() {
        //todo проверка на существование конфиг файла
        $this->config = array_merge(
            $this->config,
            require(__DIR__. "/config.php")
        );
    }
    
    /**
     * @param int $id matchId
     * @param string $message message to write
     * @return bool
     */
    public function log($id = 0, $message = '', $file = false){
        if (!$this->debug) {
            return false;
        }

        $id = (!$id) ? '' : '{external_id:'.$id.'} ';
        $this->getLogger()->push($id . $message, $file);
        return true;
    }

    /**
     * @param $externalMatchId
     * @param int $sleep Time in seconds between requests
     * @return $this
     */
    public function start($externalMatchId, $sleep = 1){
        $this->clear(true);
        $this->parseMatch($externalMatchId);
        $this->prepareFields();
        $this->addToServer();
        sleep($sleep);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getKey() {
        return $this->config['cacheKey'];
    }

    /**
     * Reset all parameters
     * @param bool $debug
     * @return $this
     */
    public function clear($debug = true) {
        $this->error = false;
        $this->debug = ($debug) ? true : false;
        $this->json = [];
        return $this;
    }

    /**
     * Parse data for not started matches played 1 weeks ago
     * @return array
     */
    public function updateLastedMatch(){
        $start = date('Y-m-d H:i:00', strtotime('-7 days'));
        $end = date('Y-m-d H:i:00', strtotime('now'));

        $matches = Match::find()
            ->where(['=', 'status', Match::STATUS_NOTSTARTED])
            ->andWhere(['>=', 'date', $start])
            ->andWhere(['<=', 'date', $end])
            ->all();
        echo "Matches: " . count($matches). "\r\n";
        $start = 0;
        $end = 0;

        foreach($matches as $item) {
            if ($start == 0) $start = $item['fapi_id'];
            $end = $item['fapi_id'];
            $this->start($item['fapi_id']);
        }


        return [
            'count' => count($matches),
            'start'   => $start,
            'end'     => $end,
        ];
    }

    /**
     * parsing matches of corner-stats.com
     * @param int $id
     * @param bool $logger
     * @return string
     */
    public function parseMatch($id = 1) {
        $time_start = microtime(true);
        //$this->log($id, 'START');
        $this->error = true;

        $url = $this->getUrl($id);
        $json = array();
        $result = file_get_html($url);
        $title = $result->find('h1');
        $h1 =  $title[0]->plaintext;
        $imgs = $result->find('h1 img');
        # h1[0] - date? format dd/mm/yy
        # h1[1] - league (country) *
        # h1[2] - team1 - team2
        $h1Pieces = explode(".", $h1);
        foreach($h1Pieces as &$item) {
            $item = str_replace("&nbsp;", '', $item);
            //trim($item, chr(0xC2).chr(0xA0));
        }

        $json['Id'] = $id;
        $json['Time'] = trim($h1Pieces[0]);

        #hard fix: (if league include dot.

        if ((int)$h1Pieces[1]>=1 && (int)$h1Pieces[1]<=3) {
            //$this->log($id, 'LEAGUE\'s NAME WITH DOT');

            $h1Pieces[1].='.'.$h1Pieces[2];
            $i=3;
            while(isset($h1Pieces[$i])){
                $h1Pieces[$i-1] = $h1Pieces[$i];
                $i++;
            }
            unset($h1Pieces[$i-1]);
        }

        $json['League'] = trim(substr($h1Pieces[1], 0, strpos($h1Pieces[1], " (")));
        $json['Country'] = substr(substr(trim(strstr($h1Pieces[1], '(')), 0, -1), 1);

        #hard fix: if name include dot.
        $i=3;
        while (isset($h1Pieces[$i])) {
            //$this->log($id, 'TEAM\'s NAME WITH DOT');
            $h1Pieces[2].=$h1Pieces[$i];
            $i++;
        }
        $teamsPieces = explode("-", $h1Pieces[2]);
        foreach($teamsPieces as &$item) {
            $item = trim(str_replace("&nbsp;", '', $item));
        }
        $json['TeamHome'] = $teamsPieces[0];
        $json['TeamAway'] = $teamsPieces[1];

        if (!$json['TeamHome'] || !$json['TeamAway'] || !$json['League'] || !$json['Country']) {
            $this->error = 'empty page';
            $this->log($id, 'ERROR - EMPTY PAGE'); //♥
            return '';
        }

        $temp = explode('/',$imgs[1]->src);
        $temp = $temp[count($temp)-1];
        $json['TeamHomeCountry'] = substr($temp, 0, strlen($temp)-4);
        if (isset($imgs[2]) && $imgs[2]) {
            $temp = explode('/',$imgs[2]->src);
            $temp = $temp[count($temp)-1];
            $json['TeamAwayCountry'] = substr($temp, 0, strlen($temp)-4);
        } else {
            $json['TeamAwayCountry'] = $json['TeamHomeCountry'];
        }

        if($result->innertext!='' && count($result->find('#information'))){
            $block = $result->find('#information');
            $res2 = $block[0]->innertext;
            $result->clear();
            unset($result);
        }
        $result = str_get_html($res2);

        # quick information
        $information = $result->find('table tr td table tr');
        for ($i = 0; $i < 6; $i++) {
            $tds = $information[$i]->innertext;
            $tdsParse = str_get_html($tds);
            $resll = $tdsParse->find('td');
            if ($resll[0]->plaintext == 'Referee:') {
                $rid = str_get_html($resll[2]->innertext);

                $riid = $rid->find('a');
                if ($riid[0] && $riid[0]->innertext!='' && count($riid)>0) {
                    foreach ($riid as $item) {
                        $href = $item->href;
                        $href = str_replace('&amp;','&',$href);
                        $hrefPieces = explode("/", $href);
                        if (!isset($hrefPieces[4])) {
                            //$this->log($id, 'REFEREE WITHOUT ID');
                            continue;
                        }
                        $json['RefereeId'] = $hrefPieces[4];
                        $json['RefereeCountry'] = $hrefPieces[2];
                        $json['Referee'] = $item->plaintext;
                    }
                } else {
                    //$this->logger('empty referee',1,$id);
                }
            } else {
                $json[str_replace(":","",str_replace("/","",str_replace(" ","",$resll[0]->plaintext)))] = $resll[2]->innertext;
            }
        }

        # events (teams + all events (yellow card, goals, corners etc))
        $result = str_get_html($res2);
        $eventsOuter = $result->find('table');
        //var_dump($eventsOuter[2]->innertext);
        $events = str_get_html($eventsOuter[2]->innertext);

        $teams = $events->find('td a');

        for($i=0;$i<2;$i++) {
            if ($i==0) {
                $name = 'Home';
            } else {
                $name = 'Away';
            }
            $json['Team'.$name] = trim(str_replace("&nbsp;", '', $teams[$i]->plaintext));
            $tPieces = explode("/", $teams[$i]->href);
            $json['Team'.$name.'Id'] = $tPieces[4];
        }

        #only events
        #goal
        $tabsmain = $events->find('#score_text #score_goals table tr td');

        if ($tabsmain[0]->plaintext!==false && $tabsmain[0]->plaintext!='' && $tabsmain[0]->plaintext!='?') {
            $json['GoalHome'] = $tabsmain[0]->plaintext;
        }
        if ($tabsmain[2]->plaintext!==false && $tabsmain[2]->plaintext!='' && $tabsmain[2]->plaintext!='?') {
            $json['GoalAway'] = $tabsmain[2]->plaintext;
        }
        $json['Event'] = array();
        if (array_key_exists('GoalHome',$json) && array_key_exists('GoalAway',$json)) {
            //$json['GoalTime'] = array();
            #minutes goal
            $tabsGoal = $events->find('#score_text #score_goals table tr.gameDetails1 td');
            #detect just goal or penalty to src the image
            $penalty7 = $events->find('#score_text #score_goals table tr.gameDetails1 td img');
            for($i=0;$i<count($tabsGoal);$i=$i+4) {
                $item = array();
                $min = (trim($tabsGoal[$i]->plaintext)!="")?trim($tabsGoal[$i]->plaintext):trim($tabsGoal[$i+3]->plaintext);
                $min = substr($min, 0, strlen($min)-1);
                //$item['homescore'] = trim($tabsGoal[$i+1]->plaintext);
                //$item['awayscore'] = trim($tabsGoal[$i+2]->plaintext);
                $item['team'] = (trim($tabsGoal[$i]->plaintext)!="") ? $this->map['home'] : $this->map['away'];

                if (isset($penalty7[$i/4]->src) && $penalty7[$i/4]->src=="/image/penalty.png") {
                    $item['type'] = $this->map['pen'];
                } else {
                    $item['type'] = $this->map['goal'];
                }

                $json['Event'][$min] = $item;
            }
        }

        #yellow and red card
        $tabsmain = $events->find('#score_text #score_cards table tr td');

        if ($tabsmain[0]->plaintext!==false && $tabsmain[0]->plaintext!='' && $tabsmain[0]->plaintext!='?') {
            $json['YellowCardHome'] = $tabsmain[0]->plaintext;
        }
        if ($tabsmain[2]->plaintext!==false && $tabsmain[2]->plaintext!='' && $tabsmain[2]->plaintext!='?') {
            $json['YellowCardAway'] = $tabsmain[2]->plaintext;
        }
        if ($tabsmain[3]->plaintext!==false && $tabsmain[3]->plaintext!='' && $tabsmain[3]->plaintext!='?') {
            $json['RedCardHome'] = $tabsmain[3]->plaintext;
        }
        if ($tabsmain[5]->plaintext!==false && $tabsmain[5]->plaintext!='' && $tabsmain[5]->plaintext!='?') {
            $json['RedCardAway'] = $tabsmain[5]->plaintext;
        }

        if (array_key_exists('YellowCardHome',$json) && array_key_exists('YellowCardAway',$json)) {
            //$json['CardTime'] = array();
            #minutes yellow and red card
            $tabsCard = $events->find('#score_text #score_cards table tr.gameDetails1 td');
            ///image/yellow_card.png
            $typeCard = $events->find('#score_text #score_cards table tr.gameDetails1 td img');
            for($i=0;$i<count($tabsCard);$i=$i+4) {
                $item = array();
                $min = (trim($tabsCard[$i]->plaintext)!="")?trim($tabsCard[$i]->plaintext):trim($tabsCard[$i+3]->plaintext);
                $min = substr($min, 0, strlen($min)-1);
                //$item['homescore'] = trim($tabsCard[$i+1]->plaintext);
                //$item['awayscore'] = trim($tabsCard[$i+2]->plaintext);
                $item['team'] = (trim($tabsCard[$i]->plaintext)!="") ? $this->map['home'] : $this->map['away'];

                if (isset($typeCard[$i/4]->src) && $typeCard[$i/4]->src=="/image/yellow_card.png") {
                    $item['type'] = $this->map['yellow'];
                } else {
                    $item['type'] = $this->map['red'];
                }

                $json['Event'][$min] = $item;
            }
        }
        #subs card
        $tabsmain = $events->find('#score_text #score_subs table tr td');

        if ($tabsmain[0]->plaintext && $tabsmain[0]->plaintext!='' && $tabsmain[0]->plaintext!='?') {
            $json['SubsHome'] = $tabsmain[0]->plaintext;
        }
        if ($tabsmain[2]->plaintext && $tabsmain[2]->plaintext!='' && $tabsmain[2]->plaintext!='?') {
            $json['SubsAway'] = $tabsmain[2]->plaintext;
        }

        if (array_key_exists('SubsHome',$json) && array_key_exists('SubsAway',$json)) {
            //$json['SubsTime'] = array();
            #minutes subs
            $tabsCard = $events->find('#score_text #score_subs table tr.gameDetails1 td');

            for($i=0;$i<count($tabsCard);$i=$i+4) {
                $item = array();
                $min = (trim($tabsCard[$i]->plaintext)!="")?trim($tabsCard[$i]->plaintext):trim($tabsCard[$i+3]->plaintext);
                $min = substr($min, 0, strlen($min)-1);
                $item['team'] = (trim($tabsCard[$i]->plaintext)!="") ? $this->map['home'] : $this->map['away'];
                //$item['homescore'] = trim($tabsCard[$i+1]->plaintext);
                //$item['awayscore'] = trim($tabsCard[$i+2]->plaintext);
                $item['type'] = $this->map['sub'];

                $json['Event'][$min] = $item;
            }
        }
        #others events (CORNERS + FOULS as main)
        $tabsEvents = $events->find('#score_text #score_other table tr td');
        $json['Stats'] = array();


        for($i=0;$i<count($tabsEvents);$i=$i+3) {
            $eventName = str_replace(" ","",trim($tabsEvents[$i+1]->plaintext));
            if (strripos($eventName, 'Corners')!== false) {
                if (strripos($eventName, 'brackets')!== false) {
                    $eventName = 'Corners';
                    if (strripos($tabsEvents[$i]->plaintext, '(')!== false) {
                        //$json['League'] = trim(substr($h1Pieces[1], 0, strpos($h1Pieces[1], " (")));
                        //$json['Country'] = substr(substr(trim(strstr($h1Pieces[1], '(')), 0, -1), 1);
                        $home = substr(substr(trim(strstr($tabsEvents[$i]->plaintext, '(')), 0, -1), 1);
                        $away = substr(substr(trim(strstr($tabsEvents[$i+2]->plaintext, '(')), 0, -1), 1);
                        $tabsEvents[$i]->plaintext = trim(substr($tabsEvents[$i]->plaintext, 0, strpos($tabsEvents[$i]->plaintext, " (")));
                        $tabsEvents[$i+2]->plaintext = trim(substr($tabsEvents[$i+2]->plaintext, 0, strpos($tabsEvents[$i+2]->plaintext, " (")));

                        $json['Stats'][$eventName.'h1'] = array();
                        $json['Stats'][$eventName.'h1'][$this->map['home']] = $home;
                        $json['Stats'][$eventName.'h1'][$this->map['away']] = $away;
                    }
                }

            }

            if (trim($tabsEvents[$i]->plaintext!==false) && trim($tabsEvents[$i]->plaintext)!='' && trim($tabsEvents[$i]->plaintext)!='?') {
                if (!isset($json['Stats'][$eventName])) {
                    $json['Stats'][$eventName] = array();
                }
                $json['Stats'][$eventName][$this->map['home']] = trim($tabsEvents[$i]->plaintext);
                $json['Stats'][$eventName][$this->map['away']] = trim($tabsEvents[$i+2]->plaintext);
            }
        }
        if (count($json['Stats'])==0) {
            unset($json['Stats']);
        }

        #preview result
        //echo "<pre>"; print_r($json); echo "</pre>";

        $this->json = $json;
        $this->error = false;

        // Anywhere else in the script
        //$this->log($id, 'MATCH SUCCESS WAS PARSERED FOR ('.(microtime(true) - $time_start).'s)');
        return json_encode($json);

    }

    /**
     * Prepare fields from result
     */
    public function prepareFields() {
        if ($this->error || !count($this->json)) {
            //$this->logger('error prepare field - exist error or not result',3,$this->json['Id']);
            return false;
        }

        foreach($this->json as $key=>$item) {
            if ($item=='') {
                unset($this->json[$key]);
            }
        }

        if (!array_key_exists('Matchstart',$this->json)) {
            if (array_key_exists('Time',$this->json)) {
                //$this->log($this->json['Id'], 'ERROR - CAN\'T DELECT MATCH'); //♥
                $this->error = true;
                return;
            }
        } else {
            unset($this->json['Time']);
        }
        if (array_key_exists('League',$this->json)) {
            if (!array_key_exists('Tournament',$this->json)) {
                $this->json['Tournament'] = $this->json['League'];
            }
            unset($this->json['League']);
        } else if (!array_key_exists('Tournament',$this->json)) {
            //$this->log($this->json['Id'], 'ERROR - HAVEN\'T GET TOURNAMENT'); //♥
            $this->error = true;
            return;
        }

        if (array_key_exists('GoalHome',$this->json) && $this->json['GoalHome']!==false) {
            $this->json['Stats']['Goals'][$this->map['home']] = $this->json['GoalHome'];
            //unset($this->json['GoalHome']);
        }
        if (array_key_exists('GoalAway',$this->json) && $this->json['GoalAway']!==false) {
            $this->json['Stats']['Goals'][$this->map['away']] = $this->json['GoalAway'];
            //unset($this->json['GoalAway']);
        }

        if (isset($this->json['YellowCardHome']) && isset($this->json['YellowCardAway'])) {
            $this->json['Stats']['YellowCard'][$this->map['home']] = $this->json['YellowCardHome'];
            $this->json['Stats']['YellowCard'][$this->map['away']] = $this->json['YellowCardAway'];
            unset($this->json['YellowCardHome']);
            unset($this->json['YellowCardAway']);
        }
        if (isset($this->json['RedCardHome']) && isset($this->json['RedCardAway'])) {
            $this->json['Stats']['RedCard'][$this->map['home']] = $this->json['RedCardHome'];
            $this->json['Stats']['RedCard'][$this->map['away']] = $this->json['RedCardAway'];
            unset($this->json['RedCardHome']);
            unset($this->json['RedCardAway']);
        }


        if (array_key_exists('Event',$this->json)) {
            if (count($this->json['Event'])>0){
                ksort($this->json['Event']);
            }
        }

        #preview result
        //echo "<pre>"; print_r($this->json); echo "</pre>";

        //$this->log($this->json['Id'], 'RESULT SUCCESS WAS PREPARE');
        return true;
    }

    /**
     * Save data on data base
     */
    public function addToServer() {
        if ($this->error || !count($this->json)) {
            //$this->logger('error insert in db - exist error or not result',3,$this->json['Id']);
            return false;
        }
        $r = $this->json;

        /*if (!$model = \app\modules\soccer\models\Country::findOne(['name' => $r['Country']])) {
            $model =  new \app\modules\soccer\models\Country;
            $model->name = $r['Country'];
            $model->image = strtolower($r['Country']).'.png';
            if (!$model->save()) {
                $err = json_encode($model->getErrors());
                $this->logger('error insert Country ['.$r['Country'].']: '.$err,3,$r['Id']);
            }
        }*/

        if (($id = \app\modules\soccer\models\Country::addCountryIfNotExists($r['Country'])) !== false) {
            $r['CountrtyId'] = $id;
        } else {
            //$this->log($r['Id'], 'error insert Country ['.$r['Country'].']: ');
        }

        if (array_key_exists('Stadium',$r) && $r['Stadium']!==false) {
            if (!$model = \app\modules\soccer\models\Stadium::findOne(['name' => $r['Stadium']])) {
                $model =  new \app\modules\soccer\models\Stadium;
                $model->name = $r['Stadium'];
                $model->country_id = $r['CountrtyId'];
                if (!$model->save()) {
                    $err = json_encode($model->getErrors());
                    //$this->log($r['Id'], 'error insert Stadium ['.$r['Stadium'].']'.$err);
                }
            }
            $r['StadiumId'] = $model->id;
        }

        if (array_key_exists('Referee',$r) && $r['Referee']!==false) {
            if (!$model = \app\modules\soccer\models\Referee::findOne(['fapi_id' => $r['RefereeId']])) {
                $model =  new \app\modules\soccer\models\Referee;
                $model->name = $r['Referee'];
                $model->fapi_id = $r['RefereeId'];

                //RefereeCountry
                if (array_key_exists('RefereeCountry',$r) && $r['RefereeCountry']!==false) {
                    if (($id = \app\modules\soccer\models\Country::addCountryIfNotExists(ucfirst($r['RefereeCountry']))) !== false) {
                        $model->country_id = $id;
                    } else {
                        //$this->log('error insert referee country [' . $r['RefereeCountry'] . ']: ', 3, $r['Id']);
                    }
                }

                if (!$model->save()) {
                    $err = json_encode($model->getErrors());
                    //$this->log($r['Id'], 'error insert Referee ['.$r['Referee'].']'.$err);
                }
            }
            $r['RefereeId'] = $model->id;
        }
        if (array_key_exists('Tournament',$r) && $r['Tournament']!==false) {
            if (!$model = \app\modules\soccer\models\Competition::findOne(['name' => $r['Tournament'], 'country_id' => $r['CountrtyId']])) {
                $model =  new \app\modules\soccer\models\Competition;
                $model->name = $r['Tournament'];
                $model->country_id = $r['CountrtyId'];

                if (!$model->save()) {
                    $err = json_encode($model->getErrors());
                    //$this->log($r['Id'], 'error insert Tournament ['.$r['Tournament'].']'.$err);
                }
            }
            $r['TournamentId'] = $model->id;
        }

        for($i=0;$i<2;$i++) {
            $type = ($i==0)?'Home':'Away';
            if (!$model = \app\modules\soccer\models\Team::findOne(['fapi_id' => $r['Team'.$type.'Id']])) {
                $model =  new \app\modules\soccer\models\Team;
                $model->name = $r['Team'.$type];
                $model->fapi_id = $r['Team'.$type.'Id'];

                //TeamHomeCountry
                if (array_key_exists('Team'.$type.'Country',$r) && $r['Team'.$type.'Country']!==false) {
                    if (($id = \app\modules\soccer\models\Country::addCountryIfNotExists(ucfirst($r['Team'.$type.'Country']))) !== false) {
                        $model->country_id = $id;
                    } else {
                        //$this->log($r['Id'], 'error insert '.$type.' team country [' . $r['Team'.$type.'Country'] . ']: ');
                    }
                }

                if (!$model->save()) {
                    $err = json_encode($model->getErrors());
                    //$this->log($r['Id'], 'error insert '.$type.' team ['.$r['Team'.$type.'Country'].']'.$err);
                }
            }
            $r['Team'.$type.'Id'] = $model->id;
        }

        $newModel = false;

        //create or update match!
        if (!$model = Match::findOne(['fapi_id' => $r['Id']])) {
            $model = new Match;
            $newModel = true;
        }

        $model->status = Match::STATUS_FINISHED;
        $model->fapi_id = $r['Id'];
        $model->home_id = $r['TeamHomeId'];
        $model->away_id = $r['TeamAwayId'];
        $model->homegoals = (isset($r['GoalHome']))?isset($r['GoalHome']):-1;
        $model->awaygoals = (isset($r['GoalAway']))?isset($r['GoalAway']):-1;
        $model->competition_id = (isset($r['TournamentId']))?$r['TournamentId']:null;

        $model->referee_id = (isset($r['RefereeId']))?$r['RefereeId']:null;
        $model->stadium_id = (isset($r['StadiumId']))?$r['StadiumId']:null;
        $model->matchday = (isset($r['Weekround']))?$r['Weekround']:null;
        $model->season = (isset($r['Season']))?$r['Season']:null;


        // = date('Y-m-d h:i:00', strtotime($t[1]));

        $myDateTime = \DateTime::createFromFormat('d/m/y H:i', $r['Matchstart']);
        //print_r(\DateTime::getLastErrors());
        $r['Matchstart'] = $myDateTime->format('Y-m-d H:i:00');

        //$r['Matchstart'] = \date_format(\date_create_from_format('d/m/y h:i', $r['Matchstart']), 'Y-m-d h:i:00');

        $model->date = ($r['Matchstart'])?$r['Matchstart']:null;

        if (!isset($r['GoalHome']) || !isset($r['GoalAway']) || (strtotime($model->date)>strtotime('now'))) {
            $model->status = Match::STATUS_NOTSTARTED;
        }


        if (!$model->save()) {
            $err = json_encode($model->getErrors());
            //$this->log($r['Id'], 'error insert match'.$err);
        }
        $r['MatchId'] = $model->id;


        if (!$model = MatchEvent::findOne(['match_id' => $r['MatchId']])) {
            foreach($r['Event'] as $key => $value) {
                $model = new MatchEvent;
                $model->match_id = $r['MatchId'];
                $model->type = $value['type'];
                $model->minute = $key;
                $model->team = $value['team'];
                //$model->result = $r['GoalAway'];

                if (!$model->save()) {
                    $err = json_encode($model->getErrors());
                    //$this->log($r['Id'], 'error insert match event ['.$key.'=>'.$value['type'].']'.$err);
                }
            }
        }

        $bob = false;
        if (!$model = MatchStats::findOne(['match_id' => $r['MatchId']])) {
            $bob = true;
        }

        if (isset($r['Stats'])) {
            foreach($r['Stats'] as $key => $value) {
                foreach( $value as $k => $v) {
                    if ($bob || !$model = MatchStats::findOne(['match_id' => $r['MatchId'], 'team' => $k, 'fkey' => MatchStats::$FIELDS[$key]])) {
                        $model = new MatchStats;
                    }
                    $model->match_id = $r['MatchId'];
                    $model->value = $v;
                    $model->fkey = MatchStats::$FIELDS[$key];
                    $model->team = $k;

                    if (!$model->save()) {
                        $err = json_encode($model->getErrors());
                        //$this->log($r['Id'], 'error insert match stats ['.$key.']'.$err);
                    }
                }
            }
        }

        $action = ($newModel) ? "INSERT INTO" : "UPDATE";
        $this->log($r['Id'], 'SUCCESS ' . $action. ' TABLE soccer_match [id:'.$r['MatchId'].']');

    }

}

/*
     public static $F = array(
        'T' => 'Time',
        'C' => 'Country',
        'TH' => 'TeamHome',
        'TA' => 'TeamAway',
        'RI' => 'RefereeId',
        'RC' => 'RefereeCountry',
        'R' => 'Referee',
        'GH' => 'GoalHome',
        'GA' => 'GoalAway',
        'GT' => 'GoalTime',
    );


 */