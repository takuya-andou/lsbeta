<?php

namespace app\modules\main\models;

use Yii;
use app\modules\forecast\models\Forecast;
use app\modules\user\models\User;

/**
 * This is the model class for table "{{%like}}".
 *
 * @property integer $id
 * @property integer $module_id
 * @property integer $item_id
 * @property integer $user_id
 * @property string $ip
 * @property integer $value
 *
 * @property User $user
 */
class Like extends \yii\db\ActiveRecord
{
    /**
     * like/dislike values
     */
    const LIKE = 1;
    const DISLIKE = -1;
    /**
     * project modules' ids
     */
    const MAIN = 1;
    const SOCCER = 2;
    const FORECAST = 3;
    const BETTING = 4;
    const ADMIN = 5;
    /**
     * action results
     */
    const ITEM_LIKED = 1;
    const ITEM_DISLIKED = -1;
    const ITEM_UNCHANGED = 0;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%like}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['module_id', 'item_id', 'ip', 'value'], 'required'],
            [['module_id', 'item_id', 'user_id', 'value'], 'integer'],
            [['ip'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'module_id' => 'Module ID',
            'item_id' => 'Item ID',
            'user_id' => 'User ID',
            'ip' => 'Ip',
            'value' => 'Value',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @param $module_id
     * @param $item_id
     * @return array|null|\yii\db\ActiveRecord
     * function to get likes of certain item
     */
    /*public static function getItemLikes($module_id, $item_id){
        return self::find()
            ->where('module_id=:module_id and item_id=:item_id',
                [':module_id' => $module_id, ':item_id' => $item_id])
            ->groupBy('')
            ->all();
    }*/

    /**
     * @param $module_id
     * @param $item_id
     * @return array|bool
     * function to get like rate of certain item
     */
    public static function getItemLikeRate($module_id, $item_id){
        $query = new \yii\db\Query();
        $res = $query->select('SUM(value) as like_rate')
        ->from(self::tableName())
        ->where('module_id=:module_id and item_id=:item_id',
            [':module_id' => $module_id, ':item_id' => $item_id])
        ->one();
        return $res['like_rate'];
    }

    /**
     * @param $module_id
     * @param $item_id
     * @param $user_id
     * @param $ip
     * @return array|bool|null
     */
    private static function ifUserLiked($module_id, $item_id, $user_id, $ip){
        if(empty($user_id) && empty($ip)) return null;
        $query = self::find()
            ->where('module_id=:module_id and item_id=:item_id',
                [':module_id' => $module_id, ':item_id' => $item_id]);
        if(!empty($user_id)) $query->andWhere('user_id=:user_id', [':user_id' => $user_id]);
        else $query->andWhere('ip=:ip', [':ip' => $ip]);
        return $query->one();

        /*$query = new \yii\db\Query();
        $query->from(self::tableName())
            ->where('module_id=:module_id and item_id=:item_id',
                [':module_id' => $module_id, ':item_id' => $item_id]);
        if(!empty($user_id)) $query->andWhere('user_id=:user_id', [':user_id' => $user_id]);
        else $query->andWhere('ip=:ip', [':ip' => $ip]);
        return $query->one();*/
    }

    public static function rateItem($module_id, $item_id, $user_id, $ip, $value){
        //проверка, что лайкаемый итем существует
        //TODO:мб не нужно?? потому что они просто будут болтаться в таблице, никому не мешая
        /*$modules = self::getModulesArray();
        if(!in_array($module_id, array_keys($modules))) return null;
        $item_class = $modules[$module_id];
        if(!$item_class::find()->where('id=:id',[':id' => $item_id])->one()) return null;*/

        //все существует, можно лайкать
        $rate = self::ifUserLiked($module_id, $item_id, $user_id, $ip);
        if(!empty($rate) && $rate !== false){
            if($rate->value != $value)
            {
                $rate->value=$value;
                $rate->update();
            }
            else{
                $rate->delete();
            }
        }
        else{
            $rate = new Like;
            $rate->user_id = $user_id;
            $rate->ip = $ip;
            $rate->module_id = $module_id;
            $rate->item_id = $item_id;
            $rate->value = $value;
            $rate->save();
        }
        return self::getItemLikeRate($module_id, $item_id);
    }

    public static function getModulesArray()
    {
        return array(
            self::MAIN => 'Main module',
            self::SOCCER => 'Soccer module',
            self::FORECAST => 'Forecast module',
            self::BETTING => 'Betting module',
            self::ADMIN => 'Admin module'
        );
    }

    public static function getActionResultsArray()
    {
        return array(
            self::ITEM_LIKED => 'Item\'s like rate has been increased',
            self::ITEM_DISLIKED => 'Item\'s like rate has been decreased',
            self::ITEM_UNCHANGED => 'Item\'s like rate has not been changed',
        );
    }

    /**
     * This function has to be called periodically to update result fields in forecasts
     * according to match stats inside stats table
     */
    public static function updateResults(){

    }
}
