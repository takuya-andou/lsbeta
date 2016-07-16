<?php
namespace app\components\widgets\model;
use app\modules\betting\models\BetEvent;
use app\modules\betting\models\BetType;
use app\modules\soccer\repositories\MatchRepository;
use Yii;
use app\components\helpers\Html;

class ModelCalculator extends \yii\bootstrap\Widget
{
    protected $matches;
    protected $types;
    protected $events;

    public function init() {
        parent::init();
        //подготовка выборки матчей
        if (!isset(Yii::$app->request->cookies['model.matches_from'])) {
            $matches_from = date('Y-m-d H:i:s');
            $matches_from = '2016-05-07 13:00:00';
            Yii::$app->response->cookies->add(new \yii\web\Cookie([
                'name' => 'model.matches_from',
                'value' => $matches_from,
            ]));
        }
        else{
            $matches_from = Yii::$app->request->cookies['model.matches_from'];
        }

        if (!isset(Yii::$app->request->cookies['model.matches_until'])) {
            $matches_until = date('Y-m-d H:i:s', strtotime($matches_from . ' +12 hour'));
            $matches_until = '2016-05-07 15:00:00';
            Yii::$app->response->cookies->add(new \yii\web\Cookie([
                'name' => 'model.matches_until',
                'value' => $matches_until,
            ]));
        }
        else{
            $matches_until = Yii::$app->request->cookies['model.matches_until'];
        }
        $rep = new MatchRepository();
        $this->matches = $rep->getMatchesByDate($matches_from, $matches_until);

        //выборка типов и событий
        $this->types = BetType::find()->all();
        $this->events = BetEvent::find()->all();
}

    /**
     * @return string
     */
    public function run() {

        return $this->render('modelcalculator',[
            'matches' => $this->matches,
            'events' => $this->events,
            'types' => $this->types
        ]);
    }


}