<?php
namespace app\commands;

use app\components\parser\marathonbet\Parser;

use Yii;
use app\modules\soccer\models;
use yii\base\Model;
use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\Console;
use app\components\parser\cornersstats\ParserController as ParserShell;

class ParserController extends Controller
{

    const PATH_CRON_CORNERSTATS_PARSER_MATCHES = 'cron/cornerstats_parser.log';

    /**
     * @var int $cacheTime time in seconds to store cache
     */
    private $cacheTime = 143200;

    /**
     * @var int $retryCount
     */
    private $retryCount = 3;

    public function actionMarathonbet() {
        $parser = new Parser();
        $parser->execute();
    }


    /**
     * Function return last external match's id
     * @execute >php yii parser/lastexternal
     */
    public function actionLastexternal() {
        $id = ParserShell::getInstance()->getLastExternalId();
        \Yii::$app->cache->set(ParserShell::getInstance()->getParser()->getKey(), $id, $this->cacheTime);
        print $id;
    }

    /**
     * Function update matches with status NOT_STARTED
     * @execute >php yii parser/updatenotstarted
     */
    public function actionUpdatenotstarted(){
        $res = ParserShell::getInstance()->getParser()->updateLastedMatch();

        $msg = '{' . __FUNCTION__ . '} END PARSING ' . $res['count'] . ' MATCHES FROM [' . $res['start'] . ' ... ' . $res['end'] . ']';
        ParserShell::getInstance()->getParser()->log(0, $msg, self::PATH_CRON_CORNERSTATS_PARSER_MATCHES);
    }

    /**
     * Function update matches with status NOT_STARTED
     * @execute >php yii parser/run
     */
    public function actionRun(){
        $lastId = ParserShell::getInstance()->getLastExternalId();
        $newLastId = $this->actionUpdate($lastId);

        $msg = '{' . __FUNCTION__ . '} END PARSING NEW PORTION FROM [' . $lastId . ' ... ' . $newLastId . ']';
        ParserShell::getInstance()->getParser()->log(0, $msg, self::PATH_CRON_CORNERSTATS_PARSER_MATCHES);
    }

    /**
     * @execute >php yii parser/update
     *
     * @param bool $start - start external id
     * @param bool $end - end external id
     * @return int $i - real end external id
     */
    public function actionUpdate($start = false, $end = false) {

        if ($start === false) {
            $start = \Yii::$app->cache->get(ParserShell::getInstance()->getParser()->getKey());
        }

        //for debugging
        if ($end !== false) {
            $end = $start + (int)$end;
        } else {
            $end = $start + 500;
        }

        print "FROM $start TO $end\r\n";
        $count = 0;

        for ($i = $start; $i < $end; $i++) {
            $error = ParserShell::getInstance()->getParser()->start($i, 1)->getError();
            if ($error == 'empty page') {
                $count++;
            } else {
                $count = 0;
                \Yii::$app->cache->set(ParserShell::getInstance()->getParser()->getKey(), $i, $this->cacheTime);
            }

            if ($count == $this->retryCount) {
                //ParserShell::getInstance()->getParser()->log($i, 'THE MATCHES HASN\'T ESTABLISHED YET..');
                //ParserShell::getInstance()->getParser()->log($i, 'STOP PARSING..');
                break;
            }
        }
        return $i;
    }

}