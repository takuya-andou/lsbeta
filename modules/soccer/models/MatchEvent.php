<?php

namespace app\modules\soccer\models;

use Yii;

/**
 * This is the model class for table "{{%match_event}}".
 *
 * @property integer $id
 * @property integer $match_id
 * @property integer $type
 * @property integer $minute
 * @property integer $team
 * @property string $result
 *
 * @property Match $match
 */
class MatchEvent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%match_event}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['match_id', 'type', 'minute', 'team'], 'required'],
            [['match_id', 'type', 'minute', 'team'], 'integer'],
            [['result'], 'string', 'max' => 255]
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
            'type' => 'Type',
            'minute' => 'Minute',
            'team' => 'Team',
            'result' => 'Result',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMatch()
    {
        return $this->hasOne(Match::className(), ['id' => 'match_id']);
    }
}
