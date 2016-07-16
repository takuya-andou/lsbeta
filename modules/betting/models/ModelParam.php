<?php

namespace app\modules\betting\models;

use Yii;

/**
 * This is the model class for table "soccer_model_param".
 *
 * @property integer $id
 * @property string $system_name
 * @property string $name
 * @property integer $required
 * @property string $default_value
 *
 * @property ModelParamValue[] $modelParamValues
 */
class ModelParam extends \yii\db\ActiveRecord
{
    const PARAM_NOT_REQUIRED = 0;
    const PARAM_REQUIRED = 1;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%model_param}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['system_name', 'name', 'required'], 'required'],
            [['required'], 'integer'],
            [['system_name', 'name', 'default_value'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'system_name' => 'System Name',
            'name' => 'Name',
            'required' => 'Required',
            'default_value' => 'Default Value',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModelParamValues()
    {
        return $this->hasMany(ModelParamValue::className(), ['param_id' => 'id']);
    }

    public static function getRequiredStatusesArray()
    {
        return [
            self::PARAM_NOT_REQUIRED => 'Не обязателен',
            self::PARAM_REQUIRED => 'Обязателен',
        ];
    }
    public static function getRequired(){
        return self::find()
            ->where('required != :required', [':required' => self::PARAM_REQUIRED])
            ->all();
    }
}
