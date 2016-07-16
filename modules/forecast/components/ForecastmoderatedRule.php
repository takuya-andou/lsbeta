<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 14.07.2016
 * Time: 17:11
 */

namespace app\modules\forecast\components;

use Yii;
use yii\rbac\Rule;

use app\modules\forecast\config\ForecastConfig;

class ForecastmoderatedRule extends Rule
{
    public $name = 'isModerated';

    public function execute($user, $item, $params)
    {
        return isset($params['item']) ?
            (
                $params['item']->status == ForecastConfig::STATUS_APPROVED ||
                $params['item']->status == ForecastConfig::STATUS_NOT_MODERATED_BUT_PUBLISHED ||
                $params['item']->user_id == $user
            )  : false;
    }
}