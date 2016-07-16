<?php

namespace app\modules\betting\models;
use app\modules\betting\config\BettingConfig;
use app\modules\forecast\models\Forecast;
use app\modules\soccer\models\Match;
use app\modules\soccer\models\MatchStats;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "{{%bet}}".
 *
 * @property integer $id
 * @property integer $match_id
 * @property string $date
 * @property integer $bookie_id
 * @property integer $type_id
 * @property integer $member
 * @property double $value
 * @property integer $event_id
 * @property integer $sign
 * @property double $coef
 * @property integer $external_match_id
 * @property string $update_date
 *
 * @property Match $match
 * @property Bookie $bookie
 * @property BetType $type
 * @property BetEvent $event
 * @property Forecast[] $forecasts
 */
class Bet extends DBet
{
    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return '{{%bet}}';
    }

    /**
     * @inheritdoc
     */

    public function rules()
    {
        return [
            [['match_id', 'date', 'bookie_id', 'type_id', 'event_id', 'coef'], 'required'],
            [['match_id', 'bookie_id', 'type_id', 'member', 'event_id', 'sign'], 'integer'],
            [['external_match_id'], 'string', 'max' => 256],
            [['date'], 'safe'],
            [['value', 'coef', 'initial_coef'], 'number'],
            [['match_id'], 'exist', 'skipOnError' => true, 'targetClass' => Match::className(), 'targetAttribute' => ['match_id' => 'id']],
            [['bookie_id'], 'exist', 'skipOnError' => true, 'targetClass' => Bookie::className(), 'targetAttribute' => ['bookie_id' => 'id']],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => BetType::className(), 'targetAttribute' => ['type_id' => 'id']],
            [['event_id'], 'exist', 'skipOnError' => true, 'targetClass' => BetEvent::className(), 'targetAttribute' => ['event_id' => 'id']],
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
            'user_id' => 'User ID',
            'active' => 'Active',
            'date' => 'Date',
            'title' => 'Title',
            'summary' => 'Summary',
            'content' => 'Content',
            'views' => 'Views',
            'update_date' => 'Update Date',
            'updated_by_user_id' => 'Updated By User ID',
            'bet_id' => 'Bet ID',
            'bet_amount' => 'Bet Amount',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBet()
    {
        return $this->hasOne(Bet::className(), ['id' => 'bet_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMatch()
    {
        return $this->hasOne(Match::className(), ['id' => 'match_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBookie()
    {
        return $this->hasOne(Bookie::className(), ['id' => 'bookie_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(BetType::className(), ['id' => 'type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEvent()
    {
        return $this->hasOne(BetEvent::className(), ['id' => 'event_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getForecasts()
    {
        return $this->hasMany(Forecast::className(), ['bet_id' => 'id']
            )->where('bet_in_history=:bet_in_history',[':bet_in_history' => BettingConfig::BET_NOT_IN_HISTORY]);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if($this->scenario == self::SCENARIO_CREATE){
                $this->initial_coef = $this->coef;
            }
            return true;
        }
        return false;
    }

    public function saveToHistory(){

        $hbet = BetHistory::updateOrSave($this->attributes);
        $forecasts = $this->forecasts;
        foreach($forecasts as $forecast){
            $forecast->bet_in_history = true;
            $forecast->bet_id = $hbet->id;
            $forecast->scenario = Forecast::SCENARIO_MOVE_BET;
            if($forecast->save()){

            }
            else{

            }
        }
        /*echo '<pre>';
        print_r($hbet);
        die();*/
    }

    public static function moveToHistory(){
        $outdated_bets = self::getOutdatedBets();//self::find()->where('id=:id', [':id' => 314])->all();
        foreach($outdated_bets as $bet){
            //$forecasts = $bet->forecasts;
            $bet->saveToHistory();
            $bet->delete();
        }
        /*$forecast = $bet->forecasts;
        echo '<pre>';
        print_r($forecast);
        die();*/

    }

    public static function getOutdatedBets(){
        //$date = date('Y-m-d H:i:s');
        return self::find()
            ->leftJoin(['m' => Match::tableName()], self::tableName().'.match_id=m.id')
            ->where('m.status=:match_played_st', [':match_played_st' => 1])
            ->all();
    }

}
