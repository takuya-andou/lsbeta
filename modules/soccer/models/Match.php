<?php

namespace app\modules\soccer\models;

use app\modules\betting\models\Bet;
use app\modules\soccer\repositories\MatchRepository;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "{{%match}}".
 *
 * @property integer $id
 * @property string $date
 * @property integer $status
 * @property integer $home_id
 * @property integer $away_id
 * @property integer $homegoals
 * @property integer $awaygoals
 * @property integer $competition_id
 * @property integer $stadium_id
 * @property integer $referee_id
 * @property integer $fapi_id
 * @property string $matchday
 * @property string $description
 * @property string $season
 *
 * @property Team $home
 * @property Team $away
 * @property Competition $competition
 * @property Stadium $stadium
 * @property Referee $referee
 * @property MatchEvent[] $matchEvents
 * @property MatchStats[] $matchStats
 */
class Match extends \yii\db\ActiveRecord
{

    const STATUS_FINISHED = 1;
    const STATUS_NOTSTARTED = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%match}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'status', 'home_id', 'away_id'], 'required'],
            [['date'], 'safe'],
            [['status', 'home_id', 'away_id', 'homegoals', 'awaygoals', 'competition_id', 'stadium_id', 'referee_id', 'fapi_id'], 'integer'],
            [['description'], 'string'],
            [['matchday','season'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Date',
            'status' => 'Status',
            'home_id' => 'Home ID',
            'away_id' => 'Away ID',
            'homegoals' => 'Homegoals',
            'awaygoals' => 'Awaygoals',
            'competition_id' => 'Comptition ID',
            'stadium_id' => 'Stadium ID',
            'referee_id' => 'Referee ID',
            'fapi_id' => 'Fapi ID',
            'matchday' => 'Matchday',
            'description' => 'Description',
            'season' => 'season',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHome()
    {
        return $this->hasOne(Team::className(), ['id' => 'home_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAway()
    {
        return $this->hasOne(Team::className(), ['id' => 'away_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompetition()
    {
        return $this->hasOne(Competition::className(), ['id' => 'competition_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStadium()
    {
        return $this->hasOne(Stadium::className(), ['id' => 'stadium_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReferee()
    {
        return $this->hasOne(Referee::className(), ['id' => 'referee_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMatchEvents()
    {
        return $this->hasMany(MatchEvent::className(), ['match_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMatchStats()
    {
        return $this->hasMany(MatchStats::className(), ['match_id' => 'id']);
    }

    public function getBets()
    {
        return $this->hasMany(Bet::className(), ['match_id' => 'id']);
    }

    /**
     * @var MatchRepository $repository
     */
    protected static $repository;

    /**
     * @return MatchRepository
     */
    public static function getRepository() {
        if (!self::$repository) {
            self::$repository = new MatchRepository();
        }
        return self::$repository;
    }




    //--------------------------------------------------------------------------------------
    //@todo все что ниже, нужно перенести в репозиторий [amorgunov 28-04-2016]

    public static function getMatch($match_id) {
        return self::find()
            /*->joinWith([
                'home' => function ($query) {
                    $query->joinWith('country');
                },
                'referee',
                'stadium',
            ])*/
            ->where([self::tableName().'.id'=>$match_id])
            ->one();
        $result = Match::getDb()->cache(function ($db) use ($match_id) {
            return self::find()
                /*->joinWith([
                    'home' => function ($query) {
                        $query->joinWith('country');
                    },
                    'referee',
                    'stadium',
                ])*/
                ->where([self::tableName().'.id'=>$match_id])
                ->one();
        });
        return $result;
    }

    public static function getMatchOneTeam($team_id, $team_id2, $date, $eventId, $competition_id = false, $status = self::STATUS_FINISHED){
        /*$key = __FUNCTION__ . $team_id . $competition_id . $status;
        $cache  = Yii::$app->cache;
        $data = $cache->get($key);*/

        //var_dump($query->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql);
        $query = new \yii\db\Query;
        $query->select(['sm.id', 'date', 'home_id', 'away_id', 'competition_id', 'SUM(value) as event_value'])
            ->from('soccer_match sm')
            ->leftJoin('soccer_match_stats sms', 'sm.id = sms.match_id ')
            ->where(['fkey' => (int) $eventId, 'status' => $status])
            ->andWhere(['<', 'sm.date', $date])
            ->andFilterWhere(['or',
                ['home_id' => [$team_id, $team_id2]],
                ['away_id' => [$team_id, $team_id2]]])
            ->orderBy('sm.date desc')
            ->groupBy('sm.id');
        $command = $query->createCommand();
        //var_dump($command->getRawSql());
        //exit();
        $resp = $command->queryAll();
        return $resp;

        return Match::find()
            ->joinWith([
                /*'home' => function ($query) {
                    $query->joinWith('country');
                },
                ..'referee',*/
                'matchStats' => function ($query) {
                    $query->where(['fkey' => 1]);
                },
                //'competition'
            ])
            ->select(['soccer_match.id', 'date', 'home_id', 'away_id', 'competition_id', 'SUM(value) as event_value'])
            ->where(['status' => $status])
            ->andFilterWhere(['or',
                ['home_id' => [$team_id,$team_id2]],
                ['away_id' => [$team_id,$team_id2]]])
            ->groupBy(['soccer_match.id'])
            ->orderBy(['date' => SORT_DESC])
            ->all();

        //var_dump($query->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql);
        //exit();

        $result = Match::getDb()->cache(function ($db) use ($team_id, $team_id2, $status) {
            return Match::find()
                ->joinWith([
                    /*'home' => function ($query) {
                        $query->joinWith('country');
                    },
                    ..'referee',*/
                    'matchStats' => function ($query) {
                        $query->where(['fkey' => 1]);
                    },
                    //'competition'
                ])
                ->select(['soccer_match.id', 'date', 'home_id', 'away_id', 'competition_id', 'SUM(value)'])
                ->where(['status' => $status])
                ->andFilterWhere(['or',
                    ['home_id' => [$team_id,$team_id2]],
                    ['away_id' => [$team_id,$team_id2]]])

                ->orderBy(['date' => SORT_DESC])
                ->all();
        });
        return $result;

        if ($data === false) {

            //echo "load cache \r\n";

            $data = \app\modules\soccer\models\Match::find()
                ->joinWith([
                    /*'home' => function ($query) {
                        $query->joinWith('country');
                    },
                    ..'referee',*/
                    'matchStats' => function ($query) {
                        $query->where(['fkey' => 1]);
                    },
                    //'competition'
                ])
                ->select(['soccer_match.id', 'date', 'home_id', 'away_id', 'competition_id'])
                ->where(['status' => $status])
                //->andWhere(['competition_id' => $currentMatch->competition_id])
                ->andFilterWhere(['or',
                    ['home_id' => [$team_id,$team_id2]],
                    ['away_id' => [$team_id,$team_id2]]])

                ->orderBy(['date' => SORT_DESC])
                ->all();

            //var_dump($data->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql);
            //exit();

            $cache->set($key, $data, 10800);
        }
        return $data;
    }


    public static function getMatchesByReferee($refereeId , $date, $eventId) {

        $query = new \yii\db\Query;
        $query->select(['sm.id', 'date', 'home_id', 'away_id', 'competition_id', 'SUM(value) as event_value'])
            ->from('soccer_match sm')
            ->leftJoin('soccer_match_stats sms', 'sm.id = sms.match_id ')
            ->where(['fkey' => (int) $eventId])
            ->andWhere(['referee_id' => $refereeId])
            ->andWhere(['<', 'sm.date', $date])
            ->orderBy('sm.date desc')
            ->groupBy('sm.id');

        $command = $query->createCommand();
        $resp = $command->queryAll();
        return $resp;

        /*
        $key = __FUNCTION__ . $ref_id . $date_start;
        $cache  = Yii::$app->cache;
        $data = $cache->get($key);

        if ($data === false) {

            //echo "load cache \r\n";

            $data = self::find()
                ->joinWith([
                    'home' => function ($query) {
                        $query->joinWith('country');
                    },
                    'referee',
                    'stadium',
                ])
                ->where([self::tableName().'.referee_id'=>$ref_id])
                ->andWhere(['>', self::tableName().'.date', $date_start])
                ->all();

            $cache->set($key, $data, 10800);
        }
        return $data;*/
    }

    public static function findByLeagueIdAndDateRange($id, $lower_limit, $upper_limit){
        return
            self::find()
                ->where('competition_id = :c_id and date >= :lower_limit and date <= :upper_limit',
                    [':c_id' => $id, ':lower_limit' => $lower_limit, ':upper_limit' => $upper_limit])
                ->all();
    }
    
    public static function getGoals($stats) {
        $res = array(
            'home' => '?',
            'away' => '?',
        );
        foreach ($stats as $item) {
            if ($item->fkey == MatchStats::$FIELDS['Goals']) {
                if ($item->team == 1) {
                    $res['home'] =  $item->value;
                } else if ($item->team == 2) {
                    $res['away'] =  $item->value;
                }
            }
        }

        return $res;
    }

    public static function findMatchByFapiID($id){
        return
            self::find()
                ->where('fapi_id = :fapi_id',
                    [':fapi_id' => $id])
                ->one();
    }

    public static function getStackOfMatchesFapiID($fapi_id, $limit, $since_date = '2014-09-01 00:00:00'){
        return
            self::find()
                ->where('date >= :date',
                    [':date' => $since_date])
                ->andWhere('fapi_id > :fapi_id',
                    [':fapi_id' => $fapi_id])
                ->orderBy([
                    'fapi_id' => SORT_ASC
                ])
                ->limit($limit)
                ->all();
    }
    public static function getStackOfMatchesFapiIDAndCompetition($fapi_id, $competition_id, $limit, $since_date = '2014-09-01 00:00:00'){
        return
            self::find()
                ->where('date >= :date',
                    [':date' => $since_date])
                ->andWhere('competition_id=:competition_id',
                    [':competition_id' => $competition_id])
                ->andWhere('fapi_id > :fapi_id',
                    [':fapi_id' => $fapi_id])
                //->andWhere(['>', self::tableName().'.date', '2016-01-01 00:00:00'])
                ->orderBy([
                    'fapi_id' => SORT_ASC
                ])
                ->limit($limit)
                ->all();
    }

    public static function getMatchesByDateRange($since_date, $until_date, $limit = null, $order = SORT_ASC){
        $query = self::find()
            ->where('date >= :since_date and date <= :until_date',
                [':since_date' => $since_date, ':until_date' => $until_date])
            ->orderBy([
                'date' => $order
            ]);
        if(isset($limit))
            $query->limit($limit);
        return $query->all();
    }

    public static function getMatchesByDateRangeWithTeams($since_date, $until_date, $limit = null, $order = SORT_ASC){
        $query = new Query();
        $query
            ->select([self::tableName().'.*', 'home_name' => 'ht.name', 'away_name' => 'at.name'])
            ->from(self::tableName())
            ->leftJoin(['ht' => Team::tableName()], 'home_id=ht.id')
            ->leftJoin(['at' => Team::tableName()], 'away_id=at.id')
            ->where('date >= :since_date and date <= :until_date',
                [':since_date' => $since_date, ':until_date' => $until_date])
            ->orderBy([
                'date' => $order
            ]);
        if(isset($limit))
            $query->limit($limit);

        return $query->all();

    }
}
