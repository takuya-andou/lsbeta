<?php

namespace app\components;

use Yii;
use yii\base\Component;

class ControllerComponent {
    /**
     * @var ParserController $instance
     */
    protected static $instance;

    private function __construct() {}

    /**
     * Function return App instance
     * @return static
     */
    public static function getInstance(){
        if(!static::$instance) {
            static::$instance = new static;
        }
        return static::$instance;
    }
}