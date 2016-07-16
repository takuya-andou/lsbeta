<?php

namespace app\modules\betting\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "soccer_model".
 *
 * @property integer $id
 * @property integer $type_id
 * @property integer $event_id
 * @property integer $usable
 * @property string $name
 *
 * @property BetEvent $event
 * @property BetType $type
 * @property ModelParam[] $modelParams
 */
class Model extends \yii\db\ActiveRecord
{
    const SCENARIO_INSERT = 'insert';
    const SCENARIO_UPDATE = 'update';
    const STATUS_UNAVAILABLE = 0;
    const STATUS_AVAILABLE = 1;
    const STATUS_ONTEST = 2;

    /*public static $usable_states = array(
        0 => 'Unsupported',
        1 => 'Ready to use',
        2 => 'On test'
    );*/


    public $params;

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_INSERT] = ['type_id', 'event_id', 'usable' , 'params', 'name'];
        $scenarios[self::SCENARIO_UPDATE] = ['type_id', 'event_id', 'usable' , 'params', 'name'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'soccer_model';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type_id', 'event_id','name', 'usable'], 'required'],
            ['params', 'checkRequired', 'skipOnEmpty' => false, 'skipOnError' => false],
            [['type_id', 'event_id', 'usable'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['event_id'], 'exist', 'skipOnError' => true, 'targetClass' => BetEvent::className(), 'targetAttribute' => ['event_id' => 'id']],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => BetType::className(), 'targetAttribute' => ['type_id' => 'id']],
        ];
    }

    public function checkRequired($attribute, $params)
    {
        $required_params = ArrayHelper::map(ModelParam::getRequired(), 'id', 'system_name');
        $required_param_ids = array_keys($required_params);
        //$this->addError('params[1]', print_r($attribute, true));
        if(count($required_params) <= $this->$attribute){
            foreach($this->$attribute as $key => $attr){
                if(!in_array($key, $required_param_ids) && empty($attr))
                    $this->addError($attribute, 'Specify all required params.');
            }
        }
        else
            $this->addError($attribute, 'Specify all required params.');

    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type_id' => 'Type ID',
            'event_id' => 'Event ID',
            'usable' => 'Usable',
            'name' => 'Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEvent()
    {
        return $this->hasOne(BetEvent::className(), ['id' => 'event_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(BetType::className(), ['id' => 'type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModelParams()
    {
        return $this->hasMany(ModelParamValue::className(), ['model_id' => 'id']);
    }

    public function afterSave($insert, $changedAttributes){
        parent::afterSave($insert, $changedAttributes);
        if($this->scenario==self::SCENARIO_INSERT){

        }
        else if($this->scenario == self::SCENARIO_UPDATE){
            $model_values = ModelParamValue::getModelValues($this->id);
            if(!empty($model_values))
                foreach($model_values as $model_value)
                    $model_value->delete();

        }
        foreach($this->params as $param_id => $param_val){
            $pv = new ModelParamValue();
            $pv->model_id = $this->id;
            $pv->param_id = $param_id;
            $pv->value = $param_val;
            $pv->save();
        }
    }

    public static function getModels($type_id = null, $event_id = null){
        if(!isset($type_id) && !isset($event_id)){
            return self::find()
                ->where('usable != :unsupported', [':unsupported' => 0])
                ->all();
        }
        else{
            if(isset($type_id) && !isset($event_id)){
                return self::find()
                    ->where('usable != :unsupported', [':unsupported' => 0])
                    ->andWhere('type_id=:type_id', [':type_id' => $type_id])
                    ->all();
            }
            else if(isset($type_id) && isset($event_id)){
                return self::find()
                    ->where('usable != :unsupported', [':unsupported' => 0])
                    ->andWhere('type_id=:type_id', [':type_id' => $type_id])
                    ->andWhere('event_id=:event_id', [':event_id' => $event_id])
                    ->all();
            }
        }
        return null;
    }

    public static function getStatusesArray()
    {
        return array(
            self::STATUS_UNAVAILABLE => 'Unsupported',
            self::STATUS_AVAILABLE => 'Ready to use',
            self::STATUS_ONTEST => 'On test'
        );
    }
}
