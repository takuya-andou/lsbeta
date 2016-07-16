<?php
namespace app\modules\soccer\repositories;

use app\modules\soccer\models\Match;
use app\repositories\Repository;
use Yii;
use yii\db\Query;

class MatchRepository extends Repository
{

    /**
     * @param int $id
     * @return \yii\db\ActiveRecord|array|null
     * @throws \Exception
     */
    public function findById($id){
        return Match::getDb()->cache(function ($db) use ($id) {
            return Match::find()
                ->where(['id' => (int) $id])
                ->one();
        });
    }

    /**
     * @param int $tournamentId
     * @return \yii\db\ActiveRecord[]|array
     * @throws \Exception
     */
    public function getUpcomingMatchesByTournament($tournamentId) {

        return Match::getDb()->cache(function ($db) use ($tournamentId) {

            $currentDate = date("Y-m-d H:i:s");

            return Match::find()
                ->where(['competition_id' => (int) $tournamentId])
                ->andWhere(['status' => Match::STATUS_NOTSTARTED])
                ->andWhere(['>', 'date', $currentDate])
                ->all();
        });
    }

    /**
     * @param int $tournamentId
     * @return \yii\db\ActiveRecord[]|array
     * @throws \Exception
     */
    public function getMatchesByTournament($tournamentId) {
        return Match::getDb()->cache(function ($db) use ($tournamentId) {
            return Match::find()
                ->where(['competition_id' => (int) $tournamentId])
                ->all();
        });
    }


    /**
     * @param string $start
     * @param bool|string $end
     * @return \yii\db\ActiveRecord[]|array
     * @throws \Exception
     */
    public function getMatchesByDate($start, $end = false) {
        return Match::getDb()->cache(function ($db) use ($start, $end) {
            $result = Match::find()->where(['>', 'date', $start]);

            if ($end !== false) {
                $result->andWhere(['<', 'date', $end]);
            }

            return $result->all();
        });
    }

    /**
     * Используется в линейной модели. Формирует массив матчей с суммой указанного события (угловые, карты итд)
     *
     * @param int $homeId
     * @param bool|int $awayId
     * @param int $eventId
     * @param bool|int $competitionId
     * @param int|array $status Если нужно получить несыгранные игры тоже, то нужно передать массив со всеми статусами
     * @return array
     */
    public function getMatchesByStatsAndTeams($homeId, $awayId = false, $eventId = 1, $competitionId = false, $status = Match::STATUS_FINISHED){

        $teams = [$homeId];

        if ($awayId !== false) {
            $teams = array_merge($teams, [$awayId]);
        }

        $query = new Query;
        $query->select(['sm.id', 'date', 'home_id', 'away_id', 'competition_id', 'SUM(value) as event_value'])
            ->from('soccer_match sm')
            ->leftJoin('soccer_match_stats sms', 'sm.id = sms.match_id ')
            ->where(['fkey' => (int) $eventId, 'status' => $status])
            ->andFilterWhere(['or',
                ['home_id' => $teams],
                ['away_id' => $teams]
            ])
            ->orderBy('sm.date desc')
            ->groupBy('sm.id');

        if ($competitionId !== false) {
            $query->andWhere(['competition_id' => (int) $competitionId]);
        }

        $command = $query->createCommand();
        return $command->queryAll();

        //var_dump($command->getRawSql());
        //exit();
    }

    /**
     * @param int $teamId
     * @param bool $competitionId
     * @param bool $status
     * @return \yii\db\ActiveRecord[]|array
     * @throws \Exception
     */
    public function getMatchesByTeam($teamId, $competitionId = false, $status = false){
        return Match::getDb()->cache(function ($db) use ($teamId, $competitionId, $status) {
            $result = Match::find()
                ->where(['status' => $status])
                ->andFilterWhere(['or',
                    ['home_id' => [$teamId]],
                    ['away_id' => [$teamId]]])
                ->orderBy(['date' => SORT_DESC]);

            if ($competitionId !==false) {
                $result->andWhere(['competition_id' => (int) $competitionId]);
            }

            if ($status !== false) {
                $result->andWhere(['status' => (int) $status]);
            }

            return $result->all();
        });

    }

    /**
     * @param int $refereeId
     * @param string $dateStart
     * @return \yii\db\ActiveRecord[]|array
     * @throws \Exception
     */
    public function getMatchesByReferee($refereeId , $dateStart = '2000-01-01 00:00:00') {
        return Match::getDb()->cache(function ($db) use ($refereeId, $dateStart) {
            return Match::find()
                ->joinWith([
                    'home' => function ($query) {
                        $query->joinWith('country');
                    },
                    'referee',
                    'stadium',
                ])
                ->where([self::tableName().'.referee_id' => $refereeId])
                ->andWhere(['>', self::tableName().'.date', $dateStart])
                ->all();
        });
    }



    public function getMatchesByFilter($filter) {
        //todo implement [28-04-2016 amorgunov]
        return [];
    }

    public function getTable($tournamentId, $filter = []) {
        //todo implement [28-04-2016 amorgunov]
        return [];
    }


}