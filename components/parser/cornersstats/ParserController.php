<?php
namespace app\components\parser\cornersstats;

use app\components\ControllerComponent;

class ParserController extends ControllerComponent{

    /**
     * @var Parser $parser
     */
    protected $parser;

    /**
     * Function return Parser class
     * @return Parser
     */
    public function getParser() {
        if(!$this->parser) {
            $this->parser = new Parser;
        }
        return $this->parser;
    }
    
    public function getLastExternalId() {
        $lastMatch = \app\modules\soccer\models\Match::find()
            ->orderBy(['id' => SORT_DESC])
            ->one();
        return $lastMatch->fapi_id;
    }
}