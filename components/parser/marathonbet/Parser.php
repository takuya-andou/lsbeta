<?php

namespace app\components\parser\marathonbet;

class Parser
{
    public function execute() {
        $parser = new \app\extensions\parsing\marathonbet\MarathonbetFootballParser();
        $parser->run();
    }
}