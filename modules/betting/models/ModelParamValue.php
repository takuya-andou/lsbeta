<?php

namespace app\modules\betting\models;

use Yii;

/**
 * This is the model class for table "{{%model_param_value}}".
 *
 * @property integer $id
 * @property integer $model_id
 * @property integer $param_id
 * @property string $value
 *
 * @property ModelParam $param
 * @property Model $model
 */
class ModelParamValue extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%model_param_value}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model_id', 'param_id', 'value'], 'required'],
            [['model_id', 'param_id'], 'integer'],
            [['value'], 'string', 'max' => 255],
            [['param_id'], 'exist', 'skipOnError' => true, 'targetClass' => ModelParam::className(), 'targetAttribute' => ['param_id' => 'id']],
            [['model_id'], 'exist', 'skipOnError' => true, 'targetClass' => Model::className(), 'targetAttribute' => ['model_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'model_id' => 'Model ID',
            'param_id' => 'Param ID',
            'value' => 'Value',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParam()
    {
        return $this->hasOne(ModelParam::className(), ['id' => 'param_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModel()
    {
        return $this->hasOne(Model::className(), ['id' => 'model_id']);
    }

    public static function getModelValues($model_id){
        return self::find()
            ->where('model_id=:model_id',
                [':model_id' => $model_id])
            ->all();
    }

    public static function getValue($model_id, $param_id){
        return self::find()
            ->where('model_id=:model_id and param_id=:param_id',
                [':model_id' => $model_id, ':param_id' => $param_id])
            ->one();
    }
}
