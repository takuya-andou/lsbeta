<?php

namespace app\modules\soccer\controllers;

use app\modules\soccer\models\Match;
use yii\web\Controller;
use yii\data\Pagination;

class MatchController extends Controller
{



    public function actionIndex($id=0)
    {
        //output list of match
        if ($id == 0) {
            $query = Match::find();
            $pagination = new Pagination([
                'defaultPageSize' => 10,
                'totalCount' => $query->count(),
            ]);
            $matches = $query->orderBy('id DESC')
                ->joinWith([
                    'home' => function ($query) {
                        $query->joinWith('country');
                    },
                    'competition',
                    'referee'
                ])
                ->offset($pagination->offset)
                ->limit($pagination->limit)
                ->all();

            return $this->render('index', [
                'matches' => $matches,
                'pagination' => $pagination,
            ]);
        } else {
            return $this->render('index', [
                'match' => Match::getMatch($id),
            ]);
        }
    }


    public function actionMatch($id)
    {
        return $this->render('match', [
            'result' => Match::getMatch($id),
        ]);
    }

}
