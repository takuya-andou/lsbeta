<?php

namespace app\modules\soccer\models;

use Yii;

/**
 * This is the model class for table "{{%country}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $image
 * @property integer $fapi_id
 *
 * @property Competition[] $competitions
 * @property Referee[] $referees
 * @property Stadium[] $stadia
 * @property Team[] $teams
 */
class Country extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%country}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['fapi_id'], 'integer'],
            [['name', 'image'], 'string', 'max' => 255]
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
            'image' => 'Image',
            'fapi_id' => 'Fapi ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompetitions()
    {
        return $this->hasMany(Competition::className(), ['country_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReferees()
    {
        return $this->hasMany(Referee::className(), ['country_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStadia()
    {
        return $this->hasMany(Stadium::className(), ['country_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTeams()
    {
        return $this->hasMany(Team::className(), ['country_id' => 'id']);
    }

    public static function addCountryIfNotExists($name) {
        if (!$model = self::findOne(['name' => $name])) {
            $model =  new Country;
            $model->name = $name;
            $model->image = strtolower($name).'.png';
            if (!$model->save()) {
                $err = json_encode($model->getErrors());
                return false;
            }
        }
        return $model->id;
    }
}
