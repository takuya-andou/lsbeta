<?php

namespace app\modules\betting\models;

use Yii;

/**
 * This is the model class for table "{{%bookie}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $site
 * @property integer $status
 * @property string $code
 *
 * @property Bet[] $bets
 */
class Bookie extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bookie}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['status'], 'integer'],
            [['name', 'site', 'code'], 'string', 'max' => 255],
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
            'site' => 'Site',
            'status' => 'Status',
            'code' => 'Code',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBets()
    {
        return $this->hasMany(Bet::className(), ['bookie_id' => 'id']);
    }
}
