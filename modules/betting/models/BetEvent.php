<?php

namespace app\modules\betting\models;

use app\modules\soccer\models\MatchStats;
use Yii;

/**
 * This is the model class for table "{{%bet_event}}".
 *
 * @property integer $id
 * @property string $name
 *
 * @property Bet[] $bets
 */
class BetEvent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bet_event}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
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
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBets()
    {
        return $this->hasMany(Bet::className(), ['event_id' => 'id']);
    }

    public static function findBySystemName($str){
        $db = self::getDb();
        $object = $db->cache(function ($db) use($str) {
            return
                BetEvent::find()
                    ->where('system_name = :name',
                        [':name' => $str])
                    ->one();
        });
        return $object;

    }

}
