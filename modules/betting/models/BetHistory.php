<?php

namespace app\modules\betting\models;

use app\modules\betting\config\BettingConfig;
use app\modules\forecast\models\Forecast;
use app\modules\soccer\models\Match;
use Yii;

/**
 * This is the model class for table "{{%bet_history}}".
 *
 * @property integer $id
 * @property integer $match_id
 * @property integer $bookie_id
 * @property integer $type_id
 * @property integer $event_id
 * @property integer $member
 * @property double $value
 * @property integer $sign
 * @property double $coef
 * @property double $initial_coef
 * @property string $external_match_id
 * @property string $date
 * @property string $update_date
 * @property string $move_date
 *
 * @property BetEvent $event
 * @property Bookie $bookie
 * @property Match $match
 * @property BetType $type
 */
class BetHistory extends DBet
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bet_history}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['match_id', 'bookie_id', 'type_id', 'event_id'], 'required'],
            [['match_id', 'bookie_id', 'type_id', 'event_id', 'member', 'sign'], 'integer'],
            [['value', 'coef', 'initial_coef'], 'number'],
            [['date', 'update_date', 'move_date'], 'safe'],
            [['external_match_id'], 'string', 'max' => 255],
            [['event_id'], 'exist', 'skipOnError' => true, 'targetClass' => BetEvent::className(), 'targetAttribute' => ['event_id' => 'id']],
            [['bookie_id'], 'exist', 'skipOnError' => true, 'targetClass' => Bookie::className(), 'targetAttribute' => ['bookie_id' => 'id']],
            [['match_id'], 'exist', 'skipOnError' => true, 'targetClass' => Match::className(), 'targetAttribute' => ['match_id' => 'id']],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => BetType::className(), 'targetAttribute' => ['type_id' => 'id']],
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
            'bookie_id' => 'Bookie ID',
            'type_id' => 'Type ID',
            'event_id' => 'Event ID',
            'member' => 'Member',
            'value' => 'Value',
            'sign' => 'Sign',
            'coef' => 'Coef',
            'initial_coef' => 'Initial Coef',
            'external_match_id' => 'External Match ID',
            'date' => 'Date',
            'update_date' => 'Update Date',
            'move_date' => 'Move Date',
        ];
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
    public function getBookie()
    {
        return $this->hasOne(Bookie::className(), ['id' => 'bookie_id']);
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
    public function getForecasts()
    {
        return $this->hasMany(Forecast::className(), ['bet_id' => 'id']
        )->where('bet_in_history=:bet_in_history',[':bet_in_history' => BettingConfig::BET_IN_HISTORY]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(BetType::className(), ['id' => 'type_id']);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->move_date = date("Y-m-d H:i:s");
            return true;
        }
        return false;
    }
}
