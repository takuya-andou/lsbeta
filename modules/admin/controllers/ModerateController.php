<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 14.07.2016
 * Time: 17:09
 */

namespace app\modules\admin\controllers;

use app\components\models\linear_v1\LinearModelController;
use app\modules\betting\models\BetEvent;
use app\modules\betting\models\BetType;
use app\modules\betting\models\Bookie;
use app\modules\betting\models\ModelParam;
use app\modules\betting\models\ModelParamValue;
use app\modules\soccer\models\Match;
use app\modules\soccer\repositories\MatchRepository;
use Yii;
use app\modules\betting\models\Model;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class ModerateController extends Controller
{
    /**
     * @inheritdoc
     */
    /*public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }*/

    /**
     * Lists all Model models.
     * @return mixed
     */
    public function actionForecast()
    {

    }

}