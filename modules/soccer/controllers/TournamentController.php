<?php

namespace app\modules\soccer\controllers;

use app\modules\soccer\models\Match;
use app\modules\soccer\repositories\MatchRepository;
use yii\web\Controller;

class TournamentController extends Controller
{

    public function actionIndex() {
        return $this->render('index');
    }

    /**
     * Страница с информацией о турнире
     * @see https://lionshot.myjetbrains.com/youtrack/issue/LIONSHOT-23
     *
     * @param $id - идентификатор турнира
     * @param array $filter - фильтр матчей
     * @return string
     */
    public function actionView($id, $filter = []) {


        //@todo проверить id турнира [27-04-2016 amorgunov]

        $matches = [
            'upcomingMatches'   => Match::getRepository()->getUpcomingMatchesByTournament($id),

            /** @see https://lionshot.myjetbrains.com/youtrack/issue/LIONSHOT-25 */
            'filterMatches'     => Match::getRepository()->getMatchesByFilter(array_merge($filter, ['tournamentId' => $id])),
            'lastMatches'       => Match::getRepository()->getMatchesByTournament($id),
        ];

        //$table = Match::getTable($matches['filterMatches']);

        return $this->render('view', [
            'matches'    => $matches,
            //'table'     => $table,
        ]);
    }
}
