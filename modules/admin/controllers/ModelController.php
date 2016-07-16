<?php

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

/**
 * ModelController implements the CRUD actions for Model model.
 */
class ModelController extends Controller
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
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Model::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Model model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $dataProvider = new ArrayDataProvider([
            'allModels' => $model->modelParams,
        ]);
        return $this->render('view', [
            'model' => $model,
            'dataProvider' => $dataProvider,

        ]);
    }

    /**
     * Creates a new Model model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Model();
        $types = BetType::find()->all();
        $events = BetEvent::find()->all();
        $params = ModelParam::find()->all();

        $model->scenario = Model::SCENARIO_INSERT;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
                'types' => $types,
                'events' => $events,
                'params' => $params,
                'usable_states' => Model::getStatusesArray()
            ]);
        }
    }

    /**
     * Updates an existing Model model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $types = BetType::find()->all();
        $events = BetEvent::find()->all();
        $params = ModelParam::find()->all();

        $model->scenario = Model::SCENARIO_UPDATE;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
                'types' => $types,
                'events' => $events,
                'params' => $params,
                'usable_states' => Model::getStatusesArray()
            ]);
        }
    }

    /**
     * Deletes an existing Model model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Model model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Model the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Model::findOne($id)) !== null) {
            if(!empty($m_params = $model->modelParams))
            foreach($m_params as $param)
                $model->params[$param->param_id] = $param->value;
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionCalculate(){


        return $this->render('calculate', [
        ]);
    }

    public function actionTest(){
        $models = Model::find()->all();
        $bookies = Bookie::find()->all();
        /*$model = $this->findModel($id);
        $model_param_vals = $model->modelParams;
        $model_props = [];
        foreach($model_param_vals as $param_val){
            $model_props[$param_val->param->name] = $param_val->value;
        }
        $data = [
            'model_props' => $model_props,
            'bet_type' => $model->type_id,
            'bet_event' => $model->event_id,
            /*'lower_initial_coef' => 1.7,
            'upper_initial_coef' => 2.3,*/
            /*'bookie_required_keys' => [16],
            'bet_size' => 3,
            'initial_bank_size' => 100,*/
            /*'matches_since_date' => '2016-04-01 00:00:00',
            'matches_until_date' => '2016-05-06 00:00:00'*/
        /*];
        //$model
        $results = LinearModelController::getInstance()->testV2($data);

        /*echo '<pre>';
        print_r($results);
        echo '</pre>';*/

        return $this->render('test', [
            'models' => $models,
            'bookies' => $bookies
        ]);
    }

    public function actionRuntest(){
        $model_id = Yii::$app->request->get('model_id');
        $bookie_ids = Yii::$app->request->get('bookie_ids');
        $bet_sizing = Yii::$app->request->get('bet_sizing');
        $matches_since_date = Yii::$app->request->get('matches_since_date');
        $matches_until_date = Yii::$app->request->get('matches_until_date');
        $lower_coef = Yii::$app->request->get('lower_coef');
        $upper_coef = Yii::$app->request->get('upper_coef');
        $matches_num = Yii::$app->request->get('matches_num');
        $bets_num = Yii::$app->request->get('bets_num');

        $model = $this->findModel($model_id);

        $model_param_vals = $model->modelParams;
        $model_props = [];
        foreach($model_param_vals as $param_val){
            $model_props[$param_val->param->name] = $param_val->value;
        }

        $data = [
            'model_props' => $model_props,
            'type_id' => $model->type_id,
            'event_id' => $model->event_id,
            'bookie_ids' => json_decode($bookie_ids),
            'bet_size' => $bet_sizing,
            'initial_bank_size' => 100,
            'since_date' => $matches_since_date,
            'until_date' => $matches_until_date,
            'matches_num' => $matches_num,
            'bets_num' => $bets_num,
            'lower_coef' => $lower_coef,
            'upper_coef' => $upper_coef,
            ];

        $results = LinearModelController::getInstance()->testV2($data);

        echo json_encode($results);
            //echo $model_id.' '.$bookie_ids.' '.$bet_sizing.' '.$matches_since_date.' '.$matches_until_date;
        }
        public function actionAjaxgetparamsandmodels(){
            $request = Yii::$app->request;
            $type_id = json_decode($request->get('type_id'));
            $event_id = json_decode($request->get('event_id'));
            $result = [];
            $result['status'] = 'success';
            if(!empty($type_id) && !empty($event_id)){

                $result['params'] = [];
                switch($type_id){
                    case BettingConfig::$TYPE_ID['total']:
                        $result['params_count'] = 1;
                        $result['params'][] = [
                        'type' => 'text',
                        'name' => 'total_value',
                        'id' => 'total_value'
                        ];
                        break;
                    default:
                        $result['status'] = 'error';
                        $result['messages'][] = 'Unsupported bet type.';
                        break;
                }
                if($result['status'] != 'error'){
                    $result['status'] = 'success';
                    $result['models_count'] = 0;
                    $models = Model::getModels($type_id, $event_id);
                    if(!empty($models)){
                        foreach($models as $model){
                            $result['models'][$model->id] =
                                [
                                    'name' => (empty($model->name)) ? 'No model name' : $model->name,
                                    'status' => Model::$usable_states[$model->usable]
                                ];
                            $result['models_count']++;
                        }
                    }
                }
            }
            else{
                $result['status'] = 'error';
                $result['messages'][] = 'Not all of required params specified.';
            }
            echo json_encode($result);
            //return
        }
        public function actionAjaxcalculate(){
            $request = Yii::$app->request;
            $model_id = $request->get('model_id');
            $match_ids = json_decode($request->get('match_ids'));
            $params = json_decode($request->get('params'));

            /*echo json_encode([
                'model_id' => $model_id,
                'match_ids' => $match_ids,
                'params' => $params
            ]);*/
        $result = [];
        $model = Model::find()
            ->where('id=:id', [':id' => $model_id])
            ->one();
        $result['status'] = 'success';
        if(!empty($match_ids)){
            if(!empty($model)){
                $params_parsed = false;
                //обработать полученные параметры, чтобы найти похожие ставки
                switch($model->type_id){
                    case BettingConfig::$TYPE_ID['total']:
                        $pattern = '/\s*(<|>)\s*(\d+[\.,]?\d*)/';
                        if(preg_match_all($pattern, $params[0], $matches)){

                        }
                        else{

                        }
                        break;
                    default:
                    //$result['status'] = 'error';
                    $result['bookies_coefs_status'] = 'Unsupported bet type.';
                    break;
                }
                //если значения пользователя подходят для парсинга
                if($result['status'] == 'success'){
                    foreach($match_ids as $match_id){
                        $results['results'][$match_id] = LinearModelController::getInstance()->test($match_id);
                    }
                }
            }
            else{
                $result['status'] = 'error';
                $result['messages'][] = 'No model found.';
            }
        }
        else{
            $result['status'] = 'error';
            $result['messages'][] = 'No matches specified.';
        }


        echo json_encode($result);

    }
}
