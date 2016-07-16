<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 25.04.2016
 * Time: 0:51
 */

namespace app\commands;

use yii\console\Controller;


class LineController extends Controller
{
    public function actionHandleline()
    {
        $linehandler = new \app\extensions\parsing\LineHandler();
        $linehandler->run();
    }
    public function actionPushlinemarathon()
    {
        $linepusher = new \app\extensions\parsing\marathonbet\MarathonbetLinePusher();
        $linepusher->run();
    }
    public function actionPushlinecornerstats()
    {
        $linepusher = new \app\extensions\parsing\cornerstats\CornerstatsLinePusher();
        $linepusher->run();
    }
    public function actionPushlinefootballdata()
    {
        $linepusher = new \app\extensions\parsing\footballdata\FootballdataLinePusher();
        $linepusher->run();
    }

    public function actionTest()
    {
        $fp = fopen('test.txt', 'w');
        if($fp) {
            fwrite($fp, 'hahaha');
            fclose($fp);
        }
    }
}