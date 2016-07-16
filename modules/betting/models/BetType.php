<?php

namespace app\modules\betting\models;

use Yii;

/**
 * This is the model class for table "{{%bet_type}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 *
 * @property Bet[] $bets
 */
class BetType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bet_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
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
            'description' => 'Description',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBets()
    {
        return $this->hasMany(Bet::className(), ['type_id' => 'id']);
    }
    public static function findBySystemName($str){
        $db = self::getDb();
        $object = $db->cache(function ($db) use($str) {
            return
                BetType::find()
                    ->where('system_name = :name',
                        [':name' => $str])
                    ->one();
        });
        return $object;

    }
}
