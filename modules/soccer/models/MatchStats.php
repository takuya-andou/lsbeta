<?php

namespace app\modules\soccer\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "{{%match_stats}}".
 *
 * @property integer $id
 * @property integer $match_id
 * @property string $fkey
 * @property integer $value
 * @property integer $team
 *
 * @property Match $match
 */
class MatchStats extends \yii\db\ActiveRecord {

    public static $FIELDS = array(
        'Corners' => 1,
        'Cornersh1' => 2,
        'Shotsontarget' => 3,
        'Shotswide' => 4,
        'Fouls' => 5,
        'Offsides' => 6,
        'Possession' => 7,
        'Substitutions' => 8,
        'Goals' => 9,
        'YellowCard' => 10,
        'RedCard' => 11,
        'Totalshots' => 12,
        'Blockedshots' => 13,
        'Woodwork' => 14,
        'Passes' => 15,
        'Completedpasses' => 16,
        'Penalty' => 17
    );
    
    public static function getFieldId($name) {
        return self::$FIELDS[$name];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%match_stats}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['match_id', 'fkey', 'value', 'team'], 'required'],
            [['match_id', 'value', 'team'], 'integer'],
            //[['fkey'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'match_id' => 'Match ID',
            'fkey' => 'Fkey',
            'value' => 'Value',
            'team' => 'Team',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMatch()
    {
        return $this->hasOne(Match::className(), ['id' => 'match_id']);
    }

    /**
     * @param $match_id
     * @param $stat_id
     * @param null $team
     * @return array|false
     */
    public static function getMatchStat($match_id, $stat_id){
        $query_overall = new Query();
        $query_home_team = new Query();
        $query_away_team = new Query();

        $query_home_team
            ->select('value')
            ->from(self::tableName())
            ->where('fkey=:fkey and match_id=:match_id and team=1',
                [':fkey' => $stat_id, ':match_id' => $match_id]);
        $query_away_team
            ->select('value')
            ->from(self::tableName())
            ->where('fkey=:fkey and match_id=:match_id and team=2',
                [':fkey' => $stat_id, ':match_id' => $match_id]);

        $query_overall->select(['sm.id', 'date', 'home_id', 'away_id',
            'home_value' => $query_home_team, 'away_value' => $query_away_team,
            'competition_id', 'SUM(value) as event_value'])
            ->from('soccer_match sm')
            ->leftJoin('soccer_match_stats sms', 'sm.id = sms.match_id ')
            ->where('fkey =:fkey and sms.match_id=:match_id',
                [':fkey' => (int) $stat_id, ':match_id' => $match_id]);
        $query_overall
            ->groupBy('sm.id');

        $command = $query_overall->createCommand();
        return $command->queryOne();
    }
}
