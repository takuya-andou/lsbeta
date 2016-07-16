<?php
namespace app\components\parser\linehandlerexecutor;

use app\extensions\parsing\LineHandler;

class LineHandlerExecutor
{
    public function execute() {
        $linehandler = new \app\extensions\parsing\LineHandler();
        $linehandler->run();
        echo __NAMESPACE__;
    }

}
/*
$obj = new \app\components\parser\linehandlerexecutor\LineHandlerExecutor();
$obj->execute();*/
?>