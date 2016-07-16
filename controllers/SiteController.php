<?php

namespace app\controllers;

use app\extensions\geneticalgorithm\GACore;
use app\extensions\parsing\cornerstats\CornerstatsParserExtended;
use app\extensions\geneticalgorithm;;
use app\extensions\parsing\marathonbet\MarathonbetFootballParser;
use app\modules\betting\models\Bet;
use app\modules\soccer\models\MatchStats;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\modules\user\models\LoginForm;
use app\modules\main\models\ContactForm;

set_time_limit(0);

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
    {
        echo '111';



        /*$linepusher = new \app\extensions\parsing\marathonbet\MarathonbetLinePusher();
        $linepusher->run();*/
       /* $r = MatchStats::getMatchStat(56000, 1);
        print_r($r);*/


        /*$match = Match::find()
            ->where('id=:id', [':id' => 50474])
            ->one();
        $parser = new CornerstatsParserExtended();
        $res = $parser->parseMatch($match, $a,$b);
        echo '<pre>';
        print_r($res);
        echo '</pre>';*/
       /* $pp = new \app\extensions\parsing\marathonbet\MarathonbetLinePusher();
        $pp->run();*/
        /*$pp = new \app\extensions\parsing\cornerstats\CornerstatsLinePusher();
        $pp->run();*/
        /*$pp = new \app\extensions\parsing\footballdata\FootballdataLinePusher();
        $pp->run();*/
        /*$ps = new \app\extensions\parsing\LineHandler();
        $ps->run();*/
        //echo \Yii::getAlias('@logs');
        /*$fcp = new \app\extensions\parsing\footballdata\FootballdataCSVParser();
        $r=$fcp->run();*/
        /*$fp = new \app\extensions\parsing\footballdata\FootballdataHTMLParser();
        $fp->run();*/
       /* $pattern = '/Sanderland\s*(\([-,+]?\d+[\.,]?\d*\)|$)/';
        $str = 'Sanderland';
        echo preg_match($pattern, $str, $match);
        print_r($match);*/
        /*$pp = new \app\extensions\parsing\marathonbet\MarathonbetFootballParser();
        $pp->run();*/
        /*$cc = new \app\extensions\parsing\cornerstats\CornerstatsParserExtended();
        $cc->run();*/
        /*$cs_e = new \app\components\parser\cornerstatsextended\Parser();
        $cs_e->execute();*/
    }

    function clean_all(&$items,$leave = ''){
        foreach($items as $id => $item){
            if($leave && ((!is_array($leave) && $id == $leave) || (is_array($leave) && in_array($id,$leave)))) continue;
            if($id != 'GLOBALS'){
                if(is_object($item) && ((get_class($item) == 'simple_html_dom') || (get_class($item) == 'simple_html_dom_node'))){
                    $items[$id]->clear();
                    unset($items[$id]);
                }else if(is_array($item)){
                    $first = array_shift($item);
                    if(is_object($first) && ((get_class($first) == 'simple_html_dom') || (get_class($first) == 'simple_html_dom_node'))){
                        unset($items[$id]);
                    }
                    unset($first);
                }
            }
        }
    }

    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    public function actionAbout()
    {
        return $this->render('about');
    }
}
