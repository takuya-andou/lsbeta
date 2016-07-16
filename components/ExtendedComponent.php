<?php
/*
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', 900);
 */

namespace app\components;

use Yii;
use yii\base\Component;

class ExtendedComponent extends Component {
    /**
     * @var array $config
     */
    protected $config = [], $params = [];

    /**
     * @var Logger $logger
     */
    protected $logger = null;

    /**
     * @var bool $debug Debug mode
     */
    protected $debug = true;

    /**
     * @var bool|string $error
     */
    protected $error;

    public function __construct() {}

    /**
     * @return Logger
     */
    public function getLogger($path = null){

        if ($this->config) {
            $configPath = $this->config['logPath'];
        } else {
            $configPath = $this->params['logPath'];
        }

        if(!$this->logger) {
            $this->logger = new Logger;
            $this->logger->log_path = ($path) ? $path : $configPath;
        }
        return $this->logger;
    }

    /**
     * @return bool|string
     */
    public function getError() {
        return $this->error;
    }

    /**
     * @return bool|string
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * Function called, if $this->error was equal to true
     * @param array $backtrace
     * @param string $message
     * @return bool
     */
    public function catcher($backtrace = array(), $message='') {
        $params = array();
        if (!empty($backtrace)) {
            $path = (strripos($backtrace[0]['file'], 'www/')) ? substr(strstr($backtrace[0]['file'], 'www/'),3) : $backtrace[0]['file'];
            $params['CALLED_SCRIPT'] = $path . ' (line: ' .$backtrace[0]['line']. ')';
            $params['METHOD'] = $backtrace[0]['function'];
            //return false;
        }
        //todo exeptions
        //$catcher = new LocalExeption();
        //$catcher->catcher($message,$params);
        $this->logger($message.' ('.json_encode($params).')');
        //die();
    }
}