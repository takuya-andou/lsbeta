<?php

namespace app\modules\forecast\models;

use app\modules\betting\config\BettingConfig;
use app\modules\betting\models\BetEvent;
use app\modules\betting\models\BetHistory;
use app\modules\soccer\models\MatchStats;
use Yii;
use app\modules\forecast\config\ForecastConfig;
use app\modules\user\models\User;
use app\modules\soccer\models\Match;
use app\modules\betting\models\BetType;
use app\modules\betting\models\Bet;

/**
 * This is the model class for table "{{%forecast}}".
 *
 * @property integer $id
 * @property integer $match_id
 * @property integer $user_id
 * @property integer $status
 * @property string $date
 * @property string $title
 * @property string $summary
 * @property string $content
 * @property integer $views
 * @property string $update_date
 * @property integer $updated_by_user_id
 * @property integer $bet_id
 * @property double $current_coef
 * @property double $bet_amount
 * @property integer $bet_in_history
 * @property integer $result
 *
 * @property Bet $bet
 * @property Match $match
 * @property User $updatedByUser
 * @property User $user
 */
class Forecast extends \yii\db\ActiveRecord
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_MOVE_BET = 'move_bet';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%forecast}}';
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] =[ 'match_id', 'title', 'summary', 'content', 'bet_id', 'bet_amount'];
        $scenarios[self::SCENARIO_UPDATE] =[ 'match_id', 'title', 'summary', 'content', 'bet_id', 'bet_amount'];
        $scenarios[self::SCENARIO_MOVE_BET] =[];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['match_id', 'content'], 'required'],
            [['match_id', 'user_id', 'status', 'views', 'updated_by_user_id', 'bet_id', 'result'], 'integer'],
            [['date', 'update_date'], 'safe'],
            [['content'], 'string', 'min' => ForecastConfig::MIN_CONTENT_LEN],
            [['summary'], 'string', 'max' => ForecastConfig::MAX_SUMMARY_LEN],
            [['current_coef', 'bet_amount'], 'number'],
            [['title'], 'string', 'max' => ForecastConfig::MAX_TITLE_LEN],
            [['bet_id'], 'exist', 'skipOnError' => true, 'targetClass' => Bet::className(), 'targetAttribute' => ['bet_id' => 'id'], 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['bet_id'], 'exist', 'skipOnError' => true, 'targetClass' => BetHistory::className(), 'targetAttribute' => ['bet_id' => 'id'], 'on' => [self::SCENARIO_MOVE_BET]],
            [['match_id'], 'exist', 'skipOnError' => true, 'targetClass' => Match::className(), 'targetAttribute' => ['match_id' => 'id']],
            [['updated_by_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by_user_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
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
            'status' => 'Status',
            'date' => 'Date',
            'title' => 'Title',
            'summary' => 'Summary',
            'content' => 'Content',
            'views' => 'Views',
            'update_date' => 'Update Date',
            'updated_by_user_id' => 'Updated By User ID',
            'bet_id' => 'Bet ID',
            'current_coef' => 'Current Coef',
            'bet_amount' => 'Bet Amount',
            'result' => 'Result',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBet()
    {
        if($this->bet_in_history == BettingConfig::BET_NOT_IN_HISTORY)
        return $this->hasOne(Bet::className(), ['id' => 'bet_id']);
        else $this->hasOne(BetHistory::className(), ['id' => 'bet_id']);
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
    public function getUpdatedByUser()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            switch($this->scenario){
                case self::SCENARIO_CREATE:
                    $this->date = date("Y-m-d H:i:s");
                    $this->user_id = \Yii::$app->user->id;
                    $this->result = ForecastConfig::RESULT_UNKNOWN;
                    if(empty($this->title) || strlen($this->title) == 0){
                        $match = $this->match;
                        $this->title = $match->home->name.' - '.$match->away->name;
                    }
                    if(isset($this->bet_id)){
                        $bet = Bet::find()->where('id=:id', [':id' => $this->bet_id])->one();
                        $this->current_coef = $bet->coef;
                    }
                    $this->status = ForecastConfig::STATUS_TO_BE_MODERATED;
                    break;
                case self::SCENARIO_UPDATE:
                    $this->update_date = date("Y-m-d H:i:s");
                    $this->updated_by_user_id = \Yii::$app->user->id;
                    break;
                default: break;
            }
            return true;
        }
        return false;
    }

    public function addViews($count = 1){
        $this->views += $count;
        $this->update();
    }

    /**
     * updates forecast result according to stats written in match stats table
     */
    public static function updateResults(){
        $since_date = new \DateTime(date('Y-m-d H:i:s'));
        $interval = new \DateInterval('P1D');
        $interval->invert = true;
        $since_date->add($interval);//обработать все прогнозы на день
        //echo $until_date->format('Y-m-d H:i:s');
        $forecasts_to_process = self::find()
            ->join('LEFT JOIN', Match::tableName().' `m`', 'match_id=m.id')
            ->where('result=:uresult or result=:eresult',
                [':uresult' => ForecastConfig::RESULT_UNKNOWN, ':eresult' => ForecastConfig::RESULT_PROCESSING_ERROR])
            ->andWhere('m.date >= :date',[':date' => $since_date->format('Y-m-d H:i:s')])
            ->all();
       /* echo '<pre>';
        print_r($bets_to_process);
        die();*/
        foreach($forecasts_to_process as $forecast){
            $forecast->updateResult();
        }


    }

    private function updateResult(){
        //type, event ids - are relevant to tables BetType, BetEvent

        $bet = $this->bet;
        $this->result = $bet->getBetResult();
		$this->save();
    }
}
