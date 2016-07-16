<?php

namespace app\modules\betting\models;

use Yii;

/**
 * This is the model class for table "{{%bookie}}".
 *
 * @property integer $id
 * @property string $date
 * @property string $json_string
 */
class Line extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%line}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['json_string'], 'required'],
            //['json_string', 'unique'],
            [['date'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Date',
            'json_string' => 'Json String',
        ];
    }
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            date_default_timezone_set('UTC');
            $this->date =  date('Y-m-d H:i:s');
            return true;
        }

        return false;
    }

    public static function retrieveRecords($limit){
       return  self::find()
            ->orderBy([
           'date' => SORT_ASC
            ])
           ->limit($limit)
            ->all();
    }
}
