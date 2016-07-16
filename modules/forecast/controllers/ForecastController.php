<?php

namespace app\modules\forecast\controllers;

use app\modules\betting\models\Bet;
use app\modules\betting\models\BetEvent;
use app\modules\betting\models\BetType;
use app\modules\forecast\config\ForecastConfig;
use app\modules\main\models\Like;
use app\modules\soccer\models\Match;
use Yii;
use app\modules\forecast\models\Forecast;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ForecastController implements the CRUD actions for Forecast model.
 */
class ForecastController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Forecast models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Forecast::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Forecast model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $rate = Like::getItemLikeRate(Like::FORECAST, $id);

        if(!\Yii::$app->user->can('ViewForecast',['item' => $model] )){
            throw new ForbiddenHttpException("You are not allowed to perform this action.");
        }

        if(!\Yii::$app->request->isAjax){
            //$model->scenario = Forecast::SCENARIO_VIEW;
            $model->addViews();
        }
        return $this->render('view', [
            'model' => $model,
            'rate' => $rate
        ]);
    }

    /**
     * Creates a new Forecast model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Forecast();
        $until_date = new \DateTime(date('Y-m-d H:i:s'));
        $until_date->add(new \DateInterval('P'.ForecastConfig::UPCOMING_MATCHES_RANGE.'D'));
        $current_date = date('Y-m-d H:i:s');
        $upcoming_matches = Match::getMatchesByDateRangeWithTeams($current_date, $until_date->format('Y-m-d H:i:s'));

        $model->scenario = Forecast::SCENARIO_CREATE;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
                'upcoming_matches' => $upcoming_matches
            ]);
        }
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $until_date = new \DateTime(date('Y-m-d H:i:s'));
        $until_date->add(new \DateInterval('P'.ForecastConfig::UPCOMING_MATCHES_RANGE.'D'));
        $current_date = date('Y-m-d H:i:s');
        $upcoming_matches = Match::getMatchesByDateRangeWithTeams($current_date, $until_date->format('Y-m-d H:i:s'));

        $model->scenario = Forecast::SCENARIO_UPDATE;
        if(!\Yii::$app->user->can('UpdateItem',['item' => $model] )){
            throw new ForbiddenHttpException("You are not allowed to perform this action.");
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
                'upcoming_matches' => $upcoming_matches
            ]);
        }
    }

    /**
     * Deletes an existing Forecast model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if(!\Yii::$app->user->can('UpdateItem',['item' => $model] )){
            throw new ForbiddenHttpException("You are not allowed to perform this action.");
        }
        $model->delete();
        return $this->redirect(['index']);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * action to like forecast
     */
    public function actionLike($id){
        $model = $this->findModel($id);
        $rate = \Yii::$app->runAction('/main/like/like',
            ['module_id' => Like::FORECAST, 'item_id' => $id]);
        return $this->render('view', ['model' => $model,'rate' => $rate]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * action to dislike forecast
     */
    public function actionDislike($id){
        $model = $this->findModel($id);
        $rate = \Yii::$app->runAction('/main/like/dislike',
            ['module_id' => Like::FORECAST, 'item_id' => $id]);
        return $this->render('view', ['model' => $model, 'rate' => $rate]);
    }

    public function actionTest(){
        Forecast::updateResults();
    }

    /**
     * Finds the Forecast model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Forecast the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Forecast::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
