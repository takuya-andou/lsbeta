<?php

namespace app\modules\soccer\models;

use Yii;

/**
 * This is the model class for table "soccer_team_synonym".
 *
 * @property integer $id
 * @property integer $team_id
 * @property string $name
 *
 * @property Team $team
 */
class TeamSynonym extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'soccer_team_synonym';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['team_id', 'name'], 'required'],
            [['team_id'], 'integer'],
            [['name'], 'string', 'max' => 256],
            [['name'], 'string', 'min' => 2],
            [['team_id'], 'exist', 'skipOnError' => true, 'targetClass' => Team::className(), 'targetAttribute' => ['team_id' => 'id']],
            ['name', 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'team_id' => 'Team ID',
            'name' => 'Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTeam()
    {
        return $this->hasOne(Team::className(), ['id' => 'team_id']);
    }
}
