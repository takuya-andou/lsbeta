<?php

namespace app\modules\soccer\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "{{%team}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $country_id
 * @property integer $fapi_id
 *
 * @property Match[] $matches
 * @property Match[] $matches0
 * @property Country $country
 * @property TeamSynonym[] $teamSynonyms
 */
class Team extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%team}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['country_id', 'fapi_id'], 'integer'],
            [['name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'country_id' => 'Country ID',
            'fapi_id' => 'Api ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMatches()
    {
        return $this->hasMany(Match::className(), ['home_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMatches0()
    {
        return $this->hasMany(Match::className(), ['away_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
    }
    public function getTeamSynonyms()
    {
        return $this->hasMany(TeamSynonym::className(), ['team_id' => 'id']);
    }

    public static function getTeam($id) {
        if ($id==0) {
            return false;
        }

        return self::find()
            ->joinWith([
                'country'
            ])
            ->where([self::tableName().'.id' => $id])
            ->one();
    }

    public static function getMatchesByTeamId($id) {
        return Match::getMatchOneTeam($id);
    }
    public static function getTeamsByPattern($pattern){
        return self::find()
            ->filterWhere(['like', 'name', $pattern])
            ->all();
    }
}
